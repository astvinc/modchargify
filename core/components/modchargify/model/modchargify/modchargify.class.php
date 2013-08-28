<?php

require('subscription.class.php');

class ModChargify {

    function __construct(modX &$modx, array $config = array()) {
        $this->modx = &$modx;

        $basePath = $this->modx->getOption('modchargify.core_path', $config, $this->modx->getOption('core_path') . 'components/modchargify/');
        $assetsUrl = $this->modx->getOption('modchargify.assets_url', $config, $this->modx->getOption('assets_url') . 'components/modchargify/');
        $this->config = array_merge(array(
            'basePath' => $basePath,
            'corePath' => $basePath,
            'modelPath' => $basePath . 'model/',
            'processorsPath' => $basePath . 'processors/',
            'templatesPath' => $basePath . 'templates/',
            'chunksPath' => $basePath . 'elements/chunks/',
            'jsUrl' => $assetsUrl . 'js/',
            'cssUrl' => $assetsUrl . 'css/',
            'assetsUrl' => $assetsUrl,
            'connectorUrl' => $assetsUrl . 'connector.php',
            'siteUrl' => $modx->getOption('site_url'),
            'extendedFieldName' => 'Chargify_ID', 
            'limit' => '2',
            'outputSeparator' => '\n',
            'containerTpl' => 'chargifySubscriptionsTpl',
            'emptyTpl' => 'chargifyEmptyTpl',
            'paymentTpl' => 'chargifyPaymentTpl',
            'billingAddressTpl' => 'chargifyBillingAddressTpl',
            'confirmCancelTpl' => 'chargifyConfirmCancelTpl',
            'cancelTpl' => 'chargifyCancelTpl',
            'emptyMsg' => 'At the moment you have no active subscriptions.',
            'cancelSuccessMsg' => 'Your subscription has been canceled.',
            'cancelErrorMsg' => 'Your subscription could not be canceled at this moment. Please try again later.',
            'errorMsg' => 'An error has occured.'
                ), $config);


        $_SESSION['ModChargify'] = $this->config;
    }

    function getSubscriptionsByCustomer() {

        $output = array();

        try {

            $subscription = new ModxChargifySubscription($this->modx);

            if (isset($this->config['customerId'])) {
                $subscription->customer_id = $this->config['customerId'];
            } else {
                $userId = $this->modx->user->id;
                $user = $this->modx->getObject('modUser', $userId);
                $profile = $user->getOne('Profile');
                $extended = $profile->get('extended');
                $subscription->customer_id = $extended[$this->config['extendedFieldName']];
            }

            if (isset($subscription->customer_id)) {

                $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Retrieving subscriptions for Customer id:' . $subscription->customer_id);

                $subscriptions = $subscription->getByCustomerID();
                $count = 0;

                foreach ($subscriptions as $s) {

                    if ($count <= $this->config['limit']) {

                        if ($s->state == 'canceled') {
                            continue;
                        }

                        $product_name = $s->product->name;
                        $cc = $s->credit_card;

                        $payment = array(
                            "card_type" => ucwords($cc->card_type),
                            "masked_card_number" => $cc->masked_card_number,
                            "expiration_month" => $cc->expiration_month,
                            "expiration_year" => $cc->expiration_year
                        );

                        $billing = array(
                            "first_name" => ucwords($cc->first_name),
                            "last_name" => ucwords($cc->last_name),
                            "address" => $cc->billing_address,
                            "city" => $cc->billing_city,
                            "state" => $cc->billing_state,
                            "zip" => $cc->billing_zip
                        );

                        $payment_output = $this->modx->getChunk($this->config['paymentTpl'], $payment);

                        $billing_output = $this->modx->getChunk($this->config['billingAddressTpl'], $billing);

                        $output[] = $this->modx->getChunk($this->config['containerTpl'], array(
                            "subscription_id" => $s->id,
                            "product_name" => $product_name,
                            "payment" => $payment_output,
                            "billing_address" => $billing_output,
                            "cancel_url" => $this->getCancelConfirmUrl($s->id),
                            "subscriptions_url" => $this->getSubscriptionsUrl(),
                            "update-payment-url" => $this->getUpdatePaymentUrl($s->id)
                        ));

                        $count++;
                    }
                }
                if (empty($output)) {
                    $output = $this->modx->getChunk($this->config['emptyTpl'], array(
                        "empty_message" => $this->config['emptyMsg']
                    ));
                } else {
                    $output = implode($this->config['outputSeparator'], $output);
                }
            } else {
                $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Chargify customer id could not be found');
                $output = $this->modx->getChunk($this->config['emptyTpl'], array(
                        "empty_message" => $this->config['errorMsg']
                    ));
            }
        } catch (Exception $e) {
            error_log($e);
            $output = $this->config['errorMsg'];
        }


        return $output;
    }

    function cancelConfirmationRequest($subscriptionid) {

        try {

            $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Cancelation request for :' . $subscriptionid);
            $output = '';

            $subscription = new ModxChargifySubscription($this->modx);
            $subscription->id = $subscriptionid;
            $subscription = $subscription->getByID();

            $output = $this->modx->getChunk($this->config['confirmCancelTpl'], array(
                "subscription_id" => $subscription->id,
                "product_name" => $subscription->product->name,
                "cancel_url" => $this->getCancelUrl($subscriptionid),
                "subscriptions_url" => $this->getSubscriptionsUrl()
            ));
        } catch (Exception $e) {

            error_log($e);
            $output = $this->modx->getChunk($this->config['cancelTpl'], array(
                "subscriptions_url" => $this->getSubscriptionsUrl(),
                "cancel_message" => $this->config['errorMsg']
            ));
        }

        return $output;
    }

    function cancelSubscription($subscriptionid) {

        try {

            $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Process cancelation for :' . $subscriptionid);

            if (empty($subscriptionid)) {

                $subscriptionid = $this->config['subscriptionId'];

                if (empty($subscriptionid)) {

                    $this->modx->log(modX::LOG_LEVEL_ERROR, '[Chargify] Subscription id was not provided');

                    return $this->modx->getChunk($this->config['cancelTpl'], array(
                                "subscriptions_url" => $this->getSubscriptionsUrl(),
                                "cancel_message" => $this->config['cancelErrorMsg']
                    ));
                }
            }

            $subscription = new ModxChargifySubscription($this->modx);
            $subscription->id = $subscriptionid;
            $subscription = $subscription->getByID();


            if (!$subscription->cancel($cancelmessage)) {
                $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Subscription could not be canceled');
                
                $output = $this->modx->getChunk($this->config['cancelTpl'], array(
                    "subscriptions_url" => $this->getSubscriptionsUrl(),
                    "cancel_message" => $this->config['cancelErrorMsg']
                ));
                
            }else{
                $output = $this->modx->getChunk($this->config['cancelTpl'], array(
                    "subscriptions_url" => $this->getSubscriptionsUrl(),
                    "cancel_message" => $this->config['cancelSuccessMsg']
                ));
            }
  
        } catch (Exception $e) {
            error_log($e);
            $output = $this->modx->getChunk($this->config['cancelTpl'], array(
                "subscriptions_url" => $this->getSubscriptionsUrl(),
                "cancel_message" => $this->config['errorMsg']
            ));
        }

        

        return $output;
    }

    function getCancelConfirmUrl($subscriptionid) {
        $url = $this->config['siteUrl'];
        $cancelResourceId = $this->config['cancelResourceId'];
        if (!isset($cancelResourceId))
            $cancelResourceId = $this->modx->resource->get('id');

        if ($pos = strpos($url, '?')) {
            $url .= '&id=' . $cancelResourceId . '&action=confirm&subscriptionid=' . $subscriptionid;
        } else {
            $url .= '?id=' . $cancelResourceId . '&action=confirm&subscriptionid=' . $subscriptionid;
        }
        return $url;
    }

    function getCancelUrl($subscriptionid) {
        $url = $this->config['siteUrl'];
        $cancelResourceId = $this->config['cancelResourceId'];
        if (!isset($cancelResourceId))
            $cancelResourceId = $this->modx->resource->get('id');

        if ($pos = strpos($url, '?')) {
            $url .= '&id=' . $cancelResourceId . '&action=cancel&subscriptionid=' . $subscriptionid;
        } else {
            $url .= '?id=' . $cancelResourceId . '&action=cancel&subscriptionid=' . $subscriptionid;
        }
        return $url;
    }

    function getSubscriptionsUrl() {
        $url = $this->config['siteUrl'];
        $resourceId = $this->config['subscriptionsResourceId'];
        if (!isset($resourceId))
            $resourceId = $this->modx->resource->get('id');

        if ($pos = strpos($url, '?')) {
            $url .= '&id=' . $resourceId;
        } else {
            $url .= '?id=' . $resourceId;
        }
        return $url;
    }

    function getUpdatePaymentUrl($subscriptionid) {
        $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] creating update payment url...');
        $sharedkey = $this->modx->getOption('modchargify.shared_key');
        if (!isset($sharedkey)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[Chargify] Shared key is not set on system settings...');
            return '#';
        }

        if (!isset($subscriptionid)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[Chargify] Subscription id was not provided...');
            return '#';
        }


        $message = "update_payment--" . $subscriptionid . "--" . $sharedkey;
        $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] message:' . $message);
        $token = substr(sha1($message), 0, 10);
        $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] token:' . $token);
        $domain = $this->modx->getOption('modchargify.shop_domain');
        $url = "https://" . $domain . "/update_payment/" . $subscriptionid . "/" . $token;

        return $url;
    }

    public function getChunk($name, $properties = array()) {
        $chunk = null;
        if (!isset($this->chunks[$name])) {
            $chunk = $this->_getTplChunk($name);
            if (empty($chunk)) {
                $chunk = $this->modx->getObject('modChunk', array('name' => $name));
                if ($chunk == false)
                    return false;
            }
            $this->chunks[$name] = $chunk->getContent();
        } else {
            $o = $this->chunks[$name];
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setContent($o);
        }
        $chunk->setCacheable(false);
        return $chunk->process($properties);
    }

    private function _getTplChunk($name, $postfix = '.chunk.tpl') {
        $chunk = false;
        $f = $this->config['chunksPath'] . strtolower($name) . $postfix;
        if (file_exists($f)) {
            $o = file_get_contents($f);
            $chunk = $this->modx->newObject('modChunk');
            $chunk->set('name', $name);
            $chunk->setContent($o);
        }
        return $chunk;
    }

}

?>

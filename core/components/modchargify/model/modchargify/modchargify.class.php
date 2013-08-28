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
            
            'limit' => '2',
            'outputSeparator' => '\n',
            'containerTpl' => 'ChargifySubscription',
            'paymentTpl' => 'ChargifyPayment',
            'billingAddressTpl' => 'ChargifyBillingAddress',
            'cancelResourceId'=>''
            
                ), $config);


        $_SESSION['ModChargify'] = $this->config;
    }

    function getSubscriptionsByCustomer() {

        $output = array();

        try {
            
            $subscription = new ModxChargifySubscription($this->modx);
            
            if(isset($this->config['customerId'])){
                $subscription->customer_id = $this->config['customerId'];
                
            }else{
                $userId = $this->modx->user->id;
                $user = $this->modx->getObject('modUser',$userId);
                $profile = $user->getOne('Profile');
                $extended = $profile->get('extended');
                $subscription->customer_id = $extended["chargifyId"];
            }
                
            if(isset($subscription->customer_id)){
                    
                $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Retrieving subscriptions for Customer id:'.$subscription->customer_id);

                $subscriptions = $subscription->getByCustomerID();
                $count = 0;

                foreach ($subscriptions as $s) {

                    if ($count <= $this->config['limit']) {

                        if($s->state == 'canceled'){
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
                            "product_name" => $product_name,
                            "payment" => $payment_output,
                            "billing_address" => $billing_output,
                            "cancel_url" => $this->getCancelUrl($s->id),
                            "update-payment-url" => $this->getUpdatePaymentUrl($s->id)
                        ));

                        $count++;
                    }
                }
                if(empty($output)){
                    $output = "No subscriptions found";
                }else{
                 $output = implode($this->config['outputSeparator'], $output);
                }
                
            }else{
                 $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Chargify customer id could not be founs');

            }


        } catch (ChargifyException $e) {
            error_log($e);
            $output = '<span>an error occurred retrieving your subscriptions.</span>';
        }
        

        return $output;
    }
    
    function cancelSubscription($subscriptionid = ''){
        
        try{

            if(!isset($subscriptionid)){

                $subscriptionid =  $this->config['subscriptionId'];
                if(!isset($subscriptionid)) return ''; 
            }

            $subscription = new ModxChargifySubscription($this->modx);
            $subscription->id = $subscriptionid;
            $subscription = $subscription->getByID();
            if(!$subscription->cancel('Your subscription has been cancelled.')){
                 $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] Subscription could not be canceled');
            }
            
        }catch(ChargifyConnectionException $e){
            error_log($e);
        }
        
        return $this->getSubscriptionsByCustomer();
        
    }
    
    //INCOMPLETE
    function switchSubscription($subscriptionid = ''){
        
        if(!isset($subscriptionid)){

            $subscriptionid =  $this->config['subscription_id'];
            if(!isset($subscriptionid)) return ''; 
        }

        $productid = $this->config['product_id'];
        
        $subscription = new ModxChargifySubscription($this->modx);
        $subscription->id = $subscriptionid;
        $subscription = $subscription->getByID();
        
        //Create product
        
        $subscription->updateProduct();
        
    }
    
    
    
    
    function getCancelUrl($subscriptionid) {
            $url = $this->config['siteUrl'];
            $cancelResourceId = $this->config['cancelResourceId'];
            if(!isset($cancelResourceId))
                $cancelResourceId = $this->modx->resource->get('id');

            if ($pos = strpos($url,'?')) {
                    $url .= '&id='.$cancelResourceId.'&action=cancel&subscriptionid='.$subscriptionid;
            }
            else {
                    $url .= '?id='.$cancelResourceId.'&action=cancel&subscriptionid='.$subscriptionid;
            }
            return $url;
    }
    
    
    function getUpdatePaymentUrl($subscriptionid){
        $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] creating update payment url...');
        $sharedkey = $this->modx->getOption('modchargify.shared_key');
        if(!isset($sharedkey)){
             $this->modx->log(modX::LOG_LEVEL_ERROR, '[Chargify] Shared key is not set on system settings...');
            return '#';
            
        }
        
        if(!isset($subscriptionid)){
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[Chargify] Subscription id was not provided...');
            return '#';
            
        }
        
        
        $message = "update_payment--".$subscriptionid."--".$sharedkey;
        $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] message:'.$message);
        $token = substr(sha1($message),0,10);
        $this->modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify] token:'.$token);
        $domain = $this->modx->getOption('modchargify.shop_domain');
        $url = "https://".$domain."/update_payment/".$subscriptionid."/".$token;
        
        return  $url;
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

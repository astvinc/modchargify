<?php

require_once('lib/Chargify.php');
require_once ('subscription.class.php');

class ModxChargifyConnector extends ChargifyConnector {

    //'bzlVFa6vAw8OgAEmuSzY', 'my-happy-company.chargify.com', 'sV0JE8RcLlofE0aZhC'
    //'AQRs9ve2lhHlK2WsLx','ediets.chargify.com','goWOLa00aFfuJCTbh0T6'


    public function __construct(modX &$modx, $test_mode = false, $active_domain = null, $active_api_key = null) {

        $this->modx = & $modx;

        $this->domain = $modx->getOption('modchargify.shop_domain');
        $this->api_key = $modx->getOption('modchargify.api_key');
        $this->shared_key = $modx->getOption('modchargify.shared_key');

        $this->test_domain = $modx->getOption('modchargify.test_shop_domain');
        $this->test_api_key = $modx->getOption('modchargify.test_api_key');
        $this->test_shared_key = $modx->getOption('modchargify.test_shared_key');


        $this->test_mode = $test_mode;
        if ($active_api_key == null || $active_domain == null) {
            if ($test_mode) {
                $this->setActiveDomain($this->test_domain, $this->test_api_key);
            } else {
                $this->setActiveDomain($this->domain, $this->api_key);
            }
        } else {
            $this->setActiveDomain($active_domain, $active_api_key);
        }
    }
    
    
    public function cancelSubscription($subscription_id, $cancellation_message, $format = 'XML') {
            $chargify_subscription = new ModxChargifySubscription($this->modx,null, $this->test_mode);
            $chargify_subscription->cancellation_message = $cancellation_message;
            return $this->requestCancelSubscription($subscription_id, $chargify_subscription->getXML());		
    }
    
    public function requestCancelSubscription($subscription_id, $subscriptionRequest, $format = 'XML') {
            $extension = strtoupper($format) == 'XML' ? '.xml' : '.json';
            $base_url = "/subscriptions/{$subscription_id}" . $extension;
            $xml = $this->sendRequest($base_url, $format, 'DELETE', $subscriptionRequest);

            if ($xml->code == 200) { //SUCCESS
                    return true;
            } else {
                    $errors = new SimpleXMLElement($xml->response);
                    throw new ChargifyValidationException($xml->code, $errors);
            }		
    }
    	
    public function getSubscriptionsByID($id)
    {
        $xml = $this->retrieveSubscriptionsByID($id);
        
        if(empty($xml))
            return null;
        
        $subscription = new SimpleXMLElement($xml);

        return new ModxChargifySubscription($this->modx,$subscription, $this->test_mode);
    }

}

?>
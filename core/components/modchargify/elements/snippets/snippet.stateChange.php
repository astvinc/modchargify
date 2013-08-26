<?php
/**
 * Wenhook listener for subscription state change event.
 *
 * @package modchargify
 */

$defaultCorePath = $modx->getOption('core_path').'components/modchargify/';
$modchargifyCorePath = $modx->getOption('modchargify.core_path',null,$defaultCorePath);
require_once($modchargifyCorePath.'include/Chargify-PHP-Client/Chargify.php');

$modx->log(modX::LOG_LEVEL_DEBUG
    , '[Chargify State Change] called on page '. $modx->resource->id . ' with the following parameters: '
    .print_r($scriptProperties, true));


if (!function_exists('arrayToObject')){
   function arrayToObject(array $array) {
           foreach ($array as &$value) {
                   if (is_array($value)) $value = (object) $value;
           }

           return (object) $array;
   }
}

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Listening...');

$postdata = file_get_contents('php://input');

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] POST:'.$postdata);

$event     = $_POST["event"];
$payload   = $_POST["payload"];

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Event:'.$event);

$shared_key = $this->modx->getOption('modchargify.shared_key');
$hash      = md5($shared_key . $postdata);
$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Hash:'.$hash);

$headers = apache_request_headers();
$webhookId = $headers['X-Chargify-Webhook-Id'];
$signature = $headers['X-Chargify-Webhook-Signature'];

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Webhook:'.$webhookId);
$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Signature:'.$signature);

if ($hash != $signature) {
    
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Signature is not valid...');

    header('HTTP/1.0 400 Bad request', true, 400);
    exit();
}

// Handle subscriptions ending.
if ($event == 'subscription_state_change') {
        $subscription = arrayToObject($payload['subscription']);
        file_put_contents('webhooks/subscription_state_change-' . date('m-d-y') . '-' . time() . '.xml', $payload['subscription'], FILE_APPEND);
//STATES:
//trialing
//trial_ended
//assessing
//active
//soft_failure
//past_due
//suspended
//canceled
//unpaid
//expired

        
        $state = $subscription->state;

        if(strcmp($state, "canceled")==0){

            $email = $subscription->customer->email;
            $productFamily = $subscription->product->product_family->handle;
            
            $result = $modx->query("select id from modx_users where email =" . $email);

            if ($result && is_object($result)) {
                $userId = $result->fetch(PDO::FETCH_COLUMN);
                $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] User: ' . $userId . ' with email:' . $email . ' exists');
   
                $user = $modx->getObject('modUser',$userId);
                $profile = $user->getOne('Profile');
                $extended = $profile->get('extended');
                
                if($productFamily=="diet-plans"){
                    $extended["membership"] = "free";
                    
                }
 
        
                $refreshTokens = array();
                $tokens = array();

                $refreshTokens = $xpdo->getCollection('RefreshTokens',array(
                        'user_id' => $userId
                     ));
                $tokens = $modx->getCollection('Tokens',array(
                        'user_id' => $userId
                     ));

                foreach($refreshTokens as $token){
                    $token->set('expires',time());
                    $token->save();
                }

                foreach($tokens as $token){
                    $token->set('expires',time());
                    $token->save();
                }
            }
            
            header('HTTP/1.0 200 OK', true, 200);
             exit();
            
            
        }

        
}

if ($event == 'test') {
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] This is a test');
    $test = arrayToObject($payload['chargify']);
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify State Change] Payload:'.$test);
    file_put_contents('webhooks/subscription-' . date('m-d-y') . '-' . time() . '.xml', $test, FILE_APPEND);

}


return '';
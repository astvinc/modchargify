<?php
/**
 * The base modChargify snippet.
 *
 * @package modchargify
 */

$defaultCorePath = $modx->getOption('core_path').'components/modchargify/';
$modchargifyCorePath = $modx->getOption('modchargify.core_path',null,$defaultCorePath);
require_once($modchargifyCorePath.'include/Chargify-PHP-Client/Chargify.php');

if (!function_exists('arrayToObject')){
   function arrayToObject(array $array) {
           foreach ($array as &$value) {
                   if (is_array($value)) $value = (object) $value;
           }

           return (object) $array;
   }
}

if (!function_exists('createRandomPassword')){
    function createRandomPassword() { 

        $chars = "abcdefghijkmnopqrstuvwxyz023456789"; 
        srand((double)microtime()*1000000); 
        $i = 0; 
        $pass = '' ; 

        while ($i <= 7) { 
            $num = rand() % 33; 
            $tmp = substr($chars, $num, 1); 
            $pass = $pass . $tmp; 
            $i++; 
        } 

        return $pass; 

    }
}


$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Listening...');

$postdata = file_get_contents('php://input');

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] POST:'.$postdata);

$event     = $_POST["event"];
$payload   = $_POST["payload"];

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Event:'.$event);

$shared    = 'sV0JE8RcLlofE0aZhC'; //TODO: Setup as system settings
$hash      = md5($shared . $postdata);
$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Hash:'.$hash);
//$signature = $_SERVER['X-Chargify-Webhook-Signature'];
//$webhookId = $_SERVER['X-Chargify-Webhook-Id'];

$headers = apache_request_headers();
$webhookId = $headers['X-Chargify-Webhook-Id'];
$signature = $headers['X-Chargify-Webhook-Signature'];

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Webhook:'.$webhookId);
$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Signature:'.$signature);

if ($hash != $signature) {
    header('HTTP/1.0 400 Bad request', true, 400);
    exit();
}

// Handle a new subscription being created.
if ($event == 'signup_success') {
        $subscription = arrayToObject($payload['subscription']);
        $email = $subscription->customer->email;
        $firstname = $subscription->customer->first_name;
        $lastname = $subscription->customer->last_name;
        $chargifyId = $subscription->customer->id;
        $pass = createRandomPassword();
        
        $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Username: '.$email.' Password generated:'.$pass);
            
        
        $fields = array(
            'username' => $email,   
            'password' => $pass,
            'email' => $email,
            'active' => 1,
            'blocked' => 0,
        );
        
        $user = $modx->newObject("modUser", $fields);
        if (!$user->save()) {
           $this->modx->log(modX::LOG_LEVEL_ERROR,'[Chargify Webhook]] Could not save newly registered user: '.$email);
           exit();
        }
        
        $uid = $user->get('id');
        $userProfile = $modx->newObject('modUserProfile');
        $userProfile->set('fullname',$firstname. ' ' .$lastname);
        $userProfile->set('email',$email);
        
        if (!$user->addOne($userProfile)) {
             $modx->log(modX::LOG_LEVEL_ERROR, '[Chargify Webhook] User ('.$uid.') profile has not been added succesfully');
             
        }else{
            
            $memberGroup = $modx->newObject('modUserGroupMember');
            $memberGroup->fromArray(array(
                'user_group' => 'Members',
                'member' => $uid,
                'role' => '1',
                ));
            $memberGroup->save();
            $user->joinGroup(trim('Members'));
            $user->save();
            mail('vramirez@astvinc.com', NULL, $password); //Just send password to my email during tests
            $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] User ('.$uid.') email sent...');
            
        }
        
        
        file_put_contents('webhooks/signup_success-' . date('m-d-y') . '-' . time() . '.xml', $payload['subscription'], FILE_APPEND);
}

// Handle subscription upgrades or downgrades.
if ($event == 'subscription_product_change') {
        $subscription = arrayToObject($payload['subscription']);
        file_put_contents('webhooks/subscription_product_change-' . date('m-d-y') . '-' . time() . '.xml', $payload['subscription'], FILE_APPEND);
}

// Handle subscriptions ending.
if ($event == 'subscription_state_change') {
        $subscription = arrayToObject($payload['subscription']);
        file_put_contents('webhooks/subscription_state_change-' . date('m-d-y') . '-' . time() . '.xml', $payload['subscription'], FILE_APPEND);
}

if ($event == 'test') {
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] This is a test');
    $test = arrayToObject($payload['chargify']);
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Webhook] Payload:'.$test);
    file_put_contents('webhooks/subscription-' . date('m-d-y') . '-' . time() . '.xml', $test, FILE_APPEND);

}


return '';
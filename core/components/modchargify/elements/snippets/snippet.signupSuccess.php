<?php

/**
 * Webhook listener for sign up success event
 *
 * @package modchargify
 */
$defaultCorePath = $modx->getOption('core_path') . 'components/modchargify/';
$modchargifyCorePath = $modx->getOption('modchargify.core_path', null, $defaultCorePath);
require_once($modchargifyCorePath . 'include/Chargify-PHP-Client/Chargify.php');

$modx->log(modX::LOG_LEVEL_DEBUG
        , '[Chargify SignUp Success] called on page ' . $modx->resource->id . ' with the following parameters: '
        . print_r($scriptProperties, true));

$defaults = array(
    'emailtpl' => 50,
    'role' => 1,
    'usergroup' => 'Members'
);

$scriptProperties = array_merge($defaults, $scriptProperties);


$emailTpl = $scriptProperties['emailtpl'];
$role = $scriptProperties['role'];
$usergroup = $scriptProperties['usergroup'];


if (!function_exists('arrayToObject')) {

    function arrayToObject(array $array) {
        foreach ($array as &$value) {
            if (is_array($value))
                $value = (object) $value;
        }

        return (object) $array;
    }

}

if (!function_exists('createRandomPassword')) {

    function createRandomPassword() {

        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double) microtime() * 1000000);
        $i = 0;
        $pass = '';

        while ($i <= 7) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }

        return $pass;
    }

}


$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Listening...');

$shared_key = $this->modx->getOption('modchargify.shared_key');
if (!isset($shared_key)) {
    $modx->log(modX::lOG_LEVEL_ERROR, '[Chargify SignUp Success] Shared key is not set in the system settings.');
    header('HTTP/1.0 500 Internal Server Error', true, 500);
    exit();
}

$postdata = file_get_contents('php://input');

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] POST:' . $postdata);

$event = $_POST["event"];
$payload = $_POST["payload"];

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Event:' . $event);

$hash = md5($shared_key . $postdata);
$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Hash:' . $hash);

$headers = apache_request_headers();
$webhookId = $headers['X-Chargify-Webhook-Id'];
$signature = $headers['X-Chargify-Webhook-Signature'];

$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Webhook:' . $webhookId);
$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Signature:' . $signature);

if ($hash != $signature) {

    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Signature is not valid...');
    header('HTTP/1.0 400 Bad request', true, 400);
    exit();
}

// Handle a new subscription being created.
if ($event == 'signup_success') {

    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] signup_success');

    $subscription = arrayToObject($payload['subscription']);
    $chargifyId = $subscription->customer->id;
    $email = $subscription->customer->email;

    $firstname = $subscription->customer->first_name;
    $lastname = $subscription->customer->last_name;
    $reference = $subscription->customer->reference;
    $pass = createRandomPassword();

    $mealplan = $subscription->product->name;
    $subscriptionid = $subscription->id;
    $celebritysite = "eDiets.com"; //TODO: How to determine this?
    //Check if there is a user with the same email already...
    $result = $modx->query("select id from modx_users where email =" . $email);

    if ($result && is_object($result)) {
        
        $userId = $result->fetch(PDO::FETCH_COLUMN);
        $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] User: '.$id.' with email:'.$email.'already exists' );
        $user = $modx->getObject('modUser',$userId);
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        $extended["chargifyId"] = $userId;
        $extended['membership'] = "paid";
        $profile->set('extended',$extended);
        $profile->save();
        header('HTTP/1.0 200 OK', true, 200);
        exit();
        
    }

    //User doesn't exist... Create a new account!

    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Username: ' . $email . ' Password generated:' . $pass . 'product handle ' . $product);


    $fields = array(
        'username' => $email,
        'password' => $pass,
        'email' => $email,
        'active' => 1,
        'blocked' => 0,
    );

    $user = $modx->newObject("modUser", $fields);
    if (!$user->save()) {
        $this->modx->log(modX::LOG_LEVEL_ERROR, '[Chargify SignUp Success]] Could not save newly registered user: ' . $email);
        exit();
    }

    //Settting profile values
    $uid = $user->get('id');
    $profile = $modx->newObject('modUserProfile');
    $profile->set('fullname', $firstname . ' ' . $lastname);
    $profile->set('email', $email);

    //Extending fields
    $extfields = array();
    $extfields['first_name'] = $firstname;
    $extfields['last_name'] = $lastname;
    $extfields['membership'] = "paid"; //since it is a sign up success
    $extfields['celebrity_site'] = $celebritysite;
    $profile->set('extended', $extfields);
    $profile->save();

    if (!$user->addOne($profile)) {
        $modx->log(modX::LOG_LEVEL_ERROR, '[Chargify SignUp Success] User (' . $uid . ') profile has not been added succesfully');
    } else {
        if (isset($usergroup) && isset($role)) {
            $memberGroup = $modx->newObject('modUserGroupMember');
            $memberGroup->fromArray(array(
                'user_group' => $usergroup,
                'member' => $uid,
                'role' => $role,
            ));
            $memberGroup->save();
            $user->joinGroup(trim('Members'));
            $user->save();
            $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] User (' . $uid . ') has usergroup ' . $sign_up_usergroup);
        }


        $message = $modx->getChunk($emailTpl);
        $modx->getService('mail', 'mail.modPHPMailer');
        $modx->mail->set(modMail::MAIL_BODY, $message);
        $modx->mail->set(modMail::MAIL_FROM, 'NOREPLY@ediets.com');
        $modx->mail->set(modMail::MAIL_FROM_NAME, 'eDiets.com');
        $modx->mail->set(modMail::MAIL_SUBJECT, 'New Diet Subscription');
        //$modx->mail->address('to',$email);
        $modx->mail->address('to', 'vanessaramirez30@gmail.com');
        $modx->mail->address('reply-to', 'NOREPLY@ediets.com');
        $modx->mail->setHTML(true);
        if (!$modx->mail->send()) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $modx->mail->mailer->ErrorInfo);
        }
        $modx->mail->reset();
    }
    
}


if ($event == 'test') {
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] This is a test');
    $test = arrayToObject($payload['chargify']);
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify SignUp Success] Payload:' . $test);
    file_put_contents('webhooks/subscription-' . date('m-d-y') . '-' . time() . '.xml', $test, FILE_APPEND);
}


return '';
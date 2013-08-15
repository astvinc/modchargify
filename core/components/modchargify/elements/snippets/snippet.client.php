<?php
/**
 * The base modChargify snippet.
 *
 * @package modchargify
 */

$defaultCorePath = $modx->getOption('core_path').'components/modchargify/';
$modchargifyCorePath = $modx->getOption('modchargify.core_path',null,$defaultCorePath);


//require_once($modchargifyCorePath.'/include/Splash/Chargify/Autoloader.php');
//Splash\Chargify\Autoloader::register();
//$client = new Splash\Chargify\Client('bzlVFa6vAw8OgAEmuSzY', 'my-happy-company.chargify.com', 'sV0JE8RcLlofE0aZhC');
//$subscription = $client->api('subscriptions', array("customer_id "=>3560655), 'GET', false);

$modChargify = $modx->getService('modchargify','ModChargify',$modchargifyCorePath.'/model/modchargify/',$scriptProperties);
if (!($modChargify instanceof modChargify)) return '';

/**
 * Do your snippet code here. This demo grabs 5 items from our custom table.
 */
$tpl = $modx->getOption('tpl',$scriptProperties,'Item');
$sortBy = $modx->getOption('sortBy',$scriptProperties,'name');
$sortDir = $modx->getOption('sortDir',$scriptProperties,'ASC');
$limit = $modx->getOption('limit',$scriptProperties,5);
$outputSeparator = $modx->getOption('outputSeparator',$scriptProperties,"\n");

//Chargify-PHP-Client
require_once($modchargifyCorePath.'include/Chargify-PHP-Client/Chargify.php');

$test = TRUE;
$output = '';

try{
    
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Client] STARTED');
    
    $customer = new ChargifyCustomer(NULL);
    $customer->id=3579656;
    $customer = $customer->getByID();
    print_r($customer->getJSON());
    
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Client] Customer:'.$customer->getFullName());

    $subscription = new ChargifySubscription(NULL);
    $subscription->customer_id =3579656; 
    $response = $subscription->getByCustomerID();
    print_r($response);
    

}catch(ChargifyException $e){
    error_log($e);
}



$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Client] END');

/* by default just return output */
return $output;
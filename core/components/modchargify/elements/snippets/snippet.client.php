<?php
/**
 * The base modChargify snippet.
 *
 * @package modchargify
 */

require_once($modx->getOption('modchargify.core_path',null,$modx->getOption('core_path').'components/modchargify/').'include/Chargify-PHP-Client/ChargifyException.php');
require_once($modx->getOption('modchargify.core_path',null,$modx->getOption('core_path').'components/modchargify/').'model/modchargify/customer.class.php');
$modchargifyCorePath = $modx->getOption('modchargify.core_path',null,$modx->getOption('core_path').'components/modchargify/');

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

$output = '';

try{
    
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Client] STARTED');
    
    $customer = new ModxChargifyCustomer($modx);
    $customer->id=3630122;
    $customer = $customer->getByID();
    print_r($customer->getJSON());
    
    $modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Client] Customer:'.$customer->getFullName());

//    $subscription = new ChargifySubscription();
//    $subscription->customer_id =3630122; 
//    $response = $subscription->getByCustomerID();
//    print_r($response);
    

}catch(ChargifyException $e){
    error_log($e);
}



$modx->log(modX::LOG_LEVEL_DEBUG, '[Chargify Client] END');

/* by default just return output */
return $output;
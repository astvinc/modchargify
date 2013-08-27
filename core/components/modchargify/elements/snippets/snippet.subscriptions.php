<?php
$modchargifyCorePath = $modx->getOption('modchargify.core_path', null, $modx->getOption('core_path') . 'components/modchargify/');

$ModChargify = $modx->getService('modchargify', 'ModChargify', $modchargifyCorePath . '/model/modchargify/', $scriptProperties);
if (!($ModChargify instanceof ModChargify))
    return '';

$action = 'list';

if(empty($_REQUEST['action'])) {
    $action = $modx->getOption('action', $scriptProperties, 'list');
}else{
    $action = $_REQUEST['action'];
}


$subscriptionid = '';
if(!empty($_REQUEST['subscriptionid'])) {
    $subscriptionid = $_REQUEST['subscriptionid'];   
}

$output = '';

switch ($action) {
    case 'cancel': $output = $ModChargify->cancelSubscription($subscriptionid);
        break;
    case 'switch': $output = $ModChargify->switchSubscription();
        break;
    case 'list':
    default: $output = $ModChargify->getSubscriptionsByCustomer();
        break;
}

return $output;
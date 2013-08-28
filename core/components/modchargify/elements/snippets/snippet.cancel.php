<?php
$modchargifyCorePath = $modx->getOption('modchargify.core_path', null, $modx->getOption('core_path') . 'components/modchargify/');

$ModChargify = $modx->getService('modchargify', 'ModChargify', $modchargifyCorePath . '/model/modchargify/', $scriptProperties);
if (!($ModChargify instanceof ModChargify))
    return '';

$action = 'confirm';

if(empty($_REQUEST['action'])) {
    $action = $modx->getOption('action', $scriptProperties, $action);
}else{
    $action = $_REQUEST['action'];
}


$subscriptionid = '';
if(!empty($_REQUEST['subscriptionid'])) {
    $subscriptionid = $_REQUEST['subscriptionid'];   
}


switch ($action) {
    case 'cancel': $output = $ModChargify->cancelSubscription($subscriptionid);
        break;
    case 'confirm':
    default: $output = $ModChargify->cancelConfirmationRequest($subscriptionid);
        break;
}

return $output;
<?php
$modchargifyCorePath = $modx->getOption('modchargify.core_path', null, $modx->getOption('core_path') . 'components/modchargify/');

$ModChargify = $modx->getService('modchargify', 'ModChargify', $modchargifyCorePath . '/model/modchargify/', $scriptProperties);
if (!($ModChargify instanceof ModChargify))
    return '';

return $ModChargify->getSubscriptionsByCustomer();
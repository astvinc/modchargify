<?php
function getChunkContent($filename = '') {
    $o = file_get_contents($filename);
    $o = trim($o);
    return $o;
}

$chunks = array();

$chunks[0]= $modx->newObject('modChunk');
$chunks[0]->fromArray(array(
    'id' => 0,
    'name' => 'chargifyBillingAddressTpl',
    'description' => 'Chunk that will contain the billing address information',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.billingAddress.tpl')
),'',true,true);

$chunks[1]= $modx->newObject('modChunk');
$chunks[1]->fromArray(array(
    'id' => 1,
    'name' => 'chargifyPaymentTpl',
    'description' => 'Chunk that will contain the payment information.',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.payment.tpl')
),'',true,true);

$chunks[2]= $modx->newObject('modChunk');
$chunks[2]->fromArray(array(
    'id' => 2,
    'name' => 'chargifySubscriptionTpl',
    'description' => 'Chunk that will contain the subscription information.',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.subscription.tpl')
),'',true,true);


return $chunks;

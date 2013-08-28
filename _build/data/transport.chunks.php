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
    'name' => 'chargifySubscriptionsTpl',
    'description' => 'Chunk that will contain the subscription information.',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.subscription.tpl')
),'',true,true);

$chunks[3]= $modx->newObject('modChunk');
$chunks[3]->fromArray(array(
    'id' => 3,
    'name' => 'chargifyConfirmCancelTpl',
    'description' => 'Chunk that will contain the confirm cancellation content.',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.confirmcancel.tpl')
),'',true,true);


$chunks[4]= $modx->newObject('modChunk');
$chunks[4]->fromArray(array(
    'id' => 4,
    'name' => 'chargifyCancelTpl',
    'description' => 'Chunk that will contain the cancellation content.',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.cancel.tpl')
),'',true,true);

$chunks[5]= $modx->newObject('modChunk');
$chunks[5]->fromArray(array(
    'id' => 5,
    'name' => 'chargifyEmptyTpl',
    'description' => 'Chunk that will contain what to display when there are no subscription.',
    'snippet' => getChunkContent($sources['elements'].'chunks/chunk.empty.tpl')
),'',true,true);



return $chunks;

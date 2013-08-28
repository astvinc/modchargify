<?php


$snippets = array();

$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'name' => 'Subscriptions',
    'description' => 'Given a customer id, it retrives the subscription information for that customer and handles cancellations',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.subscriptions.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.subscriptions.php';
$snippets[0]->setProperties($properties);
unset($properties);


$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'CancelSubscription',
    'description' => 'Given a subscription id, it handles the cancellation process',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.cancel.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.cancel.php';
$snippets[1]->setProperties($properties);
unset($properties);



return $snippets;
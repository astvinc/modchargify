<?php


$snippets = array();


$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 0,
    'name' => 'SignUpSuccessHook',
    'description' => 'Handles sign_up_success events.',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.signupSuccess.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.signupsucess.php';
$snippets[0]->setProperties($properties);

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 1,
    'name' => 'SubscriptionStateChange',
    'description' => 'Handles subscription_state_change events.',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.stateChange.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.statechange.php';
$snippets[1]->setProperties($properties);
unset($properties);

$snippets[2]= $modx->newObject('modSnippet');
$snippets[2]->fromArray(array(
    'id' => 2,
    'name' => 'Subscriptionsr',
    'description' => 'Given a customer id, it retrives the subscription information for that customer and handles cancellations',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.subscriptions.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.subscriptions.php';
$snippets[1]->setProperties($properties);
unset($properties);



return $snippets;
<?php


$snippets = array();


$snippets[0]= $modx->newObject('modSnippet');
$snippets[0]->fromArray(array(
    'id' => 1,
    'name' => 'SignUpSuccessHook',
    'description' => 'Handles sign_up_success events.',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.signupSuccess.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.signupsucess.php';
$snippets[0]->setProperties($properties);

$snippets[1]= $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => 2,
    'name' => 'SubscriptionStateChange',
    'description' => 'Handles subscription_state_change events.',
    'snippet' => getSnippetContent($sources['elements'].'snippets/snippet.stateChange.php'),
),'',true,true);
$properties = include $sources['data'].'properties/properties.statechange.php';
$snippets[1]->setProperties($properties);
unset($properties);


return $snippets;
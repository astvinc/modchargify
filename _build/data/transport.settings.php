<?php

$settings = array();

$settings['modchargify.api_key']= $modx->newObject('modSystemSetting');
$settings['modchargify.api_key']->fromArray(array(
    'key' => 'modchargify.api_key',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modchargify',
    'area' => 'authentication',
),'',true,true);

$settings['modchargify.shared_key']= $modx->newObject('modSystemSetting');
$settings['modchargify.shared_key']->fromArray(array(
    'key' => 'modchargify.shared_key',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modchargify',
    'area' => 'authentication',
),'',true,true);

$settings['modchargify.shop_domain']= $modx->newObject('modSystemSetting');
$settings['modchargify.shop_domain']->fromArray(array(
    'key' => 'modchargify.shop_domain',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modchargify',
    'area' => 'authentication',
),'',true,true);

$settings['modchargify.test_api_key']= $modx->newObject('modSystemSetting');
$settings['modchargify.test_api_key']->fromArray(array(
    'key' => 'modchargify.test_api_key',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modchargify',
    'area' => 'authentication',
),'',true,true);

$settings['modchargify.test_shared_key']= $modx->newObject('modSystemSetting');
$settings['modchargify.test_shared_key']->fromArray(array(
    'key' => 'modchargify.test_shared_key',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modchargify',
    'area' => 'authentication',
),'',true,true);

$settings['modchargify.test_shop_domain']= $modx->newObject('modSystemSetting');
$settings['modchargify.test_shop_domain']->fromArray(array(
    'key' => 'modchargify.test_shop_domain',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modchargify',
    'area' => 'authentication',
),'',true,true);


return $settings;

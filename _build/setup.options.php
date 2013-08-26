<?php
/**
 * Build the setup options form.
 *
 * @package quip
 * @subpackage build
 */
/* set some default values */
$values = array(
  'shopDomain' => 'my-sample-shop.chargify.com',
  'apiKey' => '',
  'sharedKey' => ''
);
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
  case xPDOTransport::ACTION_INSTALL:
  case xPDOTransport::ACTION_UPGRADE:
      $setting = $modx->getObject('modSystemSetting',array('key' => 'modchargify.shop_domain'));
      if ($setting != null) { $values['shopDomain'] = $setting->get('value'); }
      unset($setting);

      $setting = $modx->getObject('modSystemSetting',array('key' => 'modchargify.api_key'));
      if ($setting != null) { $values['apiKey'] = $setting->get('value'); }
      unset($setting);

      $setting = $modx->getObject('modSystemSetting',array('key' => 'modchargify.auth_secret'));
      if ($setting != null) { $values['sharedKey'] = $setting->get('value'); }
      unset($setting);

      
      
  break;
  case xPDOTransport::ACTION_UNINSTALL: break;
}
 
$output = '<label for="modchargify-shopDomain">Chargify Shop Domain:</label>
<input type="text" name="shopDomain" id="modchargify-shopDomain" width="300" value="'.$values['shopDomain'].'" />
<br /><br />
 
<label for="modchargify-apiKey">Chargify API Key:</label>
<input type="text" name="apiKey" id="modchargify-apiKey" width="300" value="'.$values['apiKey'].'" />
<br /><br />
 
<label for="modchargify-sharedKey">Chargify Shared Key:</label>
<input type="text" name="sharedKey" id="modchargify-sharedKey" width="300" value="'.$values['sharedKey'].'" />
<br /><br />

';
 
return $output;

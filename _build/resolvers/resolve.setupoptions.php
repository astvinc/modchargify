<?php
/**
 * Resolves setup-options settings by setting Chargify API options.
 *
 */

$success = false;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        /* shopDomain */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modchargify.shop_domain'));
        if ($setting != null) {
            $setting->set('value',$options['shopDomain']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modChargify] shopDomain setting could not be found, so the setting could not be changed.');
        }
 
        /* apiKey */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modchargify.api_key'));
        if ($setting != null) {
            $setting->set('value',$options['apiKey']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modChargify] apiKey setting could not be found, so the setting could not be changed.');
        }
        
        /* sharedKey */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modchargify.secret_key'));
        if ($setting != null) {
            $setting->set('value',$options['sharedKey']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modChargify] sharedKey setting could not be found, so the setting could not be changed.');
        }

 
        $success= true;
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success= true;
        break;
}
return $success;

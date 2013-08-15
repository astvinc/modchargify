<?php

require_once dirname(__FILE__) . '/model/modchargify/modchargify.class.php';
abstract class ModChargifyManagerController extends modExtraManagerController {
    /** @var ModChargify modchargify */
    public $modchargify;
    public function initialize() {
        $this->modchargify = new ModChargify($this->modx);
 
        $this->addCss($this->modchargify->config['cssUrl'].'mgr.css');
        $this->addJavascript($this->modchargify->config['jsUrl'].'mgr/modchargify.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            ModChargify.config = '.$this->modx->toJSON($this->modchargify->config).';
        });
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics() {
        return array('modchargify:default');
    }
    public function checkPermissions() { return true;}
}
class IndexManagerController extends ModChargifyManagerController {
    public static function getDefaultController() { return 'home'; }
}

?>

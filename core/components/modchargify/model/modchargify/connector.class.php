<?php

require_once($modx->getOption('modchargify.core_path', null, $modx->getOption('core_path') . 'components/modchargify/') . 'include/Chargify-PHP-Client/Chargify.php');

class ModxChargifyConnector extends ChargifyConnector {

    //'bzlVFa6vAw8OgAEmuSzY', 'my-happy-company.chargify.com', 'sV0JE8RcLlofE0aZhC'
    //'AQRs9ve2lhHlK2WsLx','ediets.chargify.com','goWOLa00aFfuJCTbh0T6'


    public function __construct(modX &$modx, $test_mode = false, $active_domain = null, $active_api_key = null) {

        $this->modx = & $modx;

        $this->domain = $modx->getOption('modchargify.shop_domain');
        $this->api_key = $modx->getOption('modchargify.api_key');
        $this->shared_key = $modx->getOption('modchargify.shared_key');

        $this->test_domain = $modx->getOption('modchargify.test_shop_domain');
        $this->test_api_key = $modx->getOption('modchargify.test_api_key');
        $this->test_shared_key = $modx->getOption('modchargify.test_shared_key');


        $this->test_mode = $test_mode;
        if ($active_api_key == null || $active_domain == null) {
            if ($test_mode) {
                $this->setActiveDomain($this->test_domain, $this->test_api_key);
            } else {
                $this->setActiveDomain($this->domain, $this->api_key);
            }
        } else {
            $this->setActiveDomain($active_domain, $active_api_key);
        }
    }

}

?>
<?php

require_once('lib/Chargify.php');
require_once('connector.class.php');

class ModxChargifyCustomer extends ChargifyCustomer {

    public function __construct(modX &$modx, SimpleXMLElement $customer_xml_node = null, $test_mode = false) {
        $this->modx = & $modx;
        $this->connector = new ModxChargifyConnector($modx, $test_mode);
        if ($customer_xml_node) {
            //Load object dynamically and convert SimpleXMLElements into strings
            foreach ($customer_xml_node as $key => $element) {
                $this->$key = (string) $element;
            }
        }
    }

      
    public function getFullName() { return $this->first_name . ' ' . $this->last_name; }

    protected function getName() {
            return "customer";
    }

    public function create() {
            return $this->connector->createCustomer($this);
    }

    public function update() {
            return $this->connector->updateCustomer($this);
    }

    public function delete() {
            return $this->connector->deleteCustomer($this->id);
    }

    public function getAllCustomers($page_num = 1) {
            return $this->connector->getAllCustomers($page_num);
    }

    public function getByID() {
            return $this->connector->getCustomerByID($this->id);
    }

    public function getByReferenceID() {
            return $this->connector->getCustomerByReferenceID($this->reference);
    }

}
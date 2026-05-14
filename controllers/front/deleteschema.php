<?php
require_once _PS_MODULE_DIR_ . 'creditjet/classes/JetCreditModel.php';

class CreditJetDeleteschemaModuleFrontController extends ModuleFrontController
{
    /** @var array{success: string} */
    public array $result = ['success' => 'unsuccess'];

    public function initContent()
    {
        $this->ajax = true;

        if (null !== Tools::getValue('jet_product_id')) {
            $jet_product_id = Tools::getValue('jet_product_id');
        } else {
            $jet_product_id = '';
        }

        $deleted = JetCreditModel::deleteByJetProductId($jet_product_id);
        if ($deleted) {
            $this->result['success'] = 'success';
        }
        parent::initContent();
    }

    public function displayAjax()
    {
        die(json_encode($this->result));
    }
}

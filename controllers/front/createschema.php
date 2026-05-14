<?php
require_once _PS_MODULE_DIR_.'creditjet/classes/JetCreditModel.php';

class CreditJetCreateschemaModuleFrontController extends ModuleFrontController {
    public $result = [];

    public function initContent() {
        $this->ajax = true;
        $this->result['success'] = 'unsuccess';
        $this->result['text'] = '';

        $jet_product_id = filter_var(Tools::getValue('jet_product_id', ''), FILTER_SANITIZE_STRING);
        $jet_product_percent = filter_var(Tools::getValue('jet_product_percent', -1), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $jet_product_meseci = filter_var(Tools::getValue('jet_product_meseci', ''), FILTER_SANITIZE_STRING);
        $jet_product_price = filter_var(Tools::getValue('jet_product_price', 0), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $jet_product_start = filter_var(Tools::getValue('jet_product_start', ''), FILTER_SANITIZE_STRING);
        $jet_product_end = filter_var(Tools::getValue('jet_product_end', ''), FILTER_SANITIZE_STRING);

        $creditjet_categories_current = [
            "jet_product_id" => $jet_product_id,
            "jet_product_percent" => $jet_product_percent,
            "jet_product_meseci" => $jet_product_meseci,
            "jet_product_price" => $jet_product_price,
            "jet_product_start" => $jet_product_start,
            "jet_product_end" => $jet_product_end
        ];
        $inserted = JetCreditModel::insertIfNotExists(
            $jet_product_id,
            $jet_product_percent,
            $jet_product_meseci,
            $jet_product_price,
            $jet_product_start,
            $jet_product_end
        );
        if ((int)$inserted === (int)-1) {
            $this->result['success'] = 'success';
            $this->result['text'] = 'Вече имате въведен такъв филтър!';
        } elseif ((int)$inserted === (int)0) {
            $this->result['success'] = 'unsuccess';
            $this->result['text'] = 'Не можете да създадете филтъра.';
        } else {
            $this->result['success'] = 'success';
            $this->result['text'] = 'Успешно записахте филтъра.';
        }

        parent::initContent();
    }

    public function displayAjax() {
        die(json_encode($this->result));
    }
}
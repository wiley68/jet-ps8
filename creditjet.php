<?php

/**
 * @File: creditjet.php
 * @Author: Ilko Ivanov
 * @Author e-mail: ilko.iv@gmail.com
 * @Publisher: Avalon Ltd
 * @Publisher e-mail: home@avalonbg.com
 * @Owner: Avalon Ltd
 * @Version: 1.8.5
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!defined('JET_MINPRICE')) define('JET_MINPRICE', 150.00);
if (!defined('JET_MIN_250')) define('JET_MIN_250', 250.00);
if (!defined('JET_MIN_250_EUR')) define('JET_MIN_250_EUR', 125.00);
if (!defined('JETCREDIT_FINANCIAL_MAX_ITERATIONS')) define('JETCREDIT_FINANCIAL_MAX_ITERATIONS', 128);
if (!defined('JETCREDIT_FINANCIAL_PRECISION')) define('JETCREDIT_FINANCIAL_PRECISION', 1.0e-08);

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class CreditJet extends PaymentModule
{

    const HOOKS = [
        'ActionFrontControllerSetMedia',
        'displayProductAdditionalInfo',
        'displayShoppingCart',
        'paymentOptions'
    ];

    public function __construct()
    {
        $this->name = 'creditjet';
        $this->tab = 'payments_gateways';
        $this->version = '1.8.5';
        $this->author = 'Ilko Ivanov';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];

        parent::__construct();

        $this->displayName = 'ПБ Лични Финанси';
        $this->description = 'Дава възможност на Вашите клиенти да закупуват стока на изплащане с ПБ Лични Финанси';
        $this->confirmUninstall = 'Сигурни ли сте, че искате да деинсталирате?';
        if (!Configuration::get('CREDITJET_NAME')) {
            $this->warning = 'Няма предоставено име';
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        return parent::install() &&
            (bool) $this->registerHook(static::HOOKS) &&
            Configuration::updateValue(
                'CREDITJET_NAME',
                'ПБ лични финанси Кредитен калкулатор'
            ) &&
            $this->installDb();
    }

    public function uninstall()
    {
        if (
            !parent::uninstall() ||
            !Configuration::deleteByName('CREDITJET_NAME') ||
            !Configuration::deleteByName('PS_OS_CREDITJET') ||
            !Configuration::deleteByName('JET_STATUS_IN') ||
            !Configuration::deleteByName('JET_EMAIL') ||
            !Configuration::deleteByName('JET_ID') ||
            !Configuration::deleteByName('JET_PURCENT') ||
            !Configuration::deleteByName('JET_VNOSKI_DEFAULT') ||
            !Configuration::deleteByName('JET_CART_SHOW') ||
            !Configuration::deleteByName('JET_CARD_IN') ||
            !Configuration::deleteByName('JET_PURCENT_CARD') ||
            !Configuration::deleteByName('JET_COUNT') ||
            !Configuration::deleteByName('JET_GAP') ||
            !Configuration::deleteByName('JET_VNOSKA') ||
            !Configuration::deleteByName('JET_MINPRICE') ||
            !Configuration::deleteByName('JET_EUR') ||
            !$this->uninstallDb()
        )
            return false;
        return true;
    }

    private function installDb()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'jet_kop` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `jet_product_id` VARCHAR(20) NOT NULL,
            `jet_product_percent` DECIMAL(5,2) NOT NULL,
            `jet_product_meseci` VARCHAR(50) NOT NULL,
            `jet_product_price` DECIMAL(10,2) UNSIGNED NOT NULL,
            `jet_product_start` DATE NOT NULL,
            `jet_product_end` DATE NOT NULL,
            PRIMARY KEY (`id`),
            FULLTEXT KEY `idx` (`jet_product_id`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }

    private function uninstallDb()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'jet_kop`;';
        return Db::getInstance()->execute($sql);
    }

    public function isUsingNewTranslationSystem()
    {
        return true;
    }

    public function getContent()
    {
        $route = $this->get('router')->generate('credit_jet_configuration_form');
        Tools::redirectAdmin($route);
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        if ('product' === $this->context->controller->php_self) {
            $this->context->controller->registerJavascript(
                'module-creditjet-product-js',
                'modules/' . $this->name . '/js/creditjet_product.js',
                [
                    'priority' => 200,
                    'attribute' => 'async',
                    'version' => filemtime(_PS_MODULE_DIR_ . $this->name . '/js/creditjet_product.js')
                ]
            );
            $this->context->controller->registerStylesheet(
                'module-creditjet-product-css',
                'modules/' . $this->name . '/css/creditjet_product.css',
                [
                    'media' => 'all',
                    'priority' => 200,
                    'version' => filemtime(_PS_MODULE_DIR_ . $this->name . '/css/creditjet_product.css')
                ]
            );
        }
        if ('cart' === $this->context->controller->php_self) {
            $this->context->controller->registerStylesheet(
                'module-creditjet-cart-css',
                'modules/' . $this->name . '/css/creditjet_cart.css',
                [
                    'media' => 'all',
                    'priority' => 200,
                    'version' => filemtime(_PS_MODULE_DIR_ . $this->name . '/css/creditjet_cart.css')
                ]
            );
            $this->context->controller->registerJavascript(
                'module-creditjet-cart-js',
                'modules/' . $this->name . '/js/creditjet_cart.js',
                [
                    'priority' => 200,
                    'attribute' => 'async',
                    'version' => filemtime(_PS_MODULE_DIR_ . $this->name . '/js/creditjet_cart.js')
                ]
            );
        }
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->registerStylesheet(
                'module-creditjet-checkout-css',
                'modules/' . $this->name . '/css/creditjet_checkout.css',
                [
                    'media' => 'all',
                    'priority' => 200,
                    'version' => filemtime(_PS_MODULE_DIR_ . $this->name . '/css/creditjet_checkout.css')
                ]
            );
            $this->context->controller->registerJavascript(
                'module-creditjet-checkout-js',
                'modules/' . $this->name . '/js/creditjet_checkout.js',
                [
                    'priority' => 200,
                    'attribute' => 'async',
                    'version' => filemtime(_PS_MODULE_DIR_ . $this->name . '/js/creditjet_checkout.js')
                ]
            );
        }
    }

    public function checkCurrency(Cart $cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        $jet_id_product = (int) Tools::getValue('id_product');
        $jet_product = new Product($jet_id_product);
        $jet_price = (float)Product::getPriceStatic($jet_id_product, true);
        $jet_price_before = $jet_price;

        $jet_currency_code = $this->context->currency->iso_code;
        if ($jet_currency_code != 'EUR' && $jet_currency_code != 'BGN') {
            return null;
        }

        $jet_status = (int)Configuration::get('JET_STATUS_IN');
        if ($jet_status == 0) {
            return null;
        }

        $jet_eur = (int)Configuration::get("JET_EUR");
        $jet_sign = 'лв.';
        $jet_sign_second = 'евро';
        $jet_min_250 = JET_MIN_250;

        switch ($jet_eur) {
            case 0:
                $jet_sign = 'лв.';
                $jet_sign_second = '';
                break;
            case 1:
                if ($jet_currency_code == "EUR") {
                    $jet_price = $jet_price * 1.95583;
                }
                $jet_sign = 'лв.';
                $jet_sign_second = 'евро';
                break;
            case 2:
                if ($jet_currency_code == "BGN") {
                    $jet_price = $jet_price / 1.95583;
                }
                $jet_sign = 'евро';
                $jet_sign_second = 'лв.';
                $jet_min_250 = JET_MIN_250_EUR;
                break;
            case 3:
                if ($jet_currency_code == "BGN") {
                    $jet_price = $jet_price / 1.95583;
                }
                $jet_sign = 'евро';
                $jet_sign_second = '';
                $jet_min_250 = JET_MIN_250_EUR;
                break;
        }

        $jet_gap = (int)Configuration::get("JET_GAP");
        $jet_vnoski_default = (int)Configuration::get("JET_VNOSKI_DEFAULT");
        if ($jet_price < $jet_min_250) {
            $jet_vnoski = '9';
        } else {
            $jet_vnoski = $jet_vnoski_default;
        }

        $jet_card_in = (int)Configuration::get("JET_CARD_IN");
        $is_vnoska = (int)Configuration::get("JET_VNOSKA");

        $jet_name = "";
        $jet_lastname = "";
        $jet_email = "";
        $jet_phone = "";
        $jetCustomer = $this->context->customer;
        if ($jetCustomer->isLogged()) {
            $jet_name = $jetCustomer->firstname;
            $jet_lastname = $jetCustomer->lastname;
            $jet_email = $jetCustomer->email;
            $jet_phone = $jetCustomer->getAddresses($this->context->language->id)[0]['phone'];
        }

        $useragent = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $jet_is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));
        if ($jet_is_mobile) {
            $jet_PopUp_Detailed_v1 = "jetm_PopUp_Detailed_v1";
            $jet_Mask = "jetm_Mask";
            $jet_column_left = "jetm_column_left";
            $jet_column_right = "jetm_column_right";
            $obshto_credit_text = 'Общо кредит';
            $obshto_plashtania = 'Общо плащания';
            $modalpayment_jet = 'modalpayment_jetm';
        } else {
            $jet_PopUp_Detailed_v1 = "jet_PopUp_Detailed_v1";
            $jet_Mask = "jet_Mask";
            $jet_column_left = "jet_column_left";
            $jet_column_right = "jet_column_right";
            $obshto_credit_text = 'Общ размер на кредита';
            $obshto_plashtania = 'Обща стойност на плащанията';
            $modalpayment_jet = 'modalpayment_jet';
        }

        $this->context->smarty->assign([
            'jet_gap' => $jet_gap,
            'jet_price' => number_format($jet_price_before, 2, '.', ''),
            'jet_card_in' => $jet_card_in,
            'is_vnoska' => $is_vnoska,
            'jet_sign_second' => $jet_sign_second,
            'jet_sign' => $jet_sign,
            'jet_id_product' => $jet_id_product,
            'modalpayment_jet' => $modalpayment_jet,
            'jet_PopUp_Detailed_v1' => $jet_PopUp_Detailed_v1,
            'jet_Mask' => $jet_Mask,
            'jet_column_left' => $jet_column_left,
            'jet_column_right' => $jet_column_right,
            'obshto_credit_text' => $obshto_credit_text,
            'obshto_plashtania' => $obshto_plashtania,
            'jet_eur' => $jet_eur,
            'jet_vnoski' => $jet_vnoski,
            'jet_name' => $jet_name,
            'jet_lastname' => $jet_lastname,
            'jet_email' => $jet_email,
            'jet_phone' => $jet_phone
        ]);

        return $this->display(__FILE__, 'creditjet.tpl');
    }

    /**
     * @param array{cart: Cart} $params
     */
    public function hookDisplayShoppingCart($params)
    {
        $jet_currency_code = $this->context->currency->iso_code;
        if ($jet_currency_code != 'EUR' && $jet_currency_code != 'BGN') {
            return null;
        }

        $jet_status = (int)Configuration::get('JET_STATUS_IN');
        if ($jet_status == 0) {
            return null;
        }

        $jet_cart_show = (int)Configuration::get("JET_CART_SHOW");
        if ($jet_cart_show == 0) {
            return null;
        }

        $jet_card_in = (int)Configuration::get("JET_CARD_IN");
        $is_vnoska = (int)Configuration::get("JET_VNOSKA");

        $jet_products = $params['cart']->getProducts(true);
        $jet_price = $params['cart']->getordertotal(true);
        $jet_price_before = $jet_price;

        $jet_eur = (int)Configuration::get("JET_EUR");
        $jet_sign = 'лв.';
        $jet_sign_second = 'евро';
        $jet_min_250 = JET_MIN_250;

        switch ($jet_eur) {
            case 0:
                $jet_sign = 'лв.';
                $jet_sign_second = '';
                break;
            case 1:
                if ($jet_currency_code == "EUR") {
                    $jet_price = $jet_price * 1.95583;
                }
                $jet_sign = 'лв.';
                $jet_sign_second = 'евро';
                break;
            case 2:
                if ($jet_currency_code == "BGN") {
                    $jet_price = $jet_price / 1.95583;
                }
                $jet_sign = 'евро';
                $jet_sign_second = 'лв.';
                $jet_min_250 = JET_MIN_250_EUR;
                break;
            case 3:
                if ($jet_currency_code == "BGN") {
                    $jet_price = $jet_price / 1.95583;
                }
                $jet_sign = 'евро';
                $jet_sign_second = '';
                $jet_min_250 = JET_MIN_250_EUR;
                break;
        }

        $_minprice = (float)Configuration::get("JET_MINPRICE");
        if ($jet_price < $_minprice) {
            return null;
        }

        $jet_vnoski_default = (int)Configuration::get("JET_VNOSKI_DEFAULT");
        if ($jet_price < $jet_min_250) {
            $jet_vnoski = '9';
        } else {
            $jet_vnoski = $jet_vnoski_default;
        }

        $jet_name = "";
        $jet_lastname = "";
        $jet_email = "";
        $jet_phone = "";
        $jetCustomer = $this->context->customer;
        if ($jetCustomer->isLogged()) {
            $jet_name = $jetCustomer->firstname;
            $jet_lastname = $jetCustomer->lastname;
            $jet_email = $jetCustomer->email;
            $jet_phone = $jetCustomer->getAddresses($this->context->language->id)[0]['phone'];
        }

        $useragent = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $jet_is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));
        if ($jet_is_mobile) {
            $jet_PopUp_Detailed_v1 = "jetm_PopUp_Detailed_v1";
            $jet_Mask = "jetm_Mask";
            $jet_column_left = "jetm_column_left";
            $jet_column_right = "jetm_column_right";
            $obshto_credit_text = 'Общо кредит';
            $obshto_plashtania = 'Общо плащания';
            $modalpayment_jet = 'modalpayment_jetm';
        } else {
            $jet_PopUp_Detailed_v1 = "jet_PopUp_Detailed_v1";
            $jet_Mask = "jet_Mask";
            $jet_column_left = "jet_column_left";
            $jet_column_right = "jet_column_right";
            $obshto_credit_text = 'Общ размер на кредита';
            $obshto_plashtania = 'Обща стойност на плащанията';
            $modalpayment_jet = 'modalpayment_jet';
        }

        $jet_products_id = '';
        $jet_products_qt = '';
        $jet_products_pr = '';
        $jet_products_ct = '';
        $jet_products_vr = '';
        foreach ($jet_products as $product) {
            $jet_products_id .= $product['id_product'] . '_';
            $jet_products_qt .= $product['cart_quantity'] . '_';
            $jet_products_pr_current = (float)$product['price_wt'];
            switch ($jet_eur) {
                case 0:
                    break;
                case 1:
                    if ($jet_currency_code == "EUR") {
                        $jet_products_pr_current = $jet_products_pr_current * 1.95583;
                    }
                    break;
                case 2:
                case 3:
                    if ($jet_currency_code == "BGN") {
                        $jet_products_pr_current = $jet_products_pr_current / 1.95583;
                    }
                    break;
            }
            $jet_products_pr .= number_format($jet_products_pr_current, 2, ".", "") . '_';
            $jet_products_ct .= $product['id_category_default'] . '_';
            $jet_products_vr .= $product['id_product_attribute'] . '_';
        }
        $jet_products_id = substr($jet_products_id, 0, -1);
        $jet_products_qt = substr($jet_products_qt, 0, -1);
        $jet_products_pr = substr($jet_products_pr, 0, -1);
        $jet_products_ct = substr($jet_products_ct, 0, -1);
        $jet_products_vr = substr($jet_products_vr, 0, -1);

        $this->context->smarty->assign([
            'jet_price' => number_format($jet_price_before, 2, '.', ''),
            'jet_card_in' => $jet_card_in,
            'jet_products_id' => $jet_products_id,
            'jet_products_qt' => $jet_products_qt,
            'jet_products_pr' => $jet_products_pr,
            'jet_products_ct' => $jet_products_ct,
            'jet_products_vr' => $jet_products_vr,
            'is_vnoska' => $is_vnoska,
            'jet_sign_second' => $jet_sign_second,
            'jet_sign' => $jet_sign,
            'modalpayment_jet' => $modalpayment_jet,
            'jet_PopUp_Detailed_v1' => $jet_PopUp_Detailed_v1,
            'jet_Mask' => $jet_Mask,
            'jet_column_left' => $jet_column_left,
            'jet_column_right' => $jet_column_right,
            'jet_eur' => $jet_eur,
            'jet_vnoski' => $jet_vnoski,
            'obshto_credit_text' => $obshto_credit_text,
            'obshto_plashtania' => $obshto_plashtania,
            'jet_name' => $jet_name,
            'jet_lastname' => $jet_lastname,
            'jet_phone' => $jet_phone,
            'jet_email' => $jet_email
        ]);

        return $this->display(__FILE__, 'shoppingbag.tpl');
    }

    /**
     * @param array{cart?: Cart|null} $params
     */
    public function hookPaymentOptions($params)
    {
        if (empty($params['cart'])) {
            return [];
        }

        $cart = $params['cart'];

        if ($cart->isVirtualCart()) {
            return [];
        }

        $jet_currency_code = $this->context->currency->iso_code;
        if ($jet_currency_code != 'EUR' && $jet_currency_code != 'BGN') {
            return null;
        }

        $jet_status_in = (int)Configuration::get('JET_STATUS_IN');
        $jet_card_in = (int)Configuration::get('JET_CARD_IN');

        if ($jet_status_in == 0)
            return;

        $jet_price = $cart->getordertotal(true);

        $jet_eur = (int)Configuration::get("JET_EUR");
        $jet_sign = 'лв.';
        $jet_sign_second = 'евро';
        $jet_min_250 = JET_MIN_250;

        switch ($jet_eur) {
            case 0:
                $jet_sign = 'лв.';
                $jet_sign_second = '';
                break;
            case 1:
                if ($jet_currency_code == "EUR") {
                    $jet_price = number_format($jet_price * 1.95583, 2, ".", "");
                }
                $jet_sign = 'лв.';
                $jet_sign_second = 'евро';
                break;
            case 2:
                if ($jet_currency_code == "BGN") {
                    $jet_price = number_format($jet_price / 1.95583, 2, ".", "");
                }
                $jet_sign = 'евро';
                $jet_sign_second = 'лв.';
                $jet_min_250 = JET_MIN_250_EUR;
                break;
            case 3:
                if ($jet_currency_code == "BGN") {
                    $jet_price = number_format($jet_price / 1.95583, 2, ".", "");
                }
                $jet_sign = 'евро';
                $jet_sign_second = '';
                $jet_min_250 = JET_MIN_250_EUR;
                break;
        }

        $jet_vnoski_default = (int)Configuration::get("JET_VNOSKI_DEFAULT");
        if ($jet_price < $jet_min_250) {
            $jet_vnoski = '9';
        } else {
            $jet_vnoski = $jet_vnoski_default;
        }

        $jet_products = $cart->getProducts(true);
        $jet_products_id = '';
        $jet_products_qt = '';
        $jet_products_pr = '';
        $jet_products_ct = '';
        $jet_products_vr = '';
        foreach ($jet_products as $product) {
            $jet_products_id .= $product['id_product'] . '_';
            $jet_products_qt .= $product['cart_quantity'] . '_';
            $jet_products_pr_current = (float)$product['price_wt'];
            switch ($jet_eur) {
                case 0:
                    break;
                case 1:
                    if ($jet_currency_code == "EUR") {
                        $jet_products_pr_current = $jet_products_pr_current * 1.95583;
                    }
                    break;
                case 2:
                case 3:
                    if ($jet_currency_code == "BGN") {
                        $jet_products_pr_current = $jet_products_pr_current / 1.95583;
                    }
                    break;
            }
            $jet_products_pr .= number_format($jet_products_pr_current, 2, ".", "") . '_';
            $jet_products_ct .= $product['id_category_default'] . '_';
            $jet_products_vr .= $product['id_product_attribute'] . '_';
        }
        $jet_products_id = substr($jet_products_id, 0, -1);
        $jet_products_qt = substr($jet_products_qt, 0, -1);
        $jet_products_pr = substr($jet_products_pr, 0, -1);
        $jet_products_ct = substr($jet_products_ct, 0, -1);
        $jet_products_vr = substr($jet_products_vr, 0, -1);

        $useragent = array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $jet_is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i', $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($useragent, 0, 4));
        if ($jet_is_mobile) {
            $jet_column_left = "jetm_column_left";
            $jet_column_right = "jetm_column_right";
        } else {
            $jet_column_left = "jet_column_left";
            $jet_column_right = "jet_column_right";
        }

        $this->context->smarty->assign([
            'jet_price' => $cart->getordertotal(true),
            'jet_products_id' => $jet_products_id,
            'jet_products_qt' => $jet_products_qt,
            'jet_products_pr' => $jet_products_pr,
            'jet_products_ct' => $jet_products_ct,
            'jet_products_vr' => $jet_products_vr,
            'jet_column_left' => $jet_column_left,
            'jet_sign' => $jet_sign,
            'jet_column_right' => $jet_column_right,
            'jet_sign_second' => $jet_sign_second,
            'jet_eur' => $jet_eur,
            'jet_vnoski' => $jet_vnoski,
            'jet_action' => $this->context->link->getModuleLink($this->name, 'validation', ['jet_card' => 0], true),
            'jet_action_card' => $this->context->link->getModuleLink($this->name, 'validation', ['jet_card' => 1], true)
        ]);

        $payment_options = [];

        $newOption_JET = new PaymentOption();
        $newOption_JET->setModuleName($this->name);
        $newOption_JET->setCallToActionText('ПБ Лични Финанси');
        $newOption_JET->setAdditionalInformation($this->fetch('module:creditjet/views/templates/hook/jet_checkout.tpl'));
        $payment_options[] = $newOption_JET;

        if ($jet_card_in == 1) {
            $newOption_JET_CARD = new PaymentOption();
            $newOption_JET_CARD->setModuleName($this->name . 'card');
            $newOption_JET_CARD->setCallToActionText('ПБ Лични Финанси - на вноски с кредитна карта');
            $newOption_JET_CARD->setAdditionalInformation($this->fetch('module:creditjet/views/templates/hook/jet_checkout_card.tpl'));
            $payment_options[] = $newOption_JET_CARD;
        }

        return $payment_options;
    }
}

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

use PrestaShop\Module\CreditJet\Util\JetButtonSettings;
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
            $this->installConfigurationDefaults() &&
            $this->installDb();
    }

    /**
     * Първоначални стойности в ps_configuration при нова инсталация.
     */
    private function installConfigurationDefaults(): bool
    {
        $defaults = [
            'JET_STATUS_IN' => 1,
            'JET_EMAIL' => 'pf-online.shop@postbank.bg',
            'JET_PURCENT' => 1.40,
            'JET_VNOSKI_DEFAULT' => 12,
            'JET_CART_SHOW' => 1,
            'JET_CARD_IN' => 1,
            'JET_PURCENT_CARD' => 1.40,
            'JET_GAP' => 0,
            'JET_VNOSKA' => 1,
            'JET_MINPRICE' => '75',
            'JET_EUR' => 2,
            'JET_CREDIT_BUTTON_TYPE' => 'standard',
            'JET_CREDIT_BUTTON_SCHEME' => 0,
            'JET_CREDIT_BTN_TEXT' => 'Купи на изплащане с',
            'JET_CREDIT_BTN_TEXT_CARD' => 'На вноски с твоята кредитна карта',
            'JET_CREDIT_BTN_LOGO' => 1,
            'JET_CREDIT_BTN_MAX_WIDTH' => 570,
            'JET_CREDIT_BTN_ROUND' => 16,
            'JET_CREDIT_BTN_FONT' => 14,
        ];

        foreach ($defaults as $name => $value) {
            if (!Configuration::updateValue($name, $value)) {
                return false;
            }
        }

        return true;
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
            !Configuration::deleteByName('JET_CREDIT_BUTTON_TYPE') ||
            !Configuration::deleteByName('JET_CREDIT_BUTTON_SCHEME') ||
            !Configuration::deleteByName('JET_CREDIT_BTN_TEXT') ||
            !Configuration::deleteByName('JET_CREDIT_BTN_TEXT_CARD') ||
            !Configuration::deleteByName('JET_CREDIT_BTN_LOGO') ||
            !Configuration::deleteByName('JET_CREDIT_BTN_MAX_WIDTH') ||
            !Configuration::deleteByName('JET_CREDIT_BTN_ROUND') ||
            !Configuration::deleteByName('JET_CREDIT_BTN_FONT') ||
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
        $jetButtonContext = JetButtonSettings::getFrontContext();
        if ($jetButtonContext['jet_button_type'] === JetButtonSettings::BUTTON_TYPE_WIDE) {
            $wideCssPath = _PS_MODULE_DIR_ . $this->name . '/css/creditjet_wide_button.css';
            if (file_exists($wideCssPath)) {
                $this->context->controller->registerStylesheet(
                    'module-creditjet-wide-button-css',
                    'modules/' . $this->name . '/css/creditjet_wide_button.css',
                    [
                        'media' => 'all',
                        'priority' => 201,
                        'version' => filemtime($wideCssPath),
                    ]
                );
            }
        }

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

        $this->context->smarty->assign(array_merge([
            'jet_gap' => $jet_gap,
            'jet_price' => number_format($jet_price_before, 2, '.', ''),
            'jet_card_in' => $jet_card_in,
            'is_vnoska' => $is_vnoska,
            'jet_sign_second' => $jet_sign_second,
            'jet_sign' => $jet_sign,
            'jet_id_product' => $jet_id_product,
            'jet_eur' => $jet_eur,
            'jet_vnoski' => $jet_vnoski,
            'jet_name' => $jet_name,
            'jet_lastname' => $jet_lastname,
            'jet_email' => $jet_email,
            'jet_phone' => $jet_phone,
        ], $this->getJetButtonSmartyVars()));

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

        $this->context->smarty->assign(array_merge([
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
            'jet_eur' => $jet_eur,
            'jet_vnoski' => $jet_vnoski,
            'jet_name' => $jet_name,
            'jet_lastname' => $jet_lastname,
            'jet_phone' => $jet_phone,
            'jet_email' => $jet_email,
        ], $this->getJetButtonSmartyVars()));

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

        $this->context->smarty->assign([
            'jet_price' => $cart->getordertotal(true),
            'jet_products_id' => $jet_products_id,
            'jet_products_qt' => $jet_products_qt,
            'jet_products_pr' => $jet_products_pr,
            'jet_products_ct' => $jet_products_ct,
            'jet_products_vr' => $jet_products_vr,
            'jet_sign' => $jet_sign,
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

    /**
     * @return array<string, mixed>
     */
    private function getJetButtonSmartyVars(): array
    {
        return JetButtonSettings::getFrontContext();
    }
}

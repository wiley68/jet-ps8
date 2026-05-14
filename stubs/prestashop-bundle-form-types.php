<?php

/**
 * IDE / статичен анализ: Symfony Form + PrestaShop Admin Form типове.
 * В магазина класовете идват от vendor; при вече заредени класове този файл не дефинира нищо.
 */

declare(strict_types=1);

namespace Symfony\Component\Form {
    if (!interface_exists(FormBuilderInterface::class, false)) {
        interface FormBuilderInterface
        {
            /**
             * @return $this
             */
            public function add(string $child, ?string $type = null, array $options = []);
        }
    }

    if (!class_exists(AbstractType::class, false)) {
        abstract class AbstractType {}
    }
}

namespace Symfony\Component\Form\Extension\Core\Type {
    if (!class_exists(TextType::class, false)) {
        class TextType extends \Symfony\Component\Form\AbstractType {}
    }

    if (!class_exists(NumberType::class, false)) {
        class NumberType extends \Symfony\Component\Form\AbstractType {}
    }

    if (!class_exists(ChoiceType::class, false)) {
        class ChoiceType extends \Symfony\Component\Form\AbstractType {}
    }
}

namespace PrestaShopBundle\Form\Admin\Type {
    if (!class_exists(TranslatorAwareType::class, false)) {
        abstract class TranslatorAwareType extends \Symfony\Component\Form\AbstractType {}
    }

    if (!class_exists(SwitchType::class, false)) {
        class SwitchType extends \Symfony\Component\Form\AbstractType {}
    }
}

namespace PrestaShop\PrestaShop\Core\Form {
    if (!interface_exists(FormDataProviderInterface::class, false)) {
        interface FormDataProviderInterface
        {
            /**
             * @return array<string, mixed>
             */
            public function getData();

            /**
             * @param array<string, mixed> $data
             *
             * @return array<int|string, mixed>
             */
            public function setData(array $data);
        }
    }
}

namespace PrestaShop\PrestaShop\Core\Configuration {
    if (!interface_exists(DataConfigurationInterface::class, false)) {
        interface DataConfigurationInterface
        {
            /**
             * @return array<string, mixed>
             */
            public function getConfiguration();

            /**
             * @param array<string, mixed> $configuration
             *
             * @return array<int|string, mixed>
             */
            public function updateConfiguration(array $configuration);

            /**
             * @param array<string, mixed> $configuration
             */
            public function validateConfiguration(array $configuration): bool;
        }
    }
}

namespace PrestaShop\PrestaShop\Core {
    if (!interface_exists(ConfigurationInterface::class, false)) {
        interface ConfigurationInterface
        {
            /**
             * @param mixed $key
             *
             * @return mixed
             */
            public function get($key);

            /**
             * @param mixed $key
             * @param mixed $value
             */
            public function set($key, $value);
        }
    }
}

namespace Symfony\Component\HttpFoundation {
    if (!class_exists(Response::class, false)) {
        class Response {}
    }

    if (!class_exists(RedirectResponse::class, false)) {
        class RedirectResponse extends Response
        {
            public function __construct(string $url = '', int $status = 302) {}
        }
    }

    if (!class_exists(Request::class, false)) {
        class Request {}
    }
}

namespace Symfony\Bundle\FrameworkBundle\Controller {
    if (!class_exists(AbstractController::class, false)) {
        abstract class AbstractController
        {
            /** @return mixed */
            public function get(string $id)
            {
                return null;
            }

            /**
             * @param array<string, mixed> $parameters
             */
            public function render(string $view, array $parameters = [], ?\Symfony\Component\HttpFoundation\Response $response = null): \Symfony\Component\HttpFoundation\Response
            {
                return new \Symfony\Component\HttpFoundation\Response();
            }

            /**
             * @param array<string, mixed> $parameters
             */
            public function redirectToRoute(string $route, array $parameters = [], int $status = 302): \Symfony\Component\HttpFoundation\RedirectResponse
            {
                return new \Symfony\Component\HttpFoundation\RedirectResponse('');
            }

            public function addFlash(string $type, mixed $message = null): void {}
        }
    }
}

namespace {
    if (!class_exists('ObjectModel', false)) {
        abstract class ObjectModel
        {
            public const TYPE_INT = 1;

            public const TYPE_BOOL = 2;

            public const TYPE_STRING = 3;

            public const TYPE_FLOAT = 4;

            public const TYPE_DATE = 5;

            public const TYPE_HTML = 6;

            public const TYPE_NOTHING = 7;

            public const TYPE_SQL = 8;

            /**
             * @var array<string, mixed>
             */
            public static $definition = [];
        }
    }

    if (!function_exists('pSQL')) {
        /**
         * @param string $string
         */
        function pSQL($string, $html = false): string
        {
            return (string) $string;
        }
    }

    if (!function_exists('mysql_fetch_assoc')) {
        /**
         * @param resource $result
         * @return array<string, mixed>|false
         */
        function mysql_fetch_assoc($result)
        {
            return false;
        }
    }

    if (!class_exists('Context', false)) {
        class Context
        {
            protected static ?Context $instance = null;

            /** @var object{getModuleLink: callable, getPageLink: callable} */
            public object $link;

            /** @var object|Cart */
            public $cart;

            /** @var object{id: int} */
            public object $language;

            /** @var object{id: int} */
            public object $currency;

            /**
             * @var object{isLogged: callable, id: int, secure_key: string, getAddresses: callable}
             */
            public object $customer;

            /** @var object{id_cart: int} */
            public object $cookie;

            /** @var object{id: int} */
            public object $country;

            private function __construct()
            {
                $this->link = new class {
                    public function getModuleLink(string $module, string $controller = 'default', array $params = [], bool $ssl = false, ?int $lang = null): string
                    {
                        return '';
                    }

                    /**
                     * @param mixed ...$args
                     */
                    public function getPageLink(...$args): string
                    {
                        return '';
                    }
                };
                $this->cart = new class {
                    public int $id = 0;

                    public int $id_customer = 0;

                    public int $id_address_delivery = 0;

                    public int $id_address_invoice = 0;

                    /**
                     * @param mixed ...$args
                     */
                    public function getOrderTotal(...$args): float
                    {
                        return 0.0;
                    }

                    public function isVirtualCart(): bool
                    {
                        return false;
                    }
                };
                $this->language = new class {
                    public int $id = 0;
                };
                $this->currency = new class {
                    public int $id = 0;
                };
                $this->customer = new class {
                    public int $id = 0;

                    public string $secure_key = '';

                    public function isLogged(): bool
                    {
                        return false;
                    }

                    /**
                     * @return array<int, array{phone: string, id_address: int}>
                     */
                    public function getAddresses(int $id_lang): array
                    {
                        return [['phone' => '', 'id_address' => 0]];
                    }
                };
                $this->cookie = new class {
                    public int $id_cart = 0;
                };
                $this->country = new class {
                    public int $id = 0;
                };
            }

            public static function getContext(): Context
            {
                if (self::$instance === null) {
                    self::$instance = new Context();
                }

                return self::$instance;
            }
        }
    }

    if (!class_exists('DbQuery', false)) {
        class DbQuery
        {
            /** @return $this */
            public function select($fields)
            {
                return $this;
            }

            /** @return $this */
            public function from($table)
            {
                return $this;
            }

            /**
             * @param string $restriction
             * @return $this
             */
            public function where($restriction)
            {
                return $this;
            }

            /**
             * @param string $fields
             * @return $this
             */
            public function orderBy($fields)
            {
                return $this;
            }
        }
    }

    if (!class_exists('Db', false)) {
        class Db
        {
            public static function getInstance(): self
            {
                return new self();
            }

            public function execute(string $sql): bool
            {
                return true;
            }

            /**
             * @param DbQuery|string $sql
             * @return mixed
             */
            public function getValue($sql)
            {
                return false;
            }

            /**
             * @param string $table
             * @param array<string, mixed> $data
             */
            public function insert($table, $data, $null_values = false, $use_cache = true, $type = 1, $add_prefix = true): bool
            {
                return true;
            }

            /**
             * @param string|DbQuery $sql
             * @return object|false|resource
             */
            public function query($sql)
            {
                return false;
            }

            /**
             * @return list<array<string, mixed>>|false
             */
            public function executeS($sql)
            {
                return [];
            }
        }
    }

    if (!class_exists('Cart', false)) {
        class Cart
        {
            public const BOTH = 3;

            public const BOTH_WITHOUT_SHIPPING = 4;

            public int $id = 0;

            public int $id_customer = 0;

            public int $id_address_delivery = 0;

            public int $id_address_invoice = 0;

            public int $id_currency = 0;

            public int $id_lang = 0;

            public int $id_carrier = 0;

            public int $recyclable = 0;

            public int $gift = 0;

            public function isVirtualCart(): bool
            {
                return false;
            }

            /**
             * @param mixed ...$args
             */
            public function getOrderTotal(...$args): float
            {
                return 0.0;
            }

            /**
             * @param mixed ...$args
             * @return array<int, array<string, mixed>>
             */
            public function getProducts(...$args): array
            {
                return [];
            }

            public function add(): bool
            {
                return true;
            }

            /**
             * @param mixed ...$args
             */
            public function updateQty(...$args): bool
            {
                return true;
            }

            public function delete(): bool
            {
                return true;
            }
        }
    }

    if (!class_exists('Currency', false)) {
        class Currency
        {
            public int $id = 0;

            /**
             * @param int|null $id
             * @param int|null $id_lang
             * @param int|null $id_shop
             */
            public function __construct($id = null, $id_lang = null, $id_shop = null)
            {
                if ($id !== null) {
                    $this->id = (int) $id;
                }
            }

            /**
             * @param string $isoCode
             * @param mixed ...$rest
             * @return int|false
             */
            public static function getIdByIsoCode($isoCode, ...$rest)
            {
                return false;
            }
        }
    }

    if (!class_exists('Language', false)) {
        class Language
        {
            public int $id = 0;

            /**
             * @param int|null $id
             * @param mixed ...$rest
             */
            public function __construct($id = null, ...$rest)
            {
                if ($id !== null) {
                    $this->id = (int) $id;
                }
            }
        }
    }

    if (!class_exists('Country', false)) {
        class Country
        {
            public int $id = 0;

            /**
             * @param int|null $id
             * @param mixed ...$rest
             */
            public function __construct($id = null, ...$rest)
            {
                if ($id !== null) {
                    $this->id = (int) $id;
                }
            }
        }
    }

    if (!class_exists('Order', false)) {
        class Order
        {
            public int $id = 0;

            /**
             * @param int|null $id
             * @param mixed ...$rest
             */
            public function __construct($id = null, ...$rest)
            {
                if ($id !== null) {
                    $this->id = (int) $id;
                }
            }

            /**
             * @param int $id_cart
             * @return int|false
             */
            public static function getOrderByCartId(int $id_cart)
            {
                return false;
            }
        }
    }

    if (!class_exists('Category', false)) {
        class Category
        {
            public string $name = '';

            /**
             * @param int|null $id
             * @param int|null $id_lang
             * @param mixed ...$rest
             */
            public function __construct($id = null, $id_lang = null, ...$rest) {}
        }
    }

    if (!class_exists('Product', false)) {
        class Product
        {
            public int $id = 0;

            public int $id_category_default = 0;

            public string $name = '';

            /**
             * @param int|null $id
             * @param mixed ...$rest
             */
            public function __construct($id = null, ...$rest)
            {
                if ($id !== null) {
                    $this->id = (int) $id;
                }
            }

            /**
             * @param mixed ...$args
             */
            public static function getPriceStatic(...$args): float
            {
                return 0.0;
            }
        }
    }

    if (!class_exists('Mail', false)) {
        class Mail
        {
            /**
             * @param mixed ...$args
             */
            public static function Send(...$args): bool
            {
                return true;
            }
        }
    }

    if (!class_exists('Customer', false)) {
        class Customer
        {
            public int $id = 0;

            public string $firstname = '';

            public string $lastname = '';

            public string $email = '';

            public string $secure_key = '';

            /**
             * @param int|null $id
             * @param mixed ...$rest
             */
            public function __construct($id = null, ...$rest)
            {
                if ($id !== null) {
                    $this->id = (int) $id;
                }
            }

            /**
             * @return array<int, array{phone: string}>
             */
            public function getAddresses(int $id_lang): array
            {
                return [['phone' => '']];
            }
        }
    }

    if (!class_exists('Validate', false)) {
        class Validate
        {
            public static function isLoadedObject($object): bool
            {
                return false;
            }

            public static function isUnsignedInt($value): bool
            {
                return true;
            }
        }
    }

    if (!class_exists('Tools', false)) {
        class Tools
        {
            public static function redirectAdmin(string $url): void {}

            /**
             * @param mixed $url
             * @param mixed $base_uri
             * @param object|null $link
             * @param mixed $headers
             */
            public static function redirect($url, $base_uri = '', $link = null, $headers = null): void {}

            /**
             * @param mixed $default
             */
            public static function getValue(string $key, $default = false): mixed
            {
                return $default;
            }
        }
    }

    if (!class_exists('Controller', false)) {
        class Controller
        {
            /** @var Context */
            public $context;

            /** @var Module|null */
            public $module;
        }
    }

    if (!class_exists('FrontController', false)) {
        class FrontController extends Controller
        {
            public bool $ajax = false;

            public function initContent() {}
        }
    }

    if (!class_exists('ModuleFrontController', false)) {
        class ModuleFrontController extends FrontController {}
    }
}

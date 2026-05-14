<?php

/**
 * Минимални декларации за IDE и статичен анализ, когато модулът се отваря извън пълното дърво на PrestaShop.
 * В магазина ядрото зарежда истинските класове преди vendor/autoload.php — тогава `class_exists` предотвратява дублиране.
 */

declare(strict_types=1);

if (!defined('_MYSQL_ENGINE_')) {
    define('_MYSQL_ENGINE_', 'InnoDB');
}

if (!defined('_DB_PREFIX_')) {
    define('_DB_PREFIX_', 'ps_');
}

if (!defined('_PS_MODULE_DIR_')) {
    define('_PS_MODULE_DIR_', dirname(__DIR__, 2) . DIRECTORY_SEPARATOR);
}

if (!class_exists('Module', false)) {
    final class _CreditJetIdeContext
    {
        /** @var object{php_self: string, registerJavascript: callable, registerStylesheet: callable} */
        public object $controller;

        /** @var object{iso_code: string} */
        public object $currency;

        /** @var object{getAddresses: callable} */
        public object $customer;

        /** @var object{id: int} */
        public object $language;

        /** @var object{assign: callable} */
        public object $smarty;

        /** @var object{getModuleLink: callable} */
        public object $link;

        public function __construct()
        {
            $this->controller = new class {
                public string $php_self = '';

                public function registerJavascript(string $id, string $path, array $params = []): void {}

                public function registerStylesheet(string $id, string $path, array $params = []): void {}
            };
            $this->currency = new class {
                public string $iso_code = '';
            };
            $this->customer = new class {
                /** @return array<int, array{phone: string}> */
                public function getAddresses(int $id_lang): array
                {
                    return [['phone' => '']];
                }
            };
            $this->language = new class {
                public int $id = 0;
            };
            $this->smarty = new class {
                /** @param array<string, mixed> $tpl_vars */
                public function assign(array $tpl_vars): void {}
            };
            $this->link = new class {
                public function getModuleLink(string $module, string $controller = 'default', array $params = [], bool $ssl = false, ?int $lang = null): string
                {
                    return '';
                }
            };
        }
    }

    abstract class Module
    {
        public ?int $id = null;

        public ?int $currentOrder = null;

        public string $name = '';

        public string $tab = '';

        public string $version = '';

        public string $author = '';

        public int $need_instance = 0;

        public bool $bootstrap = false;

        /** @var array<string, string> */
        public array $ps_versions_compliancy = [];

        public string $displayName = '';

        public string $description = '';

        public string $confirmUninstall = '';

        public string $warning = '';

        public _CreditJetIdeContext $context;

        public function __construct()
        {
            $this->context = new _CreditJetIdeContext();
        }

        /**
         * @param string|list<string> $hook_name
         */
        public function registerHook($hook_name): bool
        {
            return true;
        }

        public function get(string $id): object
        {
            return new class {
                public function generate(string $route, array $parameters = [], int $referenceType = 1): string
                {
                    return '';
                }
            };
        }

        public function getCurrency(int $id_currency): object|false
        {
            return false;
        }

        public function display(string $file, string $template, ?string $cache_id = null, ?string $compile_id = null): string
        {
            return '';
        }

        public function fetch(string $template, ?string $cache_id = null, ?string $compile_id = null): string
        {
            return '';
        }

        public function install()
        {
            return true;
        }

        public function uninstall()
        {
            return true;
        }

        /**
         * @param mixed ...$args
         */
        public function validateOrder(...$args): void {}

        /**
         * @return array<int, array<string, mixed>>
         */
        public static function getPaymentModules($context = null): array
        {
            return [];
        }

        /**
         * @param string $name
         * @return static|false
         */
        public static function getInstanceByName(string $name)
        {
            return false;
        }
    }
}

if (!class_exists('PaymentModule', false)) {
    abstract class PaymentModule extends Module
    {
        public function __construct()
        {
            parent::__construct();
        }

        public function install()
        {
            return parent::install();
        }

        public function uninstall()
        {
            return parent::uninstall();
        }
    }
}

if (!class_exists('Configuration', false)) {
    class Configuration
    {
        public static function get(string $key, ?int $id_lang = null, ?int $id_shop = null): mixed
        {
            return false;
        }

        /**
         * @param mixed $values
         */
        public static function updateValue(string $key, $values, bool $html = false, int $id_shop_group = 0, int $id_shop = 0): bool
        {
            return true;
        }

        public static function deleteByName(string $key): bool
        {
            return true;
        }
    }
}

if (!class_exists('Shop', false)) {
    class Shop
    {
        public const CONTEXT_ALL = 4;

        public const CONTEXT_GROUP = 2;

        public const CONTEXT_SHOP = 1;

        public static function isFeatureActive(): bool
        {
            return false;
        }

        /**
         * @param int $type
         * @param int|null $id
         */
        public static function setContext($type, $id = null): void {}
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
         * @return list<array<string, mixed>>|false
         */
        public function executeS($sql)
        {
            return [];
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

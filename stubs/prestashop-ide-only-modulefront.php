<?php

/**
 * Само за IDE (Intelephense/PHPStan): не се включва в composer autoload.
 * Не require-вайте в магазина — имената съвпадат с PrestaShop и при ранно зареждане
 * ще заместят реалните класове (липсващи методи → fatal).
 */

declare(strict_types=1);

namespace {
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

            public function initContent(): void {}
        }
    }

    if (!class_exists('ModuleFrontController', false)) {
        class ModuleFrontController extends FrontController {}
    }

    if (!class_exists('Currency', false)) {
        class Currency
        {
            public int $id = 0;

            public string $iso_code = '';

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

            /**
             * @param mixed ...$args
             * @return array<int, self>
             */
            public static function findAllInstalled(...$args): array
            {
                return [];
            }
        }
    }
}

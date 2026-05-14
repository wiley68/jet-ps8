<?php

/**
 * IDE / статичен анализ: PrestaShop Back Office базов контролер.
 */

declare(strict_types=1);

namespace PrestaShopBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

if (!class_exists(FrameworkBundleAdminController::class, false)) {
    class FrameworkBundleAdminController extends AbstractController
    {
        /**
         * @param array<int|string, mixed> $errorMessages
         */
        protected function flashErrors(array $errorMessages): void {}
    }
}

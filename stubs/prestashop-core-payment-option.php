<?php

/**
 * IDE / статичен анализ: PrestaShop\Core\Payment\PaymentOption.
 * В магазина класът идва от ядрото; при зареден реален клас този файл не дефинира нищо.
 */

declare(strict_types=1);

namespace PrestaShop\PrestaShop\Core\Payment;

if (!class_exists(PaymentOption::class, false)) {
    class PaymentOption
    {
        /**
         * @return $this
         */
        public function setModuleName($moduleName)
        {
            return $this;
        }

        /**
         * @return $this
         */
        public function setCallToActionText($callToActionText)
        {
            return $this;
        }

        /**
         * @return $this
         */
        public function setAdditionalInformation($additionalInformation)
        {
            return $this;
        }
    }
}

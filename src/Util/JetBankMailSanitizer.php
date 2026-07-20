<?php

declare(strict_types=1);

namespace PrestaShop\Module\CreditJet\Util;

/**
 * Почиства стойности за мейла към банката.
 * Всеки ред завършва с „;“ — наличието на този знак (или нови редове) в данните чупи парсинга.
 */
final class JetBankMailSanitizer
{
    public static function field(mixed $value): string
    {
        return str_replace([';', "\r", "\n"], '', (string) $value);
    }
}

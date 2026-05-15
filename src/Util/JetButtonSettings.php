<?php

declare(strict_types=1);

namespace PrestaShop\Module\CreditJet\Util;

use Configuration;
use PrestaShop\Module\CreditJet\Form\CreditJetConfigurationDataConfiguration;

final class JetButtonSettings
{
    public const BUTTON_TYPE_STANDARD = 'standard';
    public const BUTTON_TYPE_WIDE = 'wide';

    public const DEFAULT_BTN_TEXT = 'Купи на изплащане с';
    public const DEFAULT_BTN_TEXT_CARD = 'На вноски с твоята кредитна карта';

    /**
     * @return array{
     *     jet_button_type: string,
     *     jet_button_scheme: int,
     *     jet_btn_text: string,
     *     jet_btn_text_card: string,
     *     jet_btn_logo: int,
     *     jet_btn_max_width: int,
     *     jet_btn_round: int,
     *     jet_btn_font: int,
     *     jet_wide_wrap_style: string
     * }
     */
    public static function getFrontContext(): array
    {
        $scheme = self::normalizeScheme(Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BUTTON_SCHEME));
        $maxWidth = self::normalizeBtnMaxWidth(Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BTN_MAX_WIDTH));
        $round = self::normalizeBtnRound(Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BTN_ROUND));
        $font = self::normalizeBtnFont(Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BTN_FONT));

        return [
            'jet_button_type' => self::normalizeButtonType(Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BUTTON_TYPE)),
            'jet_button_scheme' => $scheme,
            'jet_btn_text' => self::normalizeBtnText(
                Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BTN_TEXT),
                self::DEFAULT_BTN_TEXT
            ),
            'jet_btn_text_card' => self::normalizeBtnText(
                Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BTN_TEXT_CARD),
                self::DEFAULT_BTN_TEXT_CARD
            ),
            'jet_btn_logo' => self::normalizeBtnLogo(Configuration::get(CreditJetConfigurationDataConfiguration::JET_CREDIT_BTN_LOGO)),
            'jet_btn_max_width' => $maxWidth,
            'jet_btn_round' => $round,
            'jet_btn_font' => $font,
            'jet_wide_wrap_style' => self::buildWrapInlineStyle($scheme, $maxWidth, $round, $font),
        ];
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return array<string, mixed>
     */
    public static function getAdminPreviewContext(array $configuration, string $moduleImgBaseUrl): array
    {
        $buttonType = self::normalizeButtonType($configuration['jet_button_type'] ?? null);
        $scheme = self::normalizeScheme($configuration['jet_button_scheme'] ?? 0);
        $maxWidth = self::normalizeBtnMaxWidth($configuration['jet_btn_max_width'] ?? null);
        $round = self::normalizeBtnRound($configuration['jet_btn_round'] ?? null);
        $font = self::normalizeBtnFont($configuration['jet_btn_font'] ?? null);
        $btnText = self::normalizeBtnText($configuration['jet_btn_text'] ?? null, self::DEFAULT_BTN_TEXT);
        $btnTextCard = self::normalizeBtnText($configuration['jet_btn_text_card'] ?? null, self::DEFAULT_BTN_TEXT_CARD);
        $btnLogo = self::normalizeBtnLogo($configuration['jet_btn_logo'] ?? null);

        $schemeStyle = JetButtonScheme::wrapInlineStyle($scheme);
        $wrapStyle = self::buildWrapInlineStyle($scheme, $maxWidth, $round, $font);

        $schemeStylesForJs = [];
        foreach (JetButtonScheme::getSchemes() as $k => $_s) {
            $schemeStylesForJs[(string) $k] = JetButtonScheme::wrapInlineStyle($k);
        }

        $schemeLabels = array_map(
            static fn(array $s): string => $s['label'] ?? '',
            JetButtonScheme::getSchemes()
        );

        $activeScheme = JetButtonScheme::getScheme($scheme);

        return [
            'jet_button_schemes' => JetButtonScheme::getSchemes(),
            'jet_button_scheme' => $scheme,
            'jet_button_scheme_label' => is_array($activeScheme) ? ($activeScheme['label'] ?? '') : '',
            'jet_button_type' => $buttonType,
            'jet_btn_text' => $btnText,
            'jet_btn_text_card' => $btnTextCard,
            'jet_btn_logo' => $btnLogo,
            'jet_btn_max_width' => $maxWidth,
            'jet_btn_round' => $round,
            'jet_btn_font' => $font,
            'jet_preview_jet' => $moduleImgBaseUrl . 'jet.png',
            'jet_preview_jet_logo' => $moduleImgBaseUrl . 'jet_logo.png',
            'jet_wide_button_wrap_style' => $wrapStyle,
            'jet_button_scheme_styles_for_js' => json_encode($schemeStylesForJs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'jet_button_scheme_labels_json' => json_encode($schemeLabels, JSON_UNESCAPED_UNICODE),
        ];
    }

    public static function buildWrapInlineStyle(int $scheme, int $maxWidth, int $round, int $font): string
    {
        return JetButtonScheme::wrapInlineStyle($scheme)
            . '--jet-wide-max-width:' . $maxWidth . 'px;'
            . '--jet-wide-radius:' . $round . 'px;'
            . '--jet-wide-font-size:' . $font . 'px;';
    }

    public static function normalizeButtonType(mixed $value): string
    {
        return $value === self::BUTTON_TYPE_WIDE ? self::BUTTON_TYPE_WIDE : self::BUTTON_TYPE_STANDARD;
    }

    public static function normalizeScheme(mixed $value): int
    {
        if ($value === null || $value === '' || $value === false) {
            return 0;
        }

        return JetButtonScheme::normalizeIndex($value);
    }

    public static function normalizeBtnText(mixed $value, string $default): string
    {
        $t = trim((string) ($value ?? ''));

        return $t === '' ? $default : $t;
    }

    public static function normalizeBtnLogo(mixed $value): int
    {
        if ($value === null || $value === '' || $value === false) {
            return 1;
        }

        return (int) $value ? 1 : 0;
    }

    public static function normalizeBtnMaxWidth(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 570;
        }
        $n = (int) $value;
        if ($n < 30) {
            return 30;
        }
        if ($n > 1200) {
            return 1200;
        }

        return $n;
    }

    public static function normalizeBtnRound(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 16;
        }
        $n = (int) $value;
        if ($n < 0) {
            return 0;
        }
        if ($n > 25) {
            return 25;
        }

        return $n;
    }

    public static function normalizeBtnFont(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 14;
        }
        $n = (int) $value;
        if ($n < 6) {
            return 6;
        }
        if ($n > 36) {
            return 36;
        }

        return $n;
    }

    /**
     * @param array<string, mixed> $configuration
     *
     * @return array<string, mixed>
     */
    public static function normalizeForSave(array $configuration): array
    {
        $configuration['jet_button_type'] = self::normalizeButtonType($configuration['jet_button_type'] ?? null);
        $configuration['jet_button_scheme'] = self::normalizeScheme($configuration['jet_button_scheme'] ?? 0);
        $configuration['jet_btn_text'] = self::normalizeBtnText($configuration['jet_btn_text'] ?? null, self::DEFAULT_BTN_TEXT);
        $configuration['jet_btn_text_card'] = self::normalizeBtnText($configuration['jet_btn_text_card'] ?? null, self::DEFAULT_BTN_TEXT_CARD);
        $configuration['jet_btn_logo'] = self::normalizeBtnLogo($configuration['jet_btn_logo'] ?? null);
        $configuration['jet_btn_max_width'] = self::normalizeBtnMaxWidth($configuration['jet_btn_max_width'] ?? null);
        $configuration['jet_btn_round'] = self::normalizeBtnRound($configuration['jet_btn_round'] ?? null);
        $configuration['jet_btn_font'] = self::normalizeBtnFont($configuration['jet_btn_font'] ?? null);

        return $configuration;
    }
}

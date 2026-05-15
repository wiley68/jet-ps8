<?php

declare(strict_types=1);

namespace PrestaShop\Module\CreditJet\Form;

use PrestaShop\Module\CreditJet\Util\JetButtonSettings;
use PrestaShop\PrestaShop\Core\Configuration\DataConfigurationInterface;
use PrestaShop\PrestaShop\Core\ConfigurationInterface;

/**
 * Configuration is used to save data to configuration table and retrieve from it.
 */
final class CreditJetConfigurationDataConfiguration implements DataConfigurationInterface
{
    public const JET_STATUS_IN = 'JET_STATUS_IN';
    public const JET_EMAIL = 'JET_EMAIL';
    public const JET_ID = 'JET_ID';
    public const JET_PURCENT = 'JET_PURCENT';
    public const JET_VNOSKI_DEFAULT = 'JET_VNOSKI_DEFAULT';
    public const JET_CART_SHOW = 'JET_CART_SHOW';
    public const JET_CARD_IN = 'JET_CARD_IN';
    public const JET_PURCENT_CARD = 'JET_PURCENT_CARD';
    public const JET_COUNT = 'JET_COUNT';
    public const JET_GAP = 'JET_GAP';
    public const JET_VNOSKA = 'JET_VNOSKA';
    public const JET_MINPRICE = 'JET_MINPRICE';
    public const JET_EUR = 'JET_EUR';
    public const JET_CREDIT_BUTTON_TYPE = 'JET_CREDIT_BUTTON_TYPE';
    public const JET_CREDIT_BUTTON_SCHEME = 'JET_CREDIT_BUTTON_SCHEME';
    public const JET_CREDIT_BTN_TEXT = 'JET_CREDIT_BTN_TEXT';
    public const JET_CREDIT_BTN_TEXT_CARD = 'JET_CREDIT_BTN_TEXT_CARD';
    public const JET_CREDIT_BTN_LOGO = 'JET_CREDIT_BTN_LOGO';
    public const JET_CREDIT_BTN_MAX_WIDTH = 'JET_CREDIT_BTN_MAX_WIDTH';
    public const JET_CREDIT_BTN_ROUND = 'JET_CREDIT_BTN_ROUND';
    public const JET_CREDIT_BTN_FONT = 'JET_CREDIT_BTN_FONT';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration(): array
    {
        $return = [];

        $return['jet_status_in'] = $this->configuration->get(static::JET_STATUS_IN);
        $return['jet_email'] = $this->configuration->get(static::JET_EMAIL);
        $return['jet_id'] = $this->configuration->get(static::JET_ID);
        $return['jet_purcent'] = $this->floatConfig(static::JET_PURCENT);
        $return['jet_vnoski_default'] = $this->configuration->get(static::JET_VNOSKI_DEFAULT);
        $return['jet_cart_show'] = $this->configuration->get(static::JET_CART_SHOW);
        $return['jet_card_in'] = $this->configuration->get(static::JET_CARD_IN);
        $return['jet_purcent_card'] = $this->floatConfig(static::JET_PURCENT_CARD);
        $return['jet_count'] = $this->configuration->get(static::JET_COUNT);
        $return['jet_gap'] = $this->configuration->get(static::JET_GAP);
        $return['jet_vnoska'] = $this->configuration->get(static::JET_VNOSKA);
        $return['jet_minprice'] = $this->configuration->get(static::JET_MINPRICE);
        $return['jet_eur'] = $this->configuration->get(static::JET_EUR);
        $return['jet_button_type'] = JetButtonSettings::normalizeButtonType($this->configuration->get(static::JET_CREDIT_BUTTON_TYPE));
        $return['jet_button_scheme'] = JetButtonSettings::normalizeScheme($this->configuration->get(static::JET_CREDIT_BUTTON_SCHEME));
        $return['jet_btn_text'] = JetButtonSettings::normalizeBtnText(
            $this->configuration->get(static::JET_CREDIT_BTN_TEXT),
            JetButtonSettings::DEFAULT_BTN_TEXT
        );
        $return['jet_btn_text_card'] = JetButtonSettings::normalizeBtnText(
            $this->configuration->get(static::JET_CREDIT_BTN_TEXT_CARD),
            JetButtonSettings::DEFAULT_BTN_TEXT_CARD
        );
        $return['jet_btn_logo'] = JetButtonSettings::normalizeBtnLogo(
            $this->configuration->get(static::JET_CREDIT_BTN_LOGO)
        );
        $return['jet_btn_max_width'] = JetButtonSettings::normalizeBtnMaxWidth($this->configuration->get(static::JET_CREDIT_BTN_MAX_WIDTH));
        $return['jet_btn_round'] = JetButtonSettings::normalizeBtnRound($this->configuration->get(static::JET_CREDIT_BTN_ROUND));
        $return['jet_btn_font'] = JetButtonSettings::normalizeBtnFont($this->configuration->get(static::JET_CREDIT_BTN_FONT));

        return $return;
    }

    public function updateConfiguration(array $configuration): array
    {
        $errors = [];

        if ($this->validateConfiguration($configuration)) {
            $configuration = JetButtonSettings::normalizeForSave($configuration);

            $this->configuration->set(static::JET_STATUS_IN, $configuration['jet_status_in']);
            $this->configuration->set(static::JET_EMAIL, $configuration['jet_email']);
            $this->configuration->set(static::JET_ID, $configuration['jet_id']);
            $this->configuration->set(static::JET_PURCENT, $configuration['jet_purcent']);
            $this->configuration->set(static::JET_VNOSKI_DEFAULT, $configuration['jet_vnoski_default']);
            $this->configuration->set(static::JET_CART_SHOW, $configuration['jet_cart_show']);
            $this->configuration->set(static::JET_CARD_IN, $configuration['jet_card_in']);
            $this->configuration->set(static::JET_PURCENT_CARD, $configuration['jet_purcent_card']);
            $this->configuration->set(static::JET_COUNT, $configuration['jet_count'] ? $configuration['jet_count'] : 1);
            $this->configuration->set(static::JET_GAP, $configuration['jet_gap']);
            $this->configuration->set(static::JET_VNOSKA, $configuration['jet_vnoska']);
            $this->configuration->set(static::JET_MINPRICE, $configuration['jet_minprice']);
            $this->configuration->set(static::JET_EUR, $configuration['jet_eur']);
            $this->configuration->set(static::JET_CREDIT_BUTTON_TYPE, $configuration['jet_button_type']);
            $this->configuration->set(static::JET_CREDIT_BUTTON_SCHEME, $configuration['jet_button_scheme']);
            $this->configuration->set(static::JET_CREDIT_BTN_TEXT, $configuration['jet_btn_text']);
            $this->configuration->set(static::JET_CREDIT_BTN_TEXT_CARD, $configuration['jet_btn_text_card']);
            $this->configuration->set(static::JET_CREDIT_BTN_LOGO, $configuration['jet_btn_logo']);
            $this->configuration->set(static::JET_CREDIT_BTN_MAX_WIDTH, $configuration['jet_btn_max_width']);
            $this->configuration->set(static::JET_CREDIT_BTN_ROUND, $configuration['jet_btn_round']);
            $this->configuration->set(static::JET_CREDIT_BTN_FONT, $configuration['jet_btn_font']);
        }

        /* Errors are returned here. */
        return $errors;
    }

    /**
     * Ensure the parameters passed are valid.
     *
     * @return bool Returns true if no exception are thrown
     */
    public function validateConfiguration(array $configuration): bool
    {
        return
            isset($configuration['jet_status_in']);
    }

    /**
     * Configuration винаги връща низ; ChoiceType с float стойности изисква истински float за съвпадение.
     */
    private function floatConfig(string $key): ?float
    {
        $raw = $this->configuration->get($key);
        if ($raw === false || $raw === null || $raw === '') {
            return null;
        }

        return (float) $raw;
    }
}

<?php

declare(strict_types=1);

namespace PrestaShop\Module\CreditJet\Form;

use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class CreditJetConfigurationType extends TranslatorAwareType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'jet_status_in',
                SwitchType::class,
                [
                    'label' => 'Включи модула',
                    'help' => 'Показвай бутоните за закупуване на кредит през ПБ Лични Финанси',
                ]
            )
            ->add('jet_email', TextType::class, [
                'label' => 'Избери email за изпращане',
                'help' => 'Избери email адрес на който ще се изпращат заявките от клиента. Използвайте "pf-online.shop@postbank.bg" за изпращане на заявките към ПБ Лични Финанси. Можете да въведете повече от един мейл адрес, като ги разделите помежду си със запетая. (Ако оставите празно ще се използва email-а по-подразбиране за системата. Препоръчва се използването на email извън Вашия домейн за да се избегне ауто спан защитата.)',
                'required' => true,
            ])
            ->add('jet_id', TextType::class, [
                'label' => 'Избери идентификатор на магазина за изпращане',
                'help' => 'Избери идентификатор на магзина, който да се изпраща към Банката заедно със заявката за лизинг. По този идентификатор Банката ще разпознава Вашите заявки',
                'required' => true,
            ])
            ->add('jet_purcent', ChoiceType::class, [
                'label' => 'Избор на процент лихва',
                'help' => 'Избор на процент лихва',
                'required' => true,
                'choices'  => [
                    '0.00%' => 0.00,
                    '0.80%' => 0.80,
                    '0.99%' => 0.99,
                    '1.00%' => 1.00,
                    '1.10%' => 1.10,
                    '1.20%' => 1.20,
                    '1.40%' => 1.40,
                ],
                'empty_data' => 1.40,
            ])
            ->add('jet_vnoski_default', ChoiceType::class, [
                'label' => 'Избор на брой вноски по-подразбиране',
                'help' => 'Избор на брой вноски по-подразбиране в продуктовата страница',
                'required' => true,
                'choices'  => [
                    '3 месеца' => 3,
                    '6 месеца' => 6,
                    '9 месеца' => 9,
                    '12 месеца' => 12,
                    '15 месеца' => 15,
                    '18 месеца' => 18,
                    '24 месеца' => 24,
                    '30 месеца' => 30,
                    '36 месеца' => 36,
                ],
                'empty_data' => 12,
            ])
            ->add(
                'jet_cart_show',
                SwitchType::class,
                [
                    'label' => 'Покажи бутона в количката',
                    'help' => 'Показвай бутона за закупуване на кредит през ПБ Лични Финанси в количката',
                ],
            )
            ->add(
                'jet_card_in',
                SwitchType::class,
                [
                    'label' => 'Покажи бутона за изпращане чрез кредитна карта',
                    'help' => 'Показвай бутона за закупуване на кредит през ПБ Лични Финанси със заявката за лизинг направена по метода с кредитна карта',
                ],
            )
            ->add('jet_purcent_card', ChoiceType::class, [
                'label' => 'Процент за лихва чрез кредитна карта',
                'help' => 'Избор на таблица за процент за лихва за заявката за лизинг направена по метода с кредитна карта',
                'required' => true,
                'choices'  => [
                    '0.00%' => 0.00,
                    '0.80%' => 0.80,
                    '0.90%' => 0.90,
                    '0.99%' => 0.99,
                    '1.00%' => 1.00,
                    '1.10%' => 1.10,
                    '1.20%' => 1.20,
                    '1.40%' => 1.40,
                ],
                'empty_data' => 1.00,
            ])
            ->add('jet_count', TextType::class, [
                'label' => '№ Поръчка',
                'help' => 'Номер на текущата поръчка за лизинг',
                'disabled' => true,
                'required' => false,
            ])
            ->add('jet_gap', NumberType::class, [
                'label' => 'Празно място над бутона',
                'help' => 'Празно място над бутона в px. Използва се за подредба на бутоните, когато са повече от един',
                'required' => false,
                'empty_data' => 0,
            ])
            ->add('jet_button_type', ChoiceType::class, [
                'label' => 'Вид на бутона',
                'help' => 'От тук можете да си изберете вида на бутоните които ще се показват в продуктовата и в страницата количка.',
                'required' => true,
                'choices' => [
                    'Стандартен бутон' => 'standard',
                    'Персонализиран бутон' => 'wide',
                ],
                'empty_data' => 'standard',
                'attr' => ['id' => 'creditjet_button_type'],
            ])
            ->add('jet_button_scheme', NumberType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => 0,
                'attr' => ['id' => 'creditjet_button_scheme', 'class' => 'creditjet-scheme-value'],
            ])
            ->add('jet_btn_text', TextType::class, [
                'label' => 'Текст на основния бутон',
                'help' => 'Текстът в първия ред на персонализирания бутон.',
                'required' => false,
                'empty_data' => 'Купи на изплащане с',
                'attr' => ['maxlength' => 160, 'id' => 'creditjet_btn_text'],
            ])
            ->add('jet_btn_text_card', TextType::class, [
                'label' => 'Текст на картовия бутон',
                'help' => 'Текстът в първия ред на бутона за кредитна карта.',
                'required' => false,
                'empty_data' => 'На вноски с твоята кредитна карта',
                'attr' => ['maxlength' => 160, 'id' => 'creditjet_btn_text_card'],
            ])
            ->add('jet_btn_logo', SwitchType::class, [
                'label' => 'Покажи лого в бутона',
                'help' => 'Определя дали да се показва логото на ПБ Лични Финанси в персонализирания бутон.',
                'attr' => ['id' => 'creditjet_btn_logo'],
            ])
            ->add('jet_btn_max_width', NumberType::class, [
                'label' => 'Максимална ширина на бутона',
                'help' => 'Ширина в px. Може да зададете стойност ръчно или с плъзгача (30–1200).',
                'required' => false,
                'empty_data' => 570,
                'attr' => [
                    'min' => 30,
                    'max' => 1200,
                    'step' => 1,
                    'id' => 'creditjet_btn_max_width',
                    'class' => 'creditjet-number-narrow',
                    'inputmode' => 'numeric',
                ],
            ])
            ->add('jet_btn_round', NumberType::class, [
                'label' => 'Радиус на закръгление',
                'help' => 'Радиус в px. 0 означава бутони без закръгление. Стойност ръчно или плъзгач (0–25).',
                'required' => false,
                'empty_data' => 16,
                'attr' => [
                    'min' => 0,
                    'max' => 25,
                    'step' => 1,
                    'id' => 'creditjet_btn_round',
                    'class' => 'creditjet-number-narrow',
                    'inputmode' => 'numeric',
                ],
            ])
            ->add('jet_btn_font', NumberType::class, [
                'label' => 'Размер на шрифт в бутона',
                'help' => 'Размер в px за текстовете в персонализирания бутон (6–36). Стойност ръчно или плъзгач.',
                'required' => false,
                'empty_data' => 14,
                'attr' => [
                    'min' => 6,
                    'max' => 36,
                    'step' => 1,
                    'id' => 'creditjet_btn_font',
                    'class' => 'creditjet-number-narrow',
                    'inputmode' => 'numeric',
                ],
            ])
            ->add(
                'jet_vnoska',
                SwitchType::class,
                [
                    'label' => 'Покажи вноска',
                    'help' => 'Дали да се показва текст, под бутона указващ месечната вноска за избрания период на лизинг?',
                ],
            )
            ->add('jet_minprice', NumberType::class, [
                'label' => 'Минимална сума',
                'help' => 'Минимално възможната сума на стоките за закупуване на кредит през ПБ Лични Финанси',
                'required' => true,
                'unit' => 'лв.',
                'empty_data' => 75,
            ])
            ->add('jet_eur', ChoiceType::class, [
                'label' => 'Избор на режим на работа с валути',
                'help' => 'Избор на режим на работа с валути. Възможност за показване в евро или лева. Изпращане на исканията в евро или лева с превалутиране ако е необходимо',
                'required' => true,
                'choices'  => [
                    'Единична визуализация в лева и изпращане на исканията в лева' => 0,
                    'Двойна визуализация лева/евро и изпращане на исканията в лева' => 1,
                    'Двойна визуализация евро/лева и изпращане на исканията в евро' => 2,
                    'Единична визуализация в евро и изпращане на исканията в евро' => 3,
                ],
                'empty_data' => 0,
            ]);
    }
}

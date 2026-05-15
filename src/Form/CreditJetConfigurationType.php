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
                'help' => 'Избери email адрес на който ще се изпращат заявките от клиента. Използвайте "online.shop@pbpf.bg" за изпращане на заявките към ПБ Лични Финанси. Можете да въведете повече от един мейл адрес, като ги разделите помежду си със запетая. (Ако оставите празно ще се използва email-а по-подразбиране за системата. Препоръчва се използването на email извън Вашия домейн за да се избегне ауто спан защитата.)',
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
            ->add(
                'jet_vnoska',
                SwitchType::class,
                [
                    'label' => 'Покажи вноска',
                    'help' => 'Дали да се показва текст, в дясно от бутона указващ месечната вноска за избрания период на лизинг?',
                ],
            )
            ->add('jet_minprice', NumberType::class, [
                'label' => 'Минимална сума',
                'help' => 'Минимално възможната сума на стоките за закупуване на кредит през ПБ Лични Финанси',
                'required' => true,
                'unit' => 'лв.',
                'empty_data' => 150,
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

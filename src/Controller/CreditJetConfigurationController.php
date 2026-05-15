<?php

declare(strict_types=1);

namespace PrestaShop\Module\CreditJet\Controller;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Context;
use Db;
use DbQuery;

class CreditJetConfigurationController extends FrameworkBundleAdminController
{
    private const CONFIGURATION_TABS = [
        'creditjet-management',
        'creditjet-functional',
        'creditjet-visual',
        'creditjet-interest-filters',
    ];

    public function index(Request $request): Response
    {
        $textFormDataHandler = $this->get('prestashop.module.creditjet.creditjet_configuration_form_handler');

        $textForm = $textFormDataHandler->getForm();
        $textForm->handleRequest($request);

        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $errors = $textFormDataHandler->save($textForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', 'Успешна актуализация');

                return $this->redirectToRoute('credit_jet_configuration_form', [
                    'active_tab' => $this->resolveActiveTab($request),
                ]);
            }

            $this->flashErrors($errors);
        }

        $context = Context::getContext();
        $link_to_create_schema_creditjet = $context->link->getModuleLink('creditjet', 'createschema', []);
        $link_to_delete_schema_creditjet = $context->link->getModuleLink('creditjet', 'deleteschema', []);
        $creditjet_schemes = $this->getAllJetKopRows();

        return $this->render('@Modules/creditjet/views/templates/admin/form.html.twig', [
            'creditJetConfigurationForm' => $textForm->createView(),
            'link_to_create_schema_creditjet' => $link_to_create_schema_creditjet,
            'link_to_delete_schema_creditjet' => $link_to_delete_schema_creditjet,
            'creditjet_schemes' => $creditjet_schemes,
            'active_tab' => $this->resolveActiveTab($request),
        ]);
    }

    private function resolveActiveTab(Request $httpRequest): string
    {
        if ($httpRequest->isMethod(Request::METHOD_POST)) {
            $tab = (string) $httpRequest->request->get('creditjet_active_tab', 'creditjet-management');
        } else {
            $tab = (string) $httpRequest->query->get('active_tab', 'creditjet-management');
        }

        if (!in_array($tab, self::CONFIGURATION_TABS, true)) {
            return 'creditjet-management';
        }

        return $tab;
    }

    public function getAllJetKopRows()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('jet_kop');
        $results = Db::getInstance()->executeS($sql);

        return $results;
    }
}

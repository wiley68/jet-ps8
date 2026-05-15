<?php

declare(strict_types=1);

namespace PrestaShop\Module\CreditJet\Controller;

use PrestaShop\Module\CreditJet\Form\CreditJetConfigurationDataConfiguration;
use PrestaShop\Module\CreditJet\Util\JetButtonSettings;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Context;
use Db;
use DbQuery;

class CreditJetConfigurationController extends FrameworkBundleAdminController
{
    public function index(Request $request): Response
    {
        $textFormDataHandler = $this->get('prestashop.module.creditjet.creditjet_configuration_form_handler');

        $textForm = $textFormDataHandler->getForm();
        $textForm->handleRequest($request);

        if ($textForm->isSubmitted() && $textForm->isValid()) {
            $errors = $textFormDataHandler->save($textForm->getData());

            if (empty($errors)) {
                $this->addFlash('success', 'Успешна актуализация');

                return $this->redirectToRoute('credit_jet_configuration_form');
            }

            $this->flashErrors($errors);
        }

        $context = Context::getContext();
        $link_to_create_schema_creditjet = $context->link->getModuleLink('creditjet', 'createschema', []);
        $link_to_delete_schema_creditjet = $context->link->getModuleLink('creditjet', 'deleteschema', []);
        $creditjet_schemes = $this->getAllJetKopRows();

        /** @var CreditJetConfigurationDataConfiguration $dataConfiguration */
        $dataConfiguration = $this->get('prestashop.module.creditjet.form.creditjet_configuration_data_configuration');
        $formConfig = $textForm->getData();
        if (!is_array($formConfig)) {
            $formConfig = $dataConfiguration->getConfiguration();
        }

        $buttonPreview = JetButtonSettings::getAdminPreviewContext(
            $formConfig,
            $this->getCreditJetModuleImageBaseUrl()
        );

        return $this->render('@Modules/creditjet/views/templates/admin/form.html.twig', array_merge([
            'creditJetConfigurationForm' => $textForm->createView(),
            'link_to_create_schema_creditjet' => $link_to_create_schema_creditjet,
            'link_to_delete_schema_creditjet' => $link_to_delete_schema_creditjet,
            'creditjet_schemes' => $creditjet_schemes,
            'active_tab' => 'creditjet-management',
            'creditjet_forms_js_version' => $this->getAssetVersion('views/js/creditjetForms.js'),
            'creditjet_admin_visual_css_version' => $this->getAssetVersion('views/css/creditjet_admin_visual.css'),
            'creditjet_admin_visual_js_version' => $this->getAssetVersion('views/js/creditjet_admin_visual.js'),
        ], $buttonPreview));
    }

    private function getCreditJetModuleImageBaseUrl(): string
    {
        $baseUri = '/';
        if (\defined('__PS_BASE_URI__')) {
            $baseUri = (string) \constant('__PS_BASE_URI__');
        }

        return rtrim($baseUri, '/') . '/modules/creditjet/views/templates/img/';
    }

    private function getAssetVersion(string $relativePath): int
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $relativePath;

        if (!file_exists($path)) {
            return time();
        }

        return (int) filemtime($path);
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

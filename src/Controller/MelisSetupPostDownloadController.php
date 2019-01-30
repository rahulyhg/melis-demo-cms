<?php

/**
 * Melis Technology (http://www.melistechnology.com)
 *
 * @copyright Copyright (c) 2019 Melis Technology (http://www.melistechnology.com)
 *
 */

namespace MelisDemoCms\Controller;

use MelisCore\MelisSetupInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

/**
 * @property bool $showOnMarketplacePostSetup
 */
class MelisSetupPostDownloadController extends AbstractActionController implements MelisSetupInterface
{
    /**
     * flag for Marketplace whether to display the setup form or not
     * @var bool $showOnMarketplacePostSetup
     */
    public $showOnMarketplacePostSetup = true;

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function getFormAction()
    {
        $form = $this->getFormSiteDemo();
        $container = new Container('melis_modules_configuration_status');
        $formData = isset($container['formData']) ? (array) $container['formData'] : null;

        if ($formData) {
            $form->setData($formData);
        }

        $view = new ViewModel();
        $view->setVariable('siteDemoCmsForm', $form);

        $view->setTerminal(true);

        return $view;
    }

    /**
     * @return \Zend\Form\ElementInterface
     */
    private function getFormSiteDemo()
    {
        /** @var \MelisCore\Service\MelisCoreConfigService $config */
        $config = $this->getServiceLocator()->get('MelisCoreConfig');
        $appConfigForm = $config->getItem('melis_demo_cms_setup/forms/melis_installer_demo_cms');


        $factory = new \Zend\Form\Factory();
        $formElements = $this->getServiceLocator()->get('FormElementManager');
        $factory->setFormElementManager($formElements);
        $form = $factory->createForm($appConfigForm);

        // default data
        $scheme = 'https';
        $domain = $this->getRequest()->getUri()->getHost();

        $data = [
            'sdom_scheme' => $scheme,
            'sdom_domain' => $domain,
        ];

        $form->setData($data);

        return $form;
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function validateFormAction()
    {
        $success = false;
        $message = 'tr_install_setup_message_ko';
        $errors = [];

        $data = $this->getTool()->sanitizeRecursive($this->params()->fromRoute());

        $success = true;
        $message = 'tr_install_setup_message_ok';

        $response = [
            'success' => $success,
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors,
            'siteDemoCmsForm' => 'melis_installer_demo_cms',
            'domainForm' => 'melis_installer_domain',
        ];

        return new JsonModel($response);
    }

    /**
     * @return \MelisCore\Service\MelisCoreToolService
     */
    private function getTool()
    {
        /** @var \MelisCore\Service\MelisCoreToolService $service */
        $service = $this->getServiceLocator()->get('MelisCoreTool');

        return $service;
    }

    /**
     * @param array $errors
     *
     * @return array
     */
    private function formatErrorMessage($errors = [])
    {
        /** @var \MelisCore\Service\MelisCoreConfigService $melisMelisCoreConfig */
        $melisMelisCoreConfig = $this->getServiceLocator()->get('MelisCoreConfig');
        $appConfigForm = $melisMelisCoreConfig->getItem('melis_demo_cms_setup/forms/melis_installer_demo_cms');
        $appConfigForm = $appConfigForm['elements'];

        foreach ($errors as $keyError => $valueError) {
            foreach ($appConfigForm as $keyForm => $valueForm) {
                if ($valueForm['spec']['name'] == $keyError &&
                    !empty($valueForm['spec']['options']['label'])) {
                    $errors[$keyError]['label'] = $valueForm['spec']['options']['label'];
                }
            }
        }

        return $errors;
    }

    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function submitAction()
    {
        $success = true;
        $message = $this->getTool()->getTranslation('tr_install_setup_message_ko');
        $errors = [];

        return new JsonModel(func_get_args());
    }
}

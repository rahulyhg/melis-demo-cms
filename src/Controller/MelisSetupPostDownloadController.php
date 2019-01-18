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

        $siteDemoCmsForm = $this->getFormSiteDemo();
        $siteDemoCmsForm->setData($data);

        if ($siteDemoCmsForm->isValid()) {
            $success = true;
            $message = 'tr_install_setup_message_ok';
        } else {
            $errors = $this->formatErrorMessage($siteDemoCmsForm->getMessages());
        }

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
        $success = 0;
        $message = 'tr_install_setup_message_ko';
        $errors = [];

        $data = null;
        if (!$data) {
            $data = $this->getTool()->sanitizeRecursive($this->params()->fromRoute());
        }

        // Getting the DemoSite config
        $config = $this->getServiceLocator()->get('config');
        $siteId = $config['site']['MelisDemoCms']['datas']['site_id'];

        $docPath = $_SERVER['DOCUMENT_ROOT'];

        $setupDatas = include $docPath . '/../module/MelisSites/MelisDemoCms/install/MelisDemoCms.setup.php';
        $siteData = $setupDatas['melis_site'];

        $siteDemoCmsForm = $this->getFormSiteDemo();
        $siteDemoCmsForm->setData($data);

        $container = new \Zend\Session\Container('melis_modules_configuration_status');
        $hasErrors = false;

        if ($siteDemoCmsForm->isValid()) {

            try {
                foreach ($container->getArrayCopy() as $module) {
                    if (!$module) {
                        $hasErrors = true;
                    }
                }

                $container = new \Zend\Session\Container('melismodules');
                $installerModuleConfigurationSuccess = isset($container['module_configuration']['success']) ?
                    (bool) $container['module_configuration']['success'] : false;


                //siteDemoCms installation start
                $scheme = $siteDemoCmsForm->get('sdom_scheme')->getValue();
                $domain = $siteDemoCmsForm->get('sdom_domain')->getValue();

                //Save siteDemoCms config
                if (false === $hasErrors) {
                    /** @var \MelisDemoCms\Service\SetupDemoCmsService $setupSrv */
                    $setupSrv = $this->getServiceLocator()->get('SetupDemoCmsService');

                    // $setupSrv->setupSite($siteData);
                    $setupSrv->setup(getenv('MELIS_PLATFORM'));
                    $setupSrv->setupSiteDomain($scheme, $domain);

                    $success = 1;
                    $message = 'tr_install_setup_message_ok';
                }
            } catch (\Exception $e) {
                $errors = $e->getMessage();
            }
        } else {
            $errors = $this->formatErrorMessage($siteDemoCmsForm->getMessages());
        }

        $response = [
            'success' => $success,
            'message' => $this->getTool()->getTranslation($message),
            'errors' => $errors,
            'siteDemoCmsForm' => 'melis_installer_demo_cms',
            'domainForm' => 'melis_installer_domain',
        ];

        return new JsonModel($response);
    }
}

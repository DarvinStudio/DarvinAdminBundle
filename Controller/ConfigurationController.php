<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14.08.15
 * Time: 16:11
 */

namespace Darvin\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Configuration controller
 */
class ConfigurationController extends Controller
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request)
    {
        $configurationPool = $this->getConfigurationPool();

        $url = $this->generateUrl('darvin_admin_configuration');

        $form = $this->createForm('darvin_config_configurations', $configurationPool, array(
            'action'             => $url,
            'translation_domain' => 'admin',
        ))->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $configurationPool->saveAll();

                $this->getFlashNotifier()->success('configuration.action.edit.success');

                return $this->redirect($url);
            }

            $this->getFlashNotifier()->formError();
        }

        return $this->render('DarvinAdminBundle:Configuration:edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @return \Darvin\ConfigBundle\Configuration\ConfigurationPool
     */
    private function getConfigurationPool()
    {
        return $this->container->get('darvin_config.configuration.pool');
    }

    /**
     * @return \Darvin\AdminBundle\Flash\FlashNotifier
     */
    private function getFlashNotifier()
    {
        return $this->container->get('darvin_admin.flash.notifier');
    }
}

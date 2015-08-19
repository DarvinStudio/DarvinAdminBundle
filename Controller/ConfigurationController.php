<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

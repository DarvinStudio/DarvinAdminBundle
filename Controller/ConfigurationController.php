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

use Darvin\AdminBundle\Form\Type\Configuration\ConfigurationsType;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ConfigBundle\Entity\ParameterEntity;
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
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function editAction(Request $request)
    {
        if (!$this->isGranted(Permission::EDIT, ParameterEntity::class)) {
            throw $this->createAccessDeniedException();
        }

        $configurationPool = $this->getConfigurationPool();

        $url = $this->generateUrl('darvin_admin_configuration');

        $form = $this->createForm(ConfigurationsType::class, $configurationPool, [
            'action'             => $url,
            'translation_domain' => 'admin',
        ])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $configurationPool->saveAll();

                $this->getFlashNotifier()->success('configuration.action.edit.success');

                return $this->redirect($url);
            }

            $this->getFlashNotifier()->formError();
        }

        return $this->render('DarvinAdminBundle:Configuration:edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return \Darvin\ConfigBundle\Configuration\ConfigurationPool
     */
    private function getConfigurationPool()
    {
        return $this->get('darvin_config.configuration.pool');
    }

    /**
     * @return \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private function getFlashNotifier()
    {
        return $this->get('darvin_utils.flash.notifier');
    }
}

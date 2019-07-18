<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller;

use Darvin\AdminBundle\Form\Type\Configuration\ConfigurationsType;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface;
use Darvin\ConfigBundle\Entity\ParameterEntity;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Configuration controller
 */
class ConfigurationController extends AbstractController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function editAction(Request $request): Response
    {
        if (!$this->isGranted(Permission::EDIT, ParameterEntity::class)) {
            throw $this->createAccessDeniedException();
        }

        $url = $this->generateUrl('darvin_admin_configuration');

        $form = $this->createForm(ConfigurationsType::class, $this->getConfigurationPool(), [
            'action'             => $url,
            'translation_domain' => 'admin',
        ])->handleRequest($request);

        $render = function (AbstractController $controller) use ($form, $request) {
            return $controller->renderView(sprintf('@DarvinAdmin/configuration/%sedit.html.twig', $request->isXmlHttpRequest() ? '_' : ''), [
                'form' => $form->createView(),
            ]);
        };

        if (!$form->isSubmitted()) {
            return new Response($render($this));
        }
        if (!$form->isValid()) {
            if ($request->isXmlHttpRequest()) {
                return new AjaxResponse($render($this), false, FlashNotifierInterface::MESSAGE_FORM_ERROR);
            }

            $this->getFlashNotifier()->formError();

            return new Response($render($this));
        }

        $this->getConfigurationPool()->saveAll();

        $message = 'configuration.action.edit.success';

        if ($request->isXmlHttpRequest()) {
            return new AjaxResponse($render($this), true, $message);
        }

        $this->getFlashNotifier()->success($message);

        return new RedirectResponse($url);
    }

    /**
     * @return \Darvin\ConfigBundle\Configuration\ConfigurationPoolInterface
     */
    private function getConfigurationPool(): ConfigurationPoolInterface
    {
        return $this->get('darvin_config.configuration.pool');
    }

    /**
     * @return \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private function getFlashNotifier(): FlashNotifierInterface
    {
        return $this->get('darvin_utils.flash.notifier');
    }
}

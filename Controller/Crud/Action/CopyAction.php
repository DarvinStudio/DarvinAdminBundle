<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud\Action;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CopiedEvent;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\CopyFormWidget;
use Darvin\AdminBundle\View\Widget\WidgetPool;
use Darvin\Utils\Cloner\ClonerInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * CRUD controller copy action
 */
class CopyAction extends AbstractAction
{
    /**
     * @var \Darvin\Utils\Cloner\ClonerInterface
     */
    private $cloner;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * @var \Darvin\AdminBundle\View\Widget\WidgetPool
     */
    private $viewWidgetPool;

    /**
     * @param \Darvin\Utils\Cloner\ClonerInterface                      $cloner         Cloner
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator      Validator
     * @param \Darvin\AdminBundle\View\Widget\WidgetPool                $viewWidgetPool View widget pool
     */
    public function __construct(ClonerInterface $cloner, ValidatorInterface $validator, WidgetPool $viewWidgetPool)
    {
        $this->cloner = $cloner;
        $this->validator = $validator;
        $this->viewWidgetPool = $viewWidgetPool;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request Request
     * @param int                                       $id      Entity ID
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(Request $request, $id): Response
    {
        $this->checkPermission(Permission::CREATE_DELETE);

        $entity = $this->findEntity($id);

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), __FUNCTION__, $entity));

        $copy        = null;
        $form        = $this->adminFormFactory->createCopyForm($entity, $this->getEntityClass())->handleRequest($request);
        $message     = sprintf('%saction.copy.success', $this->getMeta()->getBaseTranslationPrefix());
        $success     = true;
        $redirectUrl = $request->headers->get(
            'referer',
            $this->adminRouter->generate($entity, $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX)
        );

        if (!$form->isValid()) {
            $success = false;

            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));
        } else {
            $copy = $this->cloner->createClone($entity);

            $violations = $this->validator->validate($copy);

            if ($violations->count() > 0) {
                $success = false;

                $parts = [];

                /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
                foreach ($violations as $violation) {
                    $parts[] = sprintf('%s: %s', $violation->getInvalidValue(), $violation->getMessage());
                }

                $message = implode(PHP_EOL, $parts);
            }
        }
        if ($success && !empty($copy)) {
            $this->em->persist($copy);
            $this->em->flush();

            $this->eventDispatcher->dispatch(CrudEvents::COPIED, new CopiedEvent($this->getMeta(), $this->userManager->getCurrentUser(), $entity, $copy));
        }
        if ($request->isXmlHttpRequest()) {
            $html = $success ? null : $this->viewWidgetPool->getWidget(CopyFormWidget::ALIAS)->getContent($entity);

            return new AjaxResponse($html, $success, $message, [], $redirectUrl);
        }

        $this->flashNotifier->done($success, $message);

        return new RedirectResponse($redirectUrl);
    }
}

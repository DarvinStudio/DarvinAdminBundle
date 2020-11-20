<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CopiedEvent;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Route\AdminRouterInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface;
use Darvin\AdminBundle\View\Widget\Widget\CopyFormWidget;
use Darvin\Utils\Cloner\ClonerInterface;
use Darvin\Utils\HttpFoundation\AjaxResponse;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @var \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface
     */
    private $viewWidgetPool;

    /**
     * @param \Darvin\Utils\Cloner\ClonerInterface                      $cloner         Cloner
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator      Validator
     * @param \Darvin\AdminBundle\View\Widget\ViewWidgetPoolInterface   $viewWidgetPool View widget pool
     */
    public function __construct(ClonerInterface $cloner, ValidatorInterface $validator, ViewWidgetPoolInterface $viewWidgetPool)
    {
        $this->cloner = $cloner;
        $this->validator = $validator;
        $this->viewWidgetPool = $viewWidgetPool;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $entity = $this->findEntity($request->attributes->get('id'));

        $this->checkPermission(Permission::CREATE_DELETE, $entity);

        $this->eventDispatcher->dispatch(
            new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), $this->getName(), $entity),
            CrudControllerEvents::STARTED
        );

        $copy        = null;
        $form        = $this->adminFormFactory->createCopyForm($entity, $this->getEntityClass())->handleRequest($request);
        $message     = sprintf('%saction.copy.success', $this->getMeta()->getBaseTranslationPrefix());
        $success     = true;
        $redirectUrl = '/';

        if ($this->adminRouter->exists($this->getEntityClass(), AdminRouterInterface::TYPE_INDEX)) {
            $redirectUrl = $this->adminRouter->generate($entity, $this->getEntityClass(), AdminRouterInterface::TYPE_INDEX);
        }

        $redirectUrl = $request->headers->get('referer', $redirectUrl);

        if (!$form->isValid()) {
            $success = false;

            $message = implode(PHP_EOL, array_map(function (FormError $error) {
                return $error->getMessage();
            }, iterator_to_array($form->getErrors(true))));
        } else {
            $copy = $this->cloner->createClone($entity);

            if (null !== $copy) {
                $violations = $this->validator->validate($copy);

                if ($violations->count() > 0) {
                    $success = false;

                    $parts = [];

                    /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
                    foreach ($violations as $violation) {
                        $parts[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
                    }

                    $message = implode(PHP_EOL, $parts);
                }
            }
        }
        if ($success && null !== $copy) {
            $this->em->persist($copy);
            $this->em->flush();

            $this->clearCache();

            $this->eventDispatcher->dispatch(
                new CopiedEvent($this->getMeta(), $this->userManager->getCurrentUser(), $form, $entity, $copy),
                CrudEvents::COPIED
            );
        }
        if ($request->isXmlHttpRequest()) {
            $html = $success ? '' : (string)$this->viewWidgetPool->getWidget(CopyFormWidget::ALIAS)->getContent($entity);

            if ('' !== $html) {
                $redirectUrl = null;
            }

            return new AjaxResponse($html, $success, $message, [], $redirectUrl);
        }

        $this->flashNotifier->done($success, $message);

        return new RedirectResponse($redirectUrl);
    }
}

<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Controller\Crud;

use Darvin\AdminBundle\Event\Crud\Controller\ControllerEvent;
use Darvin\AdminBundle\Event\Crud\Controller\CrudControllerEvents;
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\UpdatedEvent;
use Darvin\AdminBundle\Form\Renderer\PropertyFormRendererInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller update property action
 */
class UpdatePropertyAction extends AbstractAction
{
    /**
     * @var \Darvin\AdminBundle\Form\Renderer\PropertyFormRendererInterface
     */
    private $propertyFormRenderer;

    /**
     * @var \Symfony\Contracts\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Darvin\AdminBundle\Form\Renderer\PropertyFormRendererInterface $propertyFormRenderer Property form renderer
     * @param \Symfony\Contracts\Translation\TranslatorInterface              $translator           Translator
     */
    public function __construct(PropertyFormRendererInterface $propertyFormRenderer, TranslatorInterface $translator)
    {
        $this->propertyFormRenderer = $propertyFormRenderer;
        $this->translator = $translator;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function __invoke(): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Only XMLHttpRequests are allowed.');
        }

        $this->checkPermission(Permission::EDIT);

        $id       = $request->attributes->get('id');
        $property = $request->attributes->get('property');

        $entity = $this->findEntity($id);

        $entityBefore = clone $entity;

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->getMeta(), $this->userManager->getCurrentUser(), __FUNCTION__, $entity));

        $form = $this->adminFormFactory->createPropertyForm($this->getMeta(), $property, $entity)->handleRequest($request);

        $success = $form->isValid();

        $message = 'flash.success.update_property';

        if ($success) {
            $this->em->flush();

            $this->eventDispatcher->dispatch(CrudEvents::UPDATED, new UpdatedEvent($this->getMeta(), $this->userManager->getCurrentUser(), $entityBefore, $entity));

            $form = $this->adminFormFactory->createPropertyForm($this->getMeta(), $property, $entity);
        } else {
            $prefix     = $this->getMeta()->getEntityTranslationPrefix();
            $translator = $this->translator;

            $message = implode('<br>', array_map(function (FormError $error) use ($prefix, $translator) {
                $message = $error->getMessage();

                /** @var \Symfony\Component\Validator\ConstraintViolation|null $cause */
                $cause = $error->getCause();

                if (!empty($cause)) {
                    $translation = preg_replace('/^data\./', $prefix, StringsUtil::toUnderscore($cause->getPropertyPath()));

                    $translated = $translator->trans($translation, [], 'admin');

                    if ($translated !== $translation) {
                        $message = sprintf('%s: %s', $translated, $message);
                    }
                }

                return $message;
            }, iterator_to_array($form->getErrors(true))));
        }

        return new JsonResponse([
            'html'    => $this->propertyFormRenderer->render($form, $entityBefore, $this->getEntityClass(), $property),
            'message' => $message,
            'success' => $success,
        ]);
    }
}

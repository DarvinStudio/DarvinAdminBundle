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
use Darvin\AdminBundle\Event\Crud\CrudEvents;
use Darvin\AdminBundle\Event\Crud\UpdatedEvent;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer;
use Darvin\Utils\Strings\StringsUtil;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * CRUD controller update property action
 */
class UpdatePropertyAction extends AbstractAction
{
    /**
     * @var \Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer
     */
    private $entitiesToIndexViewTransformer;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @param \Darvin\AdminBundle\View\Index\EntitiesToIndexViewTransformer $entitiesToIndexViewTransformer Entities to index view transformer
     * @param \Symfony\Component\Translation\TranslatorInterface            $translator                     Translator
     */
    public function __construct(EntitiesToIndexViewTransformer $entitiesToIndexViewTransformer, TranslatorInterface $translator)
    {
        $this->entitiesToIndexViewTransformer = $entitiesToIndexViewTransformer;
        $this->translator = $translator;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request  Request
     * @param int                                       $id       Entity ID
     * @param string                                    $property Property to update
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function run(Request $request, $id, string $property): Response
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('Only XMLHttpRequests are allowed.');
        }

        $this->checkPermission(Permission::EDIT);

        $entity = $this->findEntity($id);

        $entityBefore = clone $entity;

        $this->eventDispatcher->dispatch(CrudControllerEvents::STARTED, new ControllerEvent($this->meta, $this->userManager->getCurrentUser(), __FUNCTION__, $entity));

        $form = $this->adminFormFactory->createPropertyForm($this->meta, $property, $entity)->handleRequest($request);

        $success = $form->isValid();

        $message = 'flash.success.update_property';

        if ($success) {
            $this->em->flush();

            $this->eventDispatcher->dispatch(CrudEvents::UPDATED, new UpdatedEvent($this->meta, $this->userManager->getCurrentUser(), $entityBefore, $entity));

            $form = $this->adminFormFactory->createPropertyForm($this->meta, $property, $entity);
        } else {
            $prefix     = $this->meta->getEntityTranslationPrefix();
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
            'html'    => $this->entitiesToIndexViewTransformer->renderPropertyForm($form, $entityBefore, $this->entityClass, $property),
            'message' => $message,
            'success' => $success,
        ]);
    }
}

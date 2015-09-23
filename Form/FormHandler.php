<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Form;

use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\Utils\Cloner\ClonerInterface;
use Darvin\Utils\Flash\FlashNotifierInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Form handler
 */
class FormHandler
{
    /**
     * @var \Darvin\Utils\Cloner\ClonerInterface
     */
    private $cloner;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\Utils\Flash\FlashNotifierInterface
     */
    private $flashNotifier;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * @param \Darvin\Utils\Cloner\ClonerInterface                      $cloner          Cloner
     * @param \Doctrine\ORM\EntityManager                               $em              Entity manager
     * @param \Darvin\Utils\Flash\FlashNotifierInterface                $flashNotifier   Flash notifier
     * @param \Darvin\AdminBundle\Metadata\MetadataManager              $metadataManager Metadata manager
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator       Validator
     */
    public function __construct(
        ClonerInterface $cloner,
        EntityManager $em,
        FlashNotifierInterface $flashNotifier,
        MetadataManager $metadataManager,
        ValidatorInterface $validator
    ) {
        $this->cloner = $cloner;
        $this->em = $em;
        $this->flashNotifier = $flashNotifier;
        $this->metadataManager = $metadataManager;
        $this->validator = $validator;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form           Copy form
     * @param object                                $entity         Entity
     * @param string                                $successMessage Success message
     *
     * @return bool
     */
    public function handleCopyForm(FormInterface $form, $entity, $successMessage = 'action.copy.success')
    {
        $cloner = $this->cloner;
        $flashNotifier = $this->flashNotifier;
        $validator = $this->validator;

        return $this->handleForm($form, $entity, $successMessage, function ($entity, EntityManager $em) use (
            $cloner,
            $flashNotifier,
            $validator
        ) {
            $copy = $cloner->createClone($entity);

            $violations = $validator->validate($copy);

            if (0 === $violations->count()) {
                $em->persist($copy);
                $em->flush();

                return true;
            }
            /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $flashNotifier->error($violation->getInvalidValue().': '.$violation->getMessage());
            }

            return false;
        });
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form           Delete form
     * @param object                                $entity         Entity
     * @param string                                $successMessage Success message
     *
     * @return bool
     */
    public function handleDeleteForm(FormInterface $form, $entity, $successMessage = 'action.delete.success')
    {
        return $this->handleForm($form, $entity, $successMessage, function ($entity, EntityManager $em) {
            $em->remove($entity);
            $em->flush();

            return true;
        });
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form           Entity form
     * @param string                                $successMessage Success message
     *
     * @return bool
     */
    public function handleEntityForm(FormInterface $form, $successMessage)
    {
        return $this->handleForm($form, $form->getData(), $successMessage, function ($entity, EntityManager $em) {
            $em->persist($entity);
            $em->flush();

            return true;
        });
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form                  Form
     * @param object                                $entity                Entity
     * @param string                                $successMessage        Success message
     * @param callable                              $entityProcessCallback Entity process callback
     *
     * @return bool
     */
    private function handleForm(FormInterface $form, $entity, $successMessage, callable $entityProcessCallback)
    {
        if (!$form->isSubmitted()) {
            return false;
        }
        if (!$form->isValid()) {
            $this->flashNotifier->formError();

            return false;
        }
        if (!$entityProcessCallback($entity, $this->em)) {
            return false;
        }

        $this->flashNotifier->success(
            $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix().$successMessage
        );

        return true;
    }
}

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
use Darvin\Utils\Flash\FlashNotifierInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;

/**
 * Form handler
 */
class FormHandler
{
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
     * @param \Doctrine\ORM\EntityManager                  $em              Entity manager
     * @param \Darvin\Utils\Flash\FlashNotifierInterface   $flashNotifier   Flash notifier
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager Metadata manager
     */
    public function __construct(EntityManager $em, FlashNotifierInterface $flashNotifier, MetadataManager $metadataManager)
    {
        $this->em = $em;
        $this->flashNotifier = $flashNotifier;
        $this->metadataManager = $metadataManager;
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
        });
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form            Form
     * @param object                                $entity          Entity
     * @param string                                $successMessage  Success message
     * @param callable                              $successCallback Success callback
     *
     * @return bool
     */
    private function handleForm(FormInterface $form, $entity, $successMessage, callable $successCallback)
    {
        if (!$form->isSubmitted()) {
            return false;
        }
        if (!$form->isValid()) {
            $this->flashNotifier->formError();

            return false;
        }

        $successCallback($entity, $this->em);

        $this->flashNotifier->success(
            $this->metadataManager->getByEntity($entity)->getBaseTranslationPrefix().$successMessage
        );

        return true;
    }
}

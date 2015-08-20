<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\EventListener;

use Darvin\AdminBundle\Entity\Admin;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Administrator event listener
 */
class AdminListener
{
    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\UnitOfWork
     */
    private $uow;

    /**
     * @param \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface $encoderFactory Encoder factory
     */
    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * @param \Doctrine\ORM\Event\OnFlushEventArgs $args Event arguments
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $this->uow = $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof Admin) {
                $this->updatePassword($entity);
            }
        }
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Admin) {
                $this->updatePassword($entity);
            }
        }
    }

    /**
     * @param \Darvin\AdminBundle\Entity\Admin $admin Administrator to update password
     */
    private function updatePassword(Admin $admin)
    {
        $plainPassword = $admin->getPlainPassword();

        if (empty($plainPassword)) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($admin);

        $admin->updateSalt();

        $password = $encoder->encodePassword($plainPassword, $admin->getSalt());

        $admin
            ->setPassword($password)
            ->eraseCredentials();

        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata(ClassUtils::getClass($admin)), $admin);
    }
}

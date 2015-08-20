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
use Darvin\Utils\EventListener\AbstractOnFlushListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Administrator event listener
 */
class AdminListener extends AbstractOnFlushListener
{
    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    private $encoderFactory;

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
        parent::onFlush($args);

        $updatePasswordCallback = array($this, 'updatePassword');

        $this
            ->onInsert(Admin::CLASS_NAME, $updatePasswordCallback)
            ->onUpdate(Admin::CLASS_NAME, $updatePasswordCallback);
    }

    /**
     * @param \Darvin\AdminBundle\Entity\Admin $admin Administrator to update password
     */
    protected function updatePassword(Admin $admin)
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

        $this->recomputeChangeSet($admin);
    }
}

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

use Darvin\AdminBundle\Entity\Administrator;
use Darvin\Utils\EventListener\AbstractOnFlushListener;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Administrator event subscriber
 */
class AdministratorSubscriber extends AbstractOnFlushListener implements EventSubscriber
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
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::onFlush,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        parent::onFlush($args);

        $updatePasswordCallback = array($this, 'updatePassword');

        $this
            ->onInsert($updatePasswordCallback, Administrator::CLASS_NAME)
            ->onUpdate($updatePasswordCallback, Administrator::CLASS_NAME);
    }

    /**
     * @param \Darvin\AdminBundle\Entity\Administrator $administrator Administrator to update password
     */
    protected function updatePassword(Administrator $administrator)
    {
        $plainPassword = $administrator->getPlainPassword();

        if (empty($plainPassword)) {
            return;
        }

        $encoder = $this->encoderFactory->getEncoder($administrator);

        $administrator->updateSalt();

        $password = $encoder->encodePassword($plainPassword, $administrator->getSalt());

        $administrator
            ->setPassword($password)
            ->eraseCredentials();

        $this->recomputeChangeSet($administrator);
    }
}

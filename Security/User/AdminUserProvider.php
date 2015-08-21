<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\User;

use Darvin\AdminBundle\Entity\Admin;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Administrator user provider
 */
class AdminUserProvider implements UserProviderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em Entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $emailOrUsername = $username;

        $administrator = $this->getAdminRepository()->getByEmailOrUsername($emailOrUsername);

        if (empty($administrator)) {
            throw new UsernameNotFoundException(
                sprintf('Unable to find administrator by email or username "%s".', $emailOrUsername)
            );
        }

        return $administrator;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof Admin) {
            throw new UnsupportedUserException(sprintf('User class "%s" is not supported.', ClassUtils::getClass($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return Admin::CLASS_NAME === $class;
    }

    /**
     * @return \Darvin\AdminBundle\Repository\AdminRepository
     */
    private function getAdminRepository()
    {
        return $this->em->getRepository('DarvinAdminBundle:Admin');
    }
}

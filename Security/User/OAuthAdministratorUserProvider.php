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

use Darvin\AdminBundle\Entity\Administrator;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * OAuth administrator user provider
 */
class OAuthAdministratorUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
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
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->loadUserByUsername($response->getNickname());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $administrator = $this->getAdministratorRepository()->findOneBy(array(
            'username' => $username,
        ));

        if (!empty($administrator)) {
            return $administrator;
        }

        $administrator = new Administrator(array(Administrator::ROLE_SUPERADMIN));
        $administrator
            ->setUsername($username)
            ->setRandomPlainPassword();

        $this->em->persist($administrator);
        $this->em->flush();

        return $administrator;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(ClassUtils::getClass($user))) {
            throw new UnsupportedUserException(sprintf('User class "%s" is not supported.', ClassUtils::getClass($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return Administrator::CLASS_NAME === $class;
    }

    /**
     * @return \Darvin\AdminBundle\Repository\AdministratorRepository
     */
    private function getAdministratorRepository()
    {
        return $this->em->getRepository('DarvinAdminBundle:Administrator');
    }
}

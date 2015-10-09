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

use Darvin\AdminBundle\Security\OAuth\Exception\BadResponseException;
use Darvin\AdminBundle\Security\OAuth\Response\DarvinAuthResponse;
use Darvin\UserBundle\Entity\User;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * OAuth user provider
 */
class OAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @param \Doctrine\ORM\EntityManager                                                         $em           Entity manager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface                          $session      Session
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage Authentication token storage
     */
    public function __construct(EntityManager $em, SessionInterface $session, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        // hack to error invalid_grant
        if (!$response instanceof DarvinAuthResponse) {
            throw new BadResponseException($response);
        }
        if ($response->getError()) {
            $this->session->invalidate();
            $this->tokenStorage->setToken(null);

            return null;
        }

        return $this->loadUserByUsername($response->getNickname());
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($email)
    {
        $user = $this->getUserRepository()->findOneBy(array(
            'email' => $email,
        ));

        if (!empty($user)) {
            return $user;
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setRoles(array(
                User::ROLE_ADMIN,
            ))
            ->generateRandomPlainPassword();

        $this->em->persist($user);
        $this->em->flush();

        return $user;
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
        return User::USER_CLASS === $class;
    }

    /**
     * @return \Darvin\UserBundle\Repository\UserRepository
     */
    private function getUserRepository()
    {
        return $this->em->getRepository(User::USER_CLASS);
    }
}

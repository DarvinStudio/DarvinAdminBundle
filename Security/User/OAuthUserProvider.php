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
use Darvin\UserBundle\Entity\BaseUser;
use Darvin\UserBundle\Repository\UserRepository;
use Darvin\UserBundle\User\UserFactory;
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
     * @var \Darvin\UserBundle\User\UserFactory
     */
    private $userFactory;

    /**
     * @var \Darvin\UserBundle\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @param \Doctrine\ORM\EntityManager                                                         $em             Entity manager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface                          $session        Session
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage   Authentication token storage
     * @param \Darvin\UserBundle\User\UserFactory                                                 $userFactory    User factory
     * @param \Darvin\UserBundle\Repository\UserRepository                                        $userRepository User entity repository
     */
    public function __construct(
        EntityManager $em,
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        UserFactory $userFactory,
        UserRepository $userRepository
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->userFactory = $userFactory;
        $this->userRepository = $userRepository;
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
        $user = $this->userRepository->findOneBy(array(
            'email' => $email,
        ));

        if (!empty($user)) {
            return $user;
        }

        $user = $this->userFactory->createUser()
            ->setEmail($email)
            ->setRoles(array(
                BaseUser::ROLE_ADMIN,
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
        return BaseUser::BASE_USER_CLASS === $class || is_subclass_of($class, BaseUser::BASE_USER_CLASS);
    }
}

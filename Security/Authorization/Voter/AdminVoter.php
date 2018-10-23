<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Authorization\Voter;

use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\UserBundle\Entity\BaseUser;
use Darvin\Utils\ORM\EntityResolverInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Admin authorization voter
 */
class AdminVoter extends Voter
{
    /**
     * @var \Darvin\Utils\ORM\EntityResolverInterface
     */
    protected $entityResolver;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    protected $metadataManager;

    /**
     * @var \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool
     */
    protected $securityConfigurationPool;

    /**
     * @var array|null
     */
    protected $permissions;

    /**
     * @var array|null
     */
    protected $supportedClasses;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface                            $entityResolver            Entity resolver
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                         $metadataManager           Metadata manager
     * @param \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationPool $securityConfigurationPool Security configuration pool
     */
    public function __construct(
        EntityResolverInterface $entityResolver,
        MetadataManager $metadataManager,
        SecurityConfigurationPool $securityConfigurationPool
    ) {
        $this->entityResolver = $entityResolver;
        $this->metadataManager = $metadataManager;
        $this->securityConfigurationPool = $securityConfigurationPool;

        $this->permissions = $this->supportedClasses = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof BaseUser) {
            return false;
        }
        if ($this->metadataManager->hasMetadata($subject)
            && $this->metadataManager->getConfiguration($subject)['oauth_only']
            && !$token instanceof OAuthToken
        ) {
            return false;
        }

        $class       = $this->getClass($subject);
        $permissions = $this->getPermissions();

        if (isset($permissions[$class][$user->getId()][$attribute])) {
            return $permissions[$class][$user->getId()][$attribute];
        }
        foreach (class_parents($class) as $parent) {
            if (isset($permissions[$parent][$user->getId()][$attribute])) {
                return $permissions[$parent][$user->getId()][$attribute];
            }
        }

        $defaultPermissions = Permission::getDefaultPermissions($user);

        return isset($defaultPermissions[$attribute]) ? $defaultPermissions[$attribute] : false;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, Permission::getAllPermissions())) {
            return false;
        }

        $class            = $this->getClass($subject);
        $supportedClasses = $this->getSupportedClasses();

        if (isset($supportedClasses[$class])) {
            return true;
        }
        foreach ($supportedClasses as $supportedClass) {
            if (is_subclass_of($class, $supportedClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    final protected function getPermissions()
    {
        if (null === $this->permissions) {
            $this->permissions = [];

            foreach ($this->securityConfigurationPool->getAllConfigurations() as $config) {
                foreach ($config->getPermissions() as $objectPermissions) {
                    $class = $objectPermissions->getObjectClass();

                    $this->permissions[$class] = [];

                    foreach ($objectPermissions->getUserPermissionsSet() as $userPermissions) {
                        $this->permissions[$class][$userPermissions->getUserId()] = $userPermissions->getPermissions();
                    }
                }
            }
        }

        return $this->permissions;
    }

    /**
     * @return string[]
     */
    final protected function getSupportedClasses()
    {
        if (null === $this->supportedClasses) {
            $this->supportedClasses = [];

            foreach ($this->securityConfigurationPool->getAllConfigurations() as $config) {
                foreach ($config->getPermissions() as $objectPermissions) {
                    $this->supportedClasses[$objectPermissions->getObjectClass()] = $objectPermissions->getObjectClass();
                }
            }
        }

        return $this->supportedClasses;
    }

    /**
     * @param object|string $object Object
     *
     * @return string
     */
    final protected function getClass($object)
    {
        return $this->entityResolver->resolve(is_object($object) ? get_class($object) : (string)$object);
    }
}

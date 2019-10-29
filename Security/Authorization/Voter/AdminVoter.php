<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Authorization\Voter;

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\UserBundle\Config\RoleConfigInterface;
use Darvin\UserBundle\Entity\BaseUser;
use Darvin\Utils\ORM\EntityResolverInterface;
use Doctrine\Common\Util\ClassUtils;
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
    private $entityResolver;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Darvin\UserBundle\Config\RoleConfigInterface
     */
    private $roleConfig;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param \Darvin\Utils\ORM\EntityResolverInterface                  $entityResolver  Entity resolver
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     * @param \Darvin\UserBundle\Config\RoleConfigInterface              $roleConfig      Role configuration
     * @param array                                                      $permissions     Permissions
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        EntityResolverInterface $entityResolver,
        AdminMetadataManagerInterface $metadataManager,
        RoleConfigInterface $roleConfig,
        array $permissions
    ) {
        $this->entityResolver = $entityResolver;
        $this->metadataManager = $metadataManager;
        $this->roleConfig = $roleConfig;

        foreach (array_keys($permissions) as $subject) {
            if (0 === strpos($subject, 'ROLE_') && !$roleConfig->hasRole($subject)) {
                throw new \InvalidArgumentException(sprintf('Role "%s" does not exist.', $subject));
            }
        }

        $this->permissions = $permissions;
    }

    /**
     * {@inheritDoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if ($subject instanceof BaseUser && !$this->isUserAccessible($subject, $attribute, $token)) {
            return false;
        }

        $subject = $this->resolveSubject($subject);

        if ($this->metadataManager->hasMetadata($subject)
            && $this->metadataManager->getConfiguration($subject)['oauth_only']
            && !$token instanceof OAuthToken
        ) {
            return false;
        }
        foreach ($token->getRoleNames() as $role) {
            if ($this->isGranted($role, $subject, $attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, Permission::getAllPermissions());
    }

    /**
     * @param \Darvin\UserBundle\Entity\BaseUser                                   $user       User
     * @param string                                                               $permission Permission
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token      Authentication token
     *
     * @return bool
     */
    private function isUserAccessible(BaseUser $user, string $permission, TokenInterface $token): bool
    {
        $grantableRoles = [];

        foreach ($token->getRoleNames() as $role) {
            if ($this->roleConfig->hasRole($role)) {
                foreach ($this->roleConfig->getRole($role)->getGrantableRoles() as $grantableRole) {
                    $grantableRoles[$grantableRole->getName()] = $grantableRole->getName();
                }
            }
        }
        foreach ($user->getRoles() as $role) {
            if ($this->roleConfig->hasRole($role) && !isset($grantableRoles[$role])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $role      Role
     * @param string $subject   Subject
     * @param string $attribute Attribute
     *
     * @return bool
     */
    private function isGranted(string $role, string $subject, string $attribute): bool
    {
        if (!isset($this->permissions[$role])) {
            return false;
        }

        $permissions = $this->permissions[$role];

        if (isset($permissions['subjects'][$subject][$attribute])) {
            return $permissions['subjects'][$subject][$attribute];
        }
        if (class_exists($subject)) {
            foreach (array_merge(class_implements($subject), class_parents($subject)) as $subj) {
                if (isset($permissions['subjects'][$subj][$attribute])) {
                    return $permissions['subjects'][$subj][$attribute];
                }
            }
        }

        return $permissions['default'][$attribute] ?? false;
    }

    /**
     * @param mixed $subject Subject
     *
     * @return string
     */
    private function resolveSubject($subject): string
    {
        $subject = is_object($subject) ? ClassUtils::getClass($subject) : (string)$subject;

        if (class_exists($subject) || interface_exists($subject)) {
            return $this->entityResolver->resolve($subject);
        }

        return $subject;
    }
}

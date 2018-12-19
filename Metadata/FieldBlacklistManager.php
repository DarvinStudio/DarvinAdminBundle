<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Field blacklist manager
 */
class FieldBlacklistManager implements FieldBlacklistManagerInterface
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var array
     */
    private $cache;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface                  $propertyAccessor     Property accessor
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, PropertyAccessorInterface $propertyAccessor)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->propertyAccessor = $propertyAccessor;

        $this->cache = [];
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldBlacklisted(Metadata $meta, string $field, ?string $configPath = null): bool
    {
        $entityName = $meta->getEntityName();

        if (!isset($this->cache[$entityName])) {
            $this->cache[$entityName] = [];
        }
        if (!isset($this->cache[$entityName][$configPath])) {
            $this->cache[$entityName][$configPath] = [];
        }
        if (!isset($this->cache[$entityName][$field][$configPath])) {
            $this->cache[$entityName][$field][$configPath] = $this->checkIfFieldBlacklisted($meta, $field, $configPath);
        }

        return $this->cache[$entityName][$field][$configPath];
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta       Metadata
     * @param string                                $field      Field
     * @param string|null                           $configPath Config path
     *
     * @return bool
     */
    private function checkIfFieldBlacklisted(Metadata $meta, string $field, ?string $configPath): bool
    {
        $globalConfig = $meta->getConfiguration();

        if (!empty($configPath)) {
            $config = $this->propertyAccessor->getValue($globalConfig, $configPath);

            foreach ($config['field_blacklist'] as $role => $blacklist) {
                if ($this->authorizationChecker->isGranted($role)) {
                    return in_array($field, $blacklist);
                }
            }
            foreach ($config['field_whitelist'] as $role => $whitelist) {
                if (!$this->authorizationChecker->isGranted($role)) {
                    continue;
                }
                if (in_array($field, $whitelist)) {
                    return false;
                }
                if (!isset($globalConfig['field_blacklist'][$role]) && !isset($globalConfig['field_whitelist'][$role])) {
                    return true;
                }
            }
        }
        foreach ($globalConfig['field_blacklist'] as $role => $blacklist) {
            if ($this->authorizationChecker->isGranted($role)) {
                return in_array($field, $blacklist);
            }
        }
        foreach ($globalConfig['field_whitelist'] as $role => $whitelist) {
            if ($this->authorizationChecker->isGranted($role)) {
                return !in_array($field, $whitelist);
            }
        }

        return false;
    }
}

<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Field blacklist manager
 */
class FieldBlacklistManager
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var array
     */
    private $cache;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;

        $this->cache = [];
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta  Metadata
     * @param string                                $field Field
     *
     * @return bool
     */
    public function isFieldBlacklisted(Metadata $meta, $field)
    {
        $entityName = $meta->getEntityName();

        if (!isset($this->cache[$entityName])) {
            $this->cache[$entityName] = [];
        }
        if (!isset($this->cache[$entityName][$field])) {
            $this->cache[$entityName][$field] = $this->checkIfFieldBlacklisted($meta, $field);
        }

        return $this->cache[$entityName][$field];
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $meta  Metadata
     * @param string                                $field Field
     *
     * @return bool
     */
    private function checkIfFieldBlacklisted(Metadata $meta, $field)
    {
        $config = $meta->getConfiguration();

        foreach ($config['field_blacklist'] as $role => $blacklist) {
            if ($this->authorizationChecker->isGranted($role)) {
                return in_array($field, $blacklist);
            }
        }
        foreach ($config['field_whitelist'] as $role => $whitelist) {
            if ($this->authorizationChecker->isGranted($role)) {
                return !in_array($field, $whitelist);
            }
        }

        return false;
    }
}

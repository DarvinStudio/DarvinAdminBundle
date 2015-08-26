<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Permissions;

/**
 * Administrator permissions
 */
class AdministratorPermissions
{
    const CLASS_NAME = __CLASS__;

    /**
     * @var int
     */
    private $administratorId;

    /**
     * @var array
     */
    private $permissions;

    /**
     * @param int   $administratorId Administrator ID
     * @param array $permissions     Permissions
     */
    public function __construct($administratorId, array $permissions)
    {
        $this->administratorId = $administratorId;
        $this->permissions = $permissions;
    }

    /**
     * @param int $administratorId administratorId
     *
     * @return AdministratorPermissions
     */
    public function setAdministratorId($administratorId)
    {
        $this->administratorId = $administratorId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAdministratorId()
    {
        return $this->administratorId;
    }

    /**
     * @param array $permissions permissions
     *
     * @return AdministratorPermissions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}

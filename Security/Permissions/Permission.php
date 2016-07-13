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
 * Permission
 */
final class Permission
{
    const CREATE_DELETE = 'create_delete';
    const EDIT          = 'edit';
    const VIEW          = 'view';

    /**
     * @var array
     */
    private static $permissions = [
        self::CREATE_DELETE,
        self::EDIT,
        self::VIEW,
    ];

    /**
     * @return array
     */
    public static function getAllPermissions()
    {
        return self::$permissions;
    }
}

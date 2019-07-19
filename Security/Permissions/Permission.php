<?php declare(strict_types=1);
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
    public const PREFIX = 'admin_';

    public const CREATE_DELETE = self::PREFIX.'create_delete';
    public const EDIT          = self::PREFIX.'edit';
    public const VIEW          = self::PREFIX.'view';

    /**
     * @return string[]
     */
    public static function getAllPermissions(): array
    {
        return [
            self::CREATE_DELETE,
            self::EDIT,
            self::VIEW,
        ];
    }
}

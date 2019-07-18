<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\User;

/**
 * User roles
 */
final class Roles
{
    public const ROLE_ADMIN        = 'ROLE_ADMIN';
    public const ROLE_COMMON_ADMIN = 'ROLE_COMMON_ADMIN';
    public const ROLE_SUPER_ADMIN  = 'ROLE_SUPER_ADMIN';

    /**
     * @return string[]
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_COMMON_ADMIN,
            self::ROLE_SUPER_ADMIN,
        ];
    }
}

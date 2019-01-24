<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\User;

use Darvin\UserBundle\Entity\BaseUser;
use Darvin\UserBundle\Security\User\OAuthUserProvider as BaseOAuthUserProvider;

/**
 * OAuth user provider
 */
class OAuthUserProvider extends BaseOAuthUserProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createUser($username): BaseUser
    {
        return parent::createUser($username)
            ->setRoles([
                Roles::ROLE_ADMIN,
            ]);
    }
}

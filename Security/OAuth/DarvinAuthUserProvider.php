<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\OAuth;

use Darvin\AdminBundle\Security\User\Roles;
use Darvin\UserBundle\Entity\BaseUser;
use Darvin\UserBundle\Security\OAuth\DarvinAuthResponse;
use Darvin\UserBundle\Security\OAuth\DarvinAuthUserProvider as BaseDarvinAuthUserProvider;

/**
 * Darvin Auth user provider
 */
class DarvinAuthUserProvider extends BaseDarvinAuthUserProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createUser(DarvinAuthResponse $response): BaseUser
    {
        return parent::createUser($response)
            ->setRoles([
                Roles::ROLE_COMMON_ADMIN,
            ]);
    }
}

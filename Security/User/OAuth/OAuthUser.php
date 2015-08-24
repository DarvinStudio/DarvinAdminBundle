<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\User\OAuth;

use Darvin\AdminBundle\Entity\Administrator;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser as BaseOAuthUser;

/**
 * OAuth user
 */
class OAuthUser extends BaseOAuthUser
{
    const CLASS_NAME = 'Darvin\\AdminBundle\\Security\\User\\OAuth\\OAuthUser';

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array(
            Administrator::ROLE_ADMIN,
            Administrator::ROLE_SUPERADMIN,
        );
    }
}

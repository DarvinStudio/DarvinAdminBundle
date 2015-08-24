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

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as BaseOAuthUserProvider;

/**
 * OAuth user provider
 */
class OAuthUserProvider extends BaseOAuthUserProvider
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return OAuthUser::CLASS_NAME === $class;
    }
}

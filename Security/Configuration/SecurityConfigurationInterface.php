<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Configuration;

use Darvin\ConfigBundle\Configuration\ConfigurationInterface;
use Darvin\Utils\Security\SecurableInterface;

/**
 * Security configuration
 */
interface SecurityConfigurationInterface extends ConfigurationInterface, SecurableInterface
{
    /**
     * @return \Darvin\AdminBundle\Security\Permissions\ObjectPermissions[]
     */
    public function getPermissions(): array;
}

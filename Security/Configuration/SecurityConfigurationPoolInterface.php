<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Security\Configuration;

/**
 * Security configuration pool
 */
interface SecurityConfigurationPoolInterface
{
    /**
     * @return \Darvin\AdminBundle\Security\Configuration\SecurityConfigurationInterface[]
     */
    public function getAllConfigurations(): array;
}

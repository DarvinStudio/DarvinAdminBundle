<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Configuration;

/**
 * Section configuration
 */
interface SectionConfigurationInterface
{
    /**
     * @param string $entity Entity class
     *
     * @return \Darvin\AdminBundle\Configuration\Section
     * @throws \InvalidArgumentException
     */
    public function getSection(string $entity): Section;

    /**
     * @param string $entity Entity class
     *
     * @return bool
     */
    public function hasSection(string $entity): bool;

    /**
     * @return \Darvin\AdminBundle\Configuration\Section[]
     */
    public function getSections(): array;
}
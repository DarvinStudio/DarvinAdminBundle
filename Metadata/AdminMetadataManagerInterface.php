<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

/**
 * Admin metadata manager
 */
interface AdminMetadataManagerInterface
{
    /**
     * @param object|string $entity Entity
     *
     * @return bool
     */
    public function hasMetadata($entity): bool;

    /**
     * @param object|string $entity Entity
     *
     * @return array
     */
    public function getConfiguration($entity): array;

    /**
     * @param object|string $entity Entity
     *
     * @return \Darvin\AdminBundle\Metadata\Metadata
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function getMetadata($entity): Metadata;

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     *
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function getAllMetadata(): array;
}

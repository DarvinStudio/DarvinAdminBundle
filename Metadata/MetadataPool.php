<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Metadata;

/**
 * Metadata pool
 */
class MetadataPool
{
    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata[]
     */
    private $metadata;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->metadata = [];
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     */
    public function addMetadata(Metadata $metadata): void
    {
        $this->metadata[$metadata->getEntityClass()] = $metadata;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     */
    public function getAllMetadata(): array
    {
        return $this->metadata;
    }
}

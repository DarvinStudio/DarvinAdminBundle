<?php
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
        $this->metadata = array();
    }

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     *
     * @throws \Darvin\AdminBundle\Metadata\MetadataException
     */
    public function add(Metadata $metadata)
    {
        if (isset($this->metadata[$metadata->getEntityClass()])) {
            throw new MetadataException(sprintf('Metadata for entity "%s" is already added.'));
        }

        $this->metadata[$metadata->getEntityClass()] = $metadata;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata[]
     */
    public function getAll()
    {
        return $this->metadata;
    }
}

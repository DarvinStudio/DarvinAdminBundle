<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 06.08.15
 * Time: 16:35
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

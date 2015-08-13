<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 11.08.15
 * Time: 9:01
 */

namespace Darvin\AdminBundle\Metadata;

/**
 * Associated metadata
 */
class AssociatedMetadata
{
    /**
     * @var string
     */
    private $association;

    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $metadata;

    /**
     * @param string                                $association Association name
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata    Associated metadata
     */
    public function __construct($association, Metadata $metadata)
    {
        $this->association = $association;
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getAssociation()
    {
        return $this->association;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}

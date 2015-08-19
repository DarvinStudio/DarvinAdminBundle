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

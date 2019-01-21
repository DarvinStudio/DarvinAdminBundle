<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Event\Metadata;

use Darvin\AdminBundle\Metadata\Metadata;
use Symfony\Component\EventDispatcher\Event;

/**
 * Metadata event
 */
class MetadataEvent extends Event
{
    /**
     * @var \Darvin\AdminBundle\Metadata\Metadata
     */
    private $metadata;

    /**
     * @param \Darvin\AdminBundle\Metadata\Metadata $metadata Metadata
     */
    public function __construct(Metadata $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return \Darvin\AdminBundle\Metadata\Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }
}

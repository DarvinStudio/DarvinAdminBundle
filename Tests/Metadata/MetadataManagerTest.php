<?php declare(strict_types=1);
/**
 * @copyright Copyright (c) 2026, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Tests\Metadata;

use Darvin\AdminBundle\Metadata\MetadataException;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Metadata\MetadataPool;
use Darvin\Utils\ORM\EntityResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Metadata manager test
 */
class MetadataManagerTest extends TestCase
{
    public function testGetMetadataForUnknownStringThrowsMetadataException(): void
    {
        $entityResolver = $this->createMock(EntityResolverInterface::class);
        $entityResolver->method('resolve')->willReturnArgument(0);

        $metadataPool = $this->createMock(MetadataPool::class);
        $metadataPool->method('getAllMetadata')->willReturn([]);

        $manager = new MetadataManager($entityResolver, $this->createMock(EventDispatcherInterface::class), $metadataPool);

        $this->expectException(MetadataException::class);
        $this->expectExceptionMessage('Unable to get metadata for class "Not\Existing\Entity".');

        $manager->getMetadata('Not\Existing\Entity');
    }
}

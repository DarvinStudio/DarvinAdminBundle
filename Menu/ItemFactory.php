<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu;

use Darvin\AdminBundle\Metadata\MetadataManager;

/**
 * Menu item factory
 */
class ItemFactory implements ItemFactoryInterface
{
    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @param \Darvin\AdminBundle\Metadata\MetadataManager $metadataManager Metadata manager
     */
    public function __construct(MetadataManager $metadataManager)
    {
        $this->metadataManager = $metadataManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        foreach ($this->metadataManager->getAllMetadata() as $meta) {
            $items[] = new Item($meta->getBaseTranslationPrefix().'action.index.link');
        }

        return $items;
    }
}

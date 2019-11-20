<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu;

use Darvin\Utils\NewObject\NewObjectCounterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Menu
 */
class Menu implements MenuInterface
{
    /**
     * @var \Darvin\Utils\NewObject\NewObjectCounterInterface
     */
    private $newObjectCounter;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Darvin\AdminBundle\Menu\ItemFactoryInterface[]
     */
    private $itemFactories;

    /**
     * @var \Darvin\AdminBundle\Menu\Item[]
     */
    private $items;

    /**
     * @param \Darvin\Utils\NewObject\NewObjectCounterInterface $newObjectCounter New object counter
     * @param \Symfony\Component\HttpFoundation\RequestStack    $requestStack     Request stack
     */
    public function __construct(NewObjectCounterInterface $newObjectCounter, RequestStack $requestStack)
    {
        $this->newObjectCounter = $newObjectCounter;
        $this->requestStack = $requestStack;

        $this->itemFactories = [];

        $this->items = null;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\ItemFactoryInterface $itemFactory Menu item factory
     */
    public function addItemFactory(ItemFactoryInterface $itemFactory): void
    {
        $this->itemFactories[] = $itemFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems(): array
    {
        if (null === $this->items) {
            /** @var \Darvin\AdminBundle\Menu\Item[] $items */
            $items = [];

            // Create items
            foreach ($this->itemFactories as $itemFactory) {
                foreach ($itemFactory->getItems() as $item) {
                    if (isset($items[$item->getName()])) {
                        throw new \RuntimeException(sprintf('Menu item "%s" already exists.', $item->getName()));
                    }
                    if ($this->isCurrent($item)) {
                        $item->setActive(true);
                    }

                    $items[$item->getName()] = $item;
                }
            }

            $items = $this->sortItems($items);

            // Build tree
            foreach ($items as $item) {
                if (!$item->hasParent()) {
                    continue;
                }

                $parentName = $item->getParentName();

                if (!isset($items[$parentName])) {
                    $item->setParentName(null);

                    continue;
                }

                $parent = $items[$parentName];
                $parent->addChild($item);

                if ($item->isActive()) {
                    $parent->setActive(true);
                }
            }
            // Count new objects
            foreach ($items as $item) {
                if (null === $item->getNewObjectCount()
                    && null !== $item->getAssociatedObject()
                    && $this->newObjectCounter->isCountable($item->getAssociatedObject())
                ) {
                    $item->setNewObjectCount($this->newObjectCounter->count($item->getAssociatedObject()));
                }
            }
            // Fold tree
            foreach ($items as $key => $item) {
                if ($item->hasParent()) {
                    unset($items[$key]);
                }
            }

            $items = $this->sortItems($items);

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\Item[] $items Menu items
     *
     * @return \Darvin\AdminBundle\Menu\Item[]
     */
    private function sortItems(array $items): array
    {
        if (!empty($items)) {
            $defaultPos = max(array_map(function (Item $item) {
                return $item->getPosition();
            }, $items)) + 1;

            uasort($items, function (Item $a, Item $b) use ($defaultPos) {
                $posA = null !== $a->getPosition() ? $a->getPosition() : $defaultPos;
                $posB = null !== $b->getPosition() ? $b->getPosition() : $defaultPos;

                return $posA <=> $posB;
            });
        }

        return $items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\Item $item Menu item
     *
     * @return bool
     */
    private function isCurrent(Item $item): bool
    {
        if (null === $item->getIndexUrl()) {
            return false;
        }

        $url = $item->getIndexUrl();

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return false;
        }

        $parts = parse_url($url);

        if (!isset($parts['path']) || 0 !== strpos($request->getPathInfo(), $parts['path'])) {
            return false;
        }
        if (!isset($parts['query'])) {
            return true;
        }

        $missing = array_diff(explode('&', $parts['query']), explode('&', (string)$request->getQueryString()));

        return empty($missing);
    }
}

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

            $items = $this->sort($items);

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
            // Fold tree
            foreach ($items as $key => $item) {
                if ($item->hasParent()) {
                    unset($items[$key]);
                }
            }

            $items = $this->cleanup($items);
            $items = $this->countNewObjects($items);
            $items = $this->sort($items);

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\Item[] $items Menu items
     *
     * @return \Darvin\AdminBundle\Menu\Item[]
     */
    private function cleanup(array $items): array
    {
        $keys = array_keys($items);

        $last = count($keys) - 1;

        for ($i = 0; $i <= $last; $i++) {
            $key = $keys[$i];

            $item = $items[$key];

            /** @var \Darvin\AdminBundle\Menu\Item|null $prev */
            $prev = null;

            for ($k = $i - 1; $k >= 0; $k--) {
                if (isset($items[$keys[$k]])) {
                    $prev = $items[$keys[$k]];

                    break;
                }
            }

            /** @var \Darvin\AdminBundle\Menu\Item|null $next */
            $next = $i < $last ? $items[$keys[$i + 1]] : null;

            if ($item->isSeparator()) {
                if ((null === $prev || $prev->isSeparator()) || (null === $next || $next->isSeparator())) {
                    unset($items[$key]);
                }

                continue;
            }
            if ($item->isEmpty()) {
                unset($items[$key]);

                continue;
            }
            if ($item->hasChildren()) {
                $children = $this->cleanup($item->getChildren());

                $item->setChildren($children);

                if (empty($children)) {
                    unset($items[$key]);
                }
            }
        }

        return $items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\Item[] $items Menu items
     *
     * @return \Darvin\AdminBundle\Menu\Item[]
     */
    private function countNewObjects(array $items): array
    {
        foreach ($items as $item) {
            if (null === $item->getNewObjectCount()
                && null !== $item->getAssociatedObject()
                && $this->newObjectCounter->isCountable($item->getAssociatedObject())
            ) {
                $item->setNewObjectCount($this->newObjectCounter->count($item->getAssociatedObject()));
            }
            if ($item->hasChildren()) {
                $item->setChildren($this->countNewObjects($item->getChildren()));
            }
        }

        return $items;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\Item[] $items Menu items
     *
     * @return \Darvin\AdminBundle\Menu\Item[]
     */
    private function sort(array $items): array
    {
        if (!empty($items)) {
            $default = max(array_map(function (Item $item): ?int {
                return $item->getPosition();
            }, $items)) + 1;

            uasort($items, function (Item $a, Item $b) use ($default): int {
                $posA = null !== $a->getPosition() ? $a->getPosition() : $default;
                $posB = null !== $b->getPosition() ? $b->getPosition() : $default;

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

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return false;
        }

        $parts = parse_url($item->getIndexUrl());

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

<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\AdminBundle\Menu;

use Darvin\AdminBundle\Security\Permissions\Permission;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Menu
 */
class Menu
{
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var array[]
     */
    private $groupsConfig;

    /**
     * @var \Darvin\AdminBundle\Menu\ItemFactoryInterface[]
     */
    private $itemFactories;

    /**
     * @var \Darvin\AdminBundle\Menu\Item[]
     */
    private $items;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     * @param array                                                                        $groupsConfig         Groups configuration
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $groupsConfig)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->groupsConfig = array_combine(array_map(function (array $config) {
            return $config['name'];
        }, $groupsConfig), $groupsConfig);

        $this->itemFactories = [];

        $this->items = null;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\ItemFactoryInterface $itemFactory Menu item factory
     */
    public function addItemFactory(ItemFactoryInterface $itemFactory): void
    {
        $this->itemFactories[get_class($itemFactory)] = $itemFactory;
    }

    /**
     * @return \Darvin\AdminBundle\Menu\Item[]
     *
     * @throws \RuntimeException
     */
    public function getItems(): array
    {
        if (null === $this->items) {
            /** @var \Darvin\AdminBundle\Menu\Item[] $items */
            $items = $skipped = [];

            foreach ($this->itemFactories as $itemFactory) {
                foreach ($itemFactory->getItems() as $item) {
                    if (isset($items[$item->getName()])) {
                        throw new \RuntimeException(sprintf('Menu item "%s" already exists.', $item->getName()));
                    }
                    if (null === $item->getIndexUrl() && null === $item->getNewUrl()) {
                        $skipped[$item->getName()] = true;

                        continue;
                    }

                    $associatedObject = $item->getAssociatedObject();

                    if (!empty($associatedObject)
                        && (!$this->authorizationChecker->isGranted(Permission::VIEW, $associatedObject) && !$this->authorizationChecker->isGranted(Permission::CREATE_DELETE, $associatedObject))
                    ) {
                        $skipped[$item->getName()] = true;

                        continue;
                    }

                    $items[$item->getName()] = $item;
                }
            }

            $items = $this->sortItems($items);

            foreach ($items as $item) {
                if (!$item->hasParent()) {
                    continue;
                }

                $parentName = $item->getParentName();

                if (isset($skipped[$parentName])) {
                    continue;
                }
                if (!isset($items[$parentName])) {
                    $items[$parentName] = $this->createItemGroup($parentName, $item->getPosition());
                }

                $parent = $items[$parentName];
                $parent->addChild($item);
            }
            foreach ($items as $key => $item) {
                if ($item->hasParent() && !isset($skipped[$item->getParentName()])) {
                    unset($items[$key]);
                }
            }

            $items = $this->sortItems($items);

            $this->items = $items;
        }

        return $this->items;
    }

    /**
     * @param string   $name            Name
     * @param int|null $defaultPosition Default position
     *
     * @return \Darvin\AdminBundle\Menu\ItemGroup
     */
    private function createItemGroup(string $name, ?int $defaultPosition): ItemGroup
    {
        $group = new ItemGroup($name);
        $group->setPosition($defaultPosition);

        if (!isset($this->groupsConfig[$name])) {
            return $group;
        }

        $config = $this->groupsConfig[$name];

        if (null !== $config['position']) {
            $group->setPosition($config['position']);
        }

        $group
            ->setMainColor($config['colors']['main'])
            ->setSidebarColor($config['colors']['sidebar'])
            ->setMainIcon($config['icons']['main'])
            ->setSidebarIcon($config['icons']['sidebar'])
            ->setAssociatedObject($config['associated_object']);

        return $group;
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

                return $posA === $posB ? 0 : ($posA > $posB ? 1 : -1);
            });
        }

        return $items;
    }
}

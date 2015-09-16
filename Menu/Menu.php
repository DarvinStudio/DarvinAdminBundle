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
     * @var \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    private $items;

    /**
     * @var \Darvin\AdminBundle\Menu\MenuItemGroup[]
     */
    private $groups;

    /**
     * @var bool
     */
    private $itemsFiltered;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker Authorization checker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->items = array();
        $this->groups = array();
        $this->itemsFiltered = false;
    }

    /**
     * @param string                                     $groupName Menu item group name
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item      Menu item
     */
    public function addItem($groupName, MenuItemInterface $item)
    {
        if (empty($groupName)) {
            $this->items[] = $item;

            return;
        }
        if (!isset($this->groups[$groupName])) {
            $group = new MenuItemGroup($groupName);
            $this->items[] = $group;
            $this->groups[$groupName] = $group;
        }

        $group = $this->groups[$groupName];
        $group->addItem($item);
    }

    /**
     * @return \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    public function getItems()
    {
        $this->filterItems();

        return $this->items;
    }

    private function filterItems()
    {
        if ($this->itemsFiltered) {
            return;
        }

        $this->items = $this->removeNeedlessItems($this->items);

        $this->itemsFiltered = true;
    }

    /**
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface[] $items Menu items
     *
     * @return \Darvin\AdminBundle\Menu\MenuItemInterface[]
     */
    private function removeNeedlessItems(array $items)
    {
        foreach ($items as $key => $item) {
            $children = $this->removeNeedlessItems($item->getChildren());
            $item->setChildren($children);

            if (!empty($children)) {
                continue;
            }

            $objectClass = $item->getAssociatedObjectClass();

            if (empty($objectClass)) {
                $indexUrl = $item->getIndexUrl();
                $newUrl = $item->getNewUrl();

                if (empty($indexUrl) && empty($newUrl)) {
                    unset($items[$key]);
                }

                continue;
            }
            if (!$this->authorizationChecker->isGranted(Permission::VIEW, $objectClass)
                && !$this->authorizationChecker->isGranted(Permission::CREATE_DELETE, $objectClass)
            ) {
                unset($items[$key]);
            }
        }

        return $items;
    }
}

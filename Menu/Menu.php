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
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
     * @var \Symfony\Component\OptionsResolver\OptionsResolver
     */
    private $optionsResolver;

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
        $this->optionsResolver = new OptionsResolver();
        $this->items = $this->groups = [];
        $this->itemsFiltered = false;

        $this->configureItemAttributes($this->optionsResolver);
    }

    /**
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item      Menu item
     * @param string                                     $groupName Menu item group name
     */
    public function addItem(MenuItemInterface $item, $groupName = null)
    {
        $this->resolveItemAttributes($item);

        if (empty($groupName)) {
            $this->items[] = $item;

            return;
        }
        if (!isset($this->groups[$groupName])) {
            $group = new MenuItemGroup($groupName);
            $this->resolveItemAttributes($group);
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

    /**
     * @param \Darvin\AdminBundle\Menu\MenuItemInterface $item Menu item
     *
     * @throws \Darvin\AdminBundle\Menu\MenuException
     */
    private function resolveItemAttributes(MenuItemInterface $item)
    {
        try {
            $item->setMenuItemAttributes($this->optionsResolver->resolve($item->getMenuItemAttributes()));
        } catch (ExceptionInterface $ex) {
            throw new MenuException(sprintf('Menu item attributes are invalid: "%s".', $ex->getMessage()));
        }
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    private function configureItemAttributes(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                [
                'associated_object_class' => '',
                'color'                   => '',
                'description'             => '',
                'homepage_menu_icon'      => '',
                'left_menu_icon'          => '',
                'new_title'               => '',
                ]
            )
            ->setRequired(
                [
                'index_title',
                'name',
                ]
            )
            ->setAllowedTypes('associated_object_class', 'string')
            ->setAllowedTypes('description', 'string')
            ->setAllowedTypes('new_title', 'string')
            ->setAllowedTypes('index_title', 'string')
            ->setAllowedTypes('name', 'string');
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
            $children = $this->removeNeedlessItems($item->getChildMenuItems());
            $item->setChildMenuItems($children);

            if (!empty($children)) {
                continue;
            }

            $attributes = $item->getMenuItemAttributes();
            $objectClass = $attributes['associated_object_class'];

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

Меню
====

## Добавление пунктов

**1. Создаем класс, реализующий "Darvin\AdminBundle\Menu\ItemFactoryInterface".**

**2. Объявляем класс сервисом и помечаем его тегом "darvin_admin.menu_item_factory".**

Пример объявления сервиса:

```yaml
parameters:
    darvin_admin.menu.item_factory.class: Darvin\AdminBundle\Menu\ItemFactory

services:
    darvin_admin.menu.item_factory:
        class:  '%darvin_admin.menu.item_factory.class%'
        public: false
        tags:
            - { name: darvin_admin.menu_item_factory }
```

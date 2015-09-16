Меню
====

## Добавление пункта

**1. Создаем класс, реализующий "Darvin\AdminBundle\Menu\MenuItemInterface".**

**2. Объявляем класс сервисом и помечаем его тегом "darvin_admin.menu_item".**

Тег имеет два аргумента:

- **group** *(опционально)* - название группы элементов меню;
- **position** *(опционально)* - позиция элемента меню.

Особенности:

- позицией группы элементов меню является позиция первого в ней элемента.

Пример объявления сервиса:

```yaml
parameters:
    app.admin.menu.item.class: AppBundle\Admin\Menu\MenuItem

services:
    app.admin.menu.item:
        class: %app.admin.menu.item.class%
        tags:
            - { name: darvin_admin.menu_item }
```

*Контроллеры разделов администрирования реализуют требуемый интерфейс, но тегируются только в том случае, если параметр
 "menu.skip" имеет значение false (по умолчанию).*

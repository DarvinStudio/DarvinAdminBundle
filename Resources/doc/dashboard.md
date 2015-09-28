Dashboard
=========

## Описание

Dashboard - набор виджетов на главной странице панели администрирования.

## Добавление виджета

**1. Создаем класс, реализующий "Darvin\AdminBundle\Dashboard\DashboardWidgetInterface" или наследующийся от
 "Darvin\AdminBundle\Dashboard\AbstractDashboardWidget".**

**2. Объявляем класс сервисом и помечаем его тегом "darvin_admin.dashboard_widget".**

Тег имеет один аргумент:

- **position** *(опционально)* - позиция виджета.

Если класс виджета наследуется от
 "Darvin\AdminBundle\Dashboard\AbstractDashboardWidget", сервис может быть потомком сервиса
 "darvin_admin.dashboard.widget.abstract".

Пример определения сервиса:

```yaml
parameters:
    app.admin.dashboard_widget.latest_posts.class: AppBundle\Admin\Dashboard\LatestPostsWidget

services:
    app.admin.dashboard_widget.latest_posts:
        class:  %app.admin.dashboard_widget.latest_posts.class%
        parent: darvin_admin.dashboard.widget.abstract
        calls:
            - [ setEntityManager, [ "@doctrine.orm.entity_manager" ] ]
        tags:
            - { name: darvin_admin.dashboard_widget }
```

Виджеты моделей представления
=============================

## Описание

Виджеты можно использовать для генерации контента в моделях представления и шаблонах. Виджет получает на вход сущность
 и возвращает строку.

## Создание

**1. Создаем класс, реализующий "Darvin\AdminBundle\View\Widget\WidgetInterface" или наследующийся от
 "Darvin\AdminBundle\View\Widget\Widget\AbstractWidget".**

Пример реализации:

```php
use Darvin\AdminBundle\Security\Permissions\Permission;
use Darvin\AdminBundle\View\Widget\Widget\AbstractWidget;

class EditLinkWidget extends AbstractWidget
{
    protected function createContent($entity, array $options, $property)
    {
        return $this->render($options, [
            'entity'             => $entity,
            'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
        ]);
    }

    protected function getRequiredPermissions()
    {
        return [
            Permission::EDIT,
        ];
    }
}
```

**Базовый класс содержит ряд методов, с которыми полезно ознакомиться.**

**2. Объявляем класс сервисом и помечаем его тегом "darvin_admin.view_widget":**

```yaml
parameters:
    darvin_admin.view.widget.edit_link.class: Darvin\AdminBundle\View\Widget\Widget\EditLinkWidget

services:
    darvin_admin.view.widget.edit_link:
        class:  "%darvin_admin.view.widget.edit_link.class%"
        parent: darvin_admin.view.widget.abstract
        tags:
            - { name: darvin_admin.view_widget }
```

Если используется базовый класс, сервис может наследоваться от базового сервиса "darvin_admin.view.widget.abstract"
 как в приведенном примере.

## Список виджетов

Список алиасов зарегистрированных виджетов можно получить с помощью команды

```shell
$ php app/console darvin:admin:widget:list
```

## Использование

**1. Для использования виджета необходимо в настройках какого-либо поля в секции "view" конфигурационного файла раздела
 администрирования указать опцию "widget":**

```yaml
Darvin\AdminBundle\Entity\Administrator:
    view:
        index:
            fields:
                email:
                    widget:
                        alias: email_link
                        options:
                            email_property: email
                roles:
                    widget:
                        alias: list
                        options:
                            keys_property:   roles
                            values_callback: [ Darvin\AdminBundle\Entity\Administrator, getAvailableExtraRoles ]
```

Псевдоним виджета передается в параметре "alias", массив опций - в "options".

**2. Также все виджеты доступны в виде функций Twig.**

Название функции - это псевдоним виджета с префиксом "admin_widget_".

Виджеты моделей представления
=============================

## Описание

Виджеты можно использовать для генерации контента в моделях представления и шаблонах. Виджет получает на вход сущность
 и возвращает строку.

## Создание

**1. Создаем класс, реализующий "Darvin\AdminBundle\View\WidgetGenerator\WidgetGeneratorInterface" или наследующийся от
 "Darvin\AdminBundle\View\WidgetGenerator\AbstractWidgetGenerator".**

Пример реализации:

```php
use Darvin\AdminBundle\Security\Permissions\Permission;

class EditLinkGenerator extends AbstractWidgetGenerator
{
    public function generate($entity, array $options = array())
    {
        if (!$this->isGranted(Permission::EDIT, $entity)) {
            return '';
        }

        return $this->render($options, array(
            'entity'             => $entity,
            'translation_prefix' => $this->metadataManager->getMetadata($entity)->getBaseTranslationPrefix(),
        ));
    }

    public function getAlias()
    {
        return 'edit_link';
    }
}
```

Метод "getAlias()" должен возвращать уникальный псевдоним виджета.

**Необходимо обязательно осуществлять проверку наличия у пользователя соответствующих прав. Для этого в базовом классе
 присутствует метод "isGranted()".**

**Базовый класс содержит и другие методы, с которыми полезно ознакомиться.**

## Список виджетов

Список алиасов зарегистрированных виджетов можно получить с помощью команды

```shell
$ php app/console darvin:admin:widget:list
```

## Использование

**1. Для использования виджета необходимо в настройках какого-либо поля в секции "view" конфигурационного файла раздела
 администрирования указать опцию "widget_generator":**

```yaml
Darvin\AdminBundle\Entity\Administrator:
    view:
        index:
            fields:
                email:
                    widget_generator:
                        alias: email_link
                        options:
                            email_property: email
                roles:
                    widget_generator:
                        alias: list
                        options:
                            keys_property:   roles
                            values_callback: [ Darvin\AdminBundle\Entity\Administrator, getAvailableExtraRoles ]
```

Псевдоним виджета передается в параметре "alias", массив опций - в "options".

**2. Также все виджеты доступны в виде функций Twig.**

Название функции - это псевдоним виджета с префиксом "admin_widget_".

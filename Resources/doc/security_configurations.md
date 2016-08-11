Конфигурации безопасности
=========================

## Описание

Конфигурации безопасности позволяют ограничивать пользователям доступ к объектам.

## Добавление конфигурации

Конфигурация создается автоматически при [регистрации](admin_section_adding.md) раздела администрирования в настройках бандла.

## Использование

Проверка доступности объекта для текущего пользователя осуществляется стандартным способом - с помощью метода
 "isGranted()" сервиса "security.authorization_checker". Первым аргументом необходимо передать одну из констант класса
 "Darvin\AdminBundle\Security\Permissions\Permission" или их массив, в качестве второго аргумента - объект или класс:

```php
$creatingGranted = $this->container->get('security.authorization_checker')->isGranted(
    Permission::CREATE_DELETE,
    'Darvin\\AdminBundle\\Entity\\Administrator'
);
$administrator = $this->container->get('doctrine.orm.entity_manager')->find('DarvinAdminBundle:Administrator', 5);
$editingGranted = $this->container->get('security.authorization_checker')->isGranted(Permission::EDIT, $administrator);
$editingAndViewingGranted = $this->container->get('security.authorization_checker')->isGranted(
    [Permission::EDIT, Permission::VIEW],
    $administrator
);
```

В последнем примере метод вернет значение true только в том случае, если пользователь обладает и правом редактирования, и
 правом просмотра.

Конфигурации безопасности
=========================

## Описание

Конфигурации безопасности позволяют ограничивать пользователям доступ к объектам.

## Добавление конфигурации

**1. Создаем класс, наследующийся от "Darvin\AdminBundle\Security\Configuration\AbstractSecurityConfiguration".**

Метод "getSecurableObjectClasses()" должен возвращать массив, ключами которого являются названия объектов, а значениями
 - их классы. С помощью метода "getAllowedRoles()" можно разрешить редактирование конфигурации только пользователям с
 определенными ролями. По умолчанию редактировать конфигурацию смогут только суперадминистраторы (см. реализацию метода
 "Darvin\AdminBundle\Security\Configuration\AbstractSecurityConfiguration::getAllowedRoles()").

Пример реализации класса конфигурации безопасности:

```php
use Darvin\AdminBundle\Security\Configuration\AbstractSecurityConfiguration;

class SecurityConfiguration extends AbstractSecurityConfiguration
{
    public function getName()
    {
        return 'darvin_admin_security';
    }

    protected function getSecurableObjectClasses()
    {
        return array(
            'abstract_image' => 'Darvin\\ImageBundle\\Entity\\Image\\AbstractImage,
            'log_entry'      => 'Darvin\\AdminBundle\\Entity\\LogEntry',
        );
    }
}
```

Данная конфигурация позволяет ограничивать доступ к администраторам и записям лога.

**2. Объявляем класс сервисом и помечаем его тегами "darvin_admin.security_configuration" и "darvin_config.configuration".**

Последний тег имеет один аргумент:

- **position** *(опционально)* - позиция конфигурации в форме редактирования в панели администрирования.

Пример определения сервиса:

```yaml
parameters:
    darvin_admin.security.configuration.class: Darvin\AdminBundle\Security\Configuration\SecurityConfiguration

services:
    darvin_admin.security.configuration:
        class: %darvin_admin.security.configuration.class%
        tags:
            - { name: darvin_admin.security_configuration }
            - { name: darvin_config.configuration }
```

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
    array(Permission::EDIT, Permission::VIEW),
    $administrator
);
```

В последнем примере метод вернет значение true только в том случае, если пользователь обладает и правом редактирования, и
 правом просмотра.

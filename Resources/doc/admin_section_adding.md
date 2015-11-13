Добавление раздела администрирования
====================================

**1. Создаем файл конфигурации раздела в формате YAML.**

Конфигурация описана в [справочнике](reference/admin_section_configuration.md). По договоренности файл конфигурации
 должен располагаться в каталоге "Resources/config/admin" вашего бандла и иметь имя, отражающее название сущности, для
 администрирования которой создается раздел. Примеры конфигурации можно найти в каталоге "Resources/config/admin" этого
 бандла.
  
Конфигурационный файл может наследоваться от другого. Для этого необходимо добавить в него ключ "extends", значением
 которого должен быть путь до конфига-родителя. Путь может быть относительным бандла, в таком случае он должен начинаться
 с символа "@". Пример:
 
```yaml
extends: @DarvinUserBundle/Resources/config/admin/user.yml
```

**2. Регистрируем сервис-метаданные раздела администрирования.**

Сервис - производный метода "createMetadata()" сервиса-фабрики "darvin_admin.metadata.factory". Класс -
 "Darvin\AdminBundle\Metadata\Metadata". Аргументами являются:

- класс сущности, для которой создается раздел администрирования;
- путь до конфигурационного файла раздела, создание которого описано в предыдущем пункте.

Путь до файла может быть
 относительным бандла, в таком случае он должен начинаться с "@@", например
 "@@DarvinAdminBundle/Resources/config/admin/administrator.yml". Чтобы метаданные попали в пул "darvin_admin.metadata.pool",
 и раздел администрирования заработал, необходимо отметить сервис тегом "darvin_admin.metadata". Так как метаданные
 доступны через менеджер метаданных "darvin_admin.metadata.manager", имеет смысл сделать сервис приватным.

Таким образом определение сервиса может выглядеть так:

```yaml
parameters:
    darvin_admin.administrator.metadata.class:  Darvin\AdminBundle\Metadata\Metadata
    darvin_admin.administrator.metadata.entity: Darvin\AdminBundle\Entity\Administrator
    darvin_admin.administrator.metadata.config: @@DarvinAdminBundle/Resources/config/admin/administrator.yml

services:
    darvin_admin.administrator.metadata:
        class:   %darvin_admin.administrator.metadata.class%
        factory: [ "@darvin_admin.metadata.factory", createMetadata ]
        public:  false
        arguments:
            - %darvin_admin.administrator.metadata.entity%
            - %darvin_admin.administrator.metadata.config%
        tags:
            - { name: darvin_admin.metadata }
```

Менеджер метаданных по умолчанию кэширует метаданные и возвращает версии из кэша. Поэтому все внесенные в конфигурацию
 раздела администрирования изменения активируются только после чисти кэша. Чтобы отключить это поведение, необходимо
  включить отладку панели администрирования, установив параметр [конфигурации](reference/configuration.md)
 "darvin_admin.debug" бандла равным true.

Пример получения метаданных из менеджера метаданных:

```php
$metadata = $this->getContainer()->get('darvin_admin.metadata.manager')->getMetadata('Darvin\\AdminBundle\\Entity\\Administrator');
```

*В менеджере метаданных присутствует также ряд других методов, с которыми полезно ознакомиться.*

После создания сервиса-метаданных раздела администрирования, становится возможным использование следующих сервисов,
 оперирующих метаданными:

- **darvin_admin.metadata.identifier_accessor** - сервис получения идентификатора сущности на основе метаданных ее
 раздела администрирования;
- **darvin_admin.metadata.sort_criteria_detector** - сервис определения критерия сортировки для сущности на основе
 метаданных ее раздела администрирования.

**3. Создаем [конфигурацию безопасности](security_configurations.md) для сущности, иначе она будет недоступна для всех пользователей.**
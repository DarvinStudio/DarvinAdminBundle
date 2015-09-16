Установка
=========

**1. Добавим бандл в секцию "require" файла composer.json:**

```json
{
    "require": {
        "darvinstudio/darvin-admin-bundle": "*"
    }
}
```

**2. Установим бандл с помощью Composer, выполнив в консоли команду**

    $ php composer.phar update darvinstudio/darvin-admin-bundle

**3. Добавим бандл в ядро приложения (обычно это файл "app/AppKernel.php"):**

```php
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Darvin\AdminBundle\DarvinAdminBundle(),
        );
    }
}
```

**4. Настроим бандл:**

- импортируем настройки бандлов "hwi/oauth-bundle" и "oneup/uploader-bundle" в главный конфигурационный файл приложения
 (обычно это "app/config/config.yml"):

```yaml
imports:
    - { resource: "@DarvinAdminBundle/Resources/config/app/hwi_oauth.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/oneup_uploader.yml" }
```

- импортируем настройки безопасности в файл конфигурации безопасности приложения (обычно "app/config/security.yml"),
 предварительно закомментировав его содержимое:

```yaml
imports:
    - { resource: "@DarvinAdminBundle/Resources/config/app/security.yml" }
```

- добавляем используемые в конфигурациях параметры в файлы параметров (обычно "app/config/parameters.yml.dist"
 и "app/config/parameters.yml");

*Если вышеупомянутые настройки необходимо изменить, вместо импортирования их нужно скопировать.*



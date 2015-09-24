Установка
=========

**1. Добавляем бандл в секцию "require" файла composer.json:**

```json
{
    "require": {
        "darvinstudio/darvin-admin-bundle": "*"
    }
}
```

**2. Устанавливаем бандл с помощью Composer, выполнив в консоли команду**

    $ php composer.phar update darvinstudio/darvin-admin-bundle

**3. Добавляем бандл и его зависимости в ядро приложения (обычно это файл "app/AppKernel.php"):**

```php
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // Third party bundles
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Ivory\CKEditorBundle\IvoryCKEditorBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            // Darvin bundles
            new Darvin\ConfigBundle\DarvinConfigBundle(),
            new Darvin\ContentBundle\DarvinContentBundle(),
            new Darvin\ImageBundle\DarvinImageBundle(),
            new Darvin\UtilsBundle\DarvinUtilsBundle(),
            // Admin bundle
            new Darvin\AdminBundle\DarvinAdminBundle(),
        );
    }
}
```

**4. Настраиваем бандл и его зависимости:**

- импортируем настройки бандлов "a2lix/translation-form-bundle", "hwi/oauth-bundle", "oneup/uploader-bundle" и
 "willdurand/js-translation-bundle" в главный конфигурационный файл приложения (обычно это "app/config/config.yml"):

```yaml
imports:
    - { resource: "@DarvinAdminBundle/Resources/config/app/a2lix_translation_form.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/bazinga_js_translation.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/hwi_oauth.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/oneup_uploader.yml" }
```

- в этом же конфигурационном файле включаем необходимые расширения Doctrine:

```yaml
stof_doctrine_extensions:
    orm:
        default:
            loggable: true
            sortable: true
            tree:     true
```

- импортируем настройки безопасности в файл конфигурации безопасности приложения (обычно "app/config/security.yml"),
 предварительно закомментировав его содержимое:

```yaml
imports:
    - { resource: "@DarvinAdminBundle/Resources/config/app/security.yml" }
```

- добавляем используемые в импортированных файлах параметры в файлы параметров (обычно "app/config/parameters.yml.dist"
 и "app/config/parameters.yml");

- если проект многоязычный, добавляем следующие сеции в настройки роутинга (обычно это файл "app/config/routing.yml"),
 указав нужные локали (в данном примере их три: "de", "en" и "ru")

```yaml
darvin_admin:
    resource:     "@DarvinAdminBundle/Resources/config/routing.yml"
    prefix:       /admin/{_locale}
    requirements: { _locale: |de|en|ru }

darvin_admin_loader:
    resource:     .
    type:         darvin_admin
    prefix:       /admin/{_locale}
    requirements: { _locale: |de|en|ru }
```

, в противном случае добавляем

```yaml
darvin_admin:
    resource: "@DarvinAdminBundle/Resources/config/routing.yml"
    prefix:   /admin

darvin_admin_loader:
    resource: .
    type:     darvin_admin
    prefix:   /admin
```

- настраиваем непосредственно бандл в соответствии с [описанием](reference/configuration.md) его конфигурации.

*Если импортируемые настройки необходимо изменить, вместо импортирования их нужно скопировать.*

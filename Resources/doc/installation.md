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

```shell
$ php composer.phar update darvinstudio/darvin-admin-bundle
```

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
            new Lexik\Bundle\TranslationBundle\LexikTranslationBundle(),
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

- импортируем настройки сторонних бандлов в главный конфигурационный файл приложения (обычно это "app/config/config.yml"):

```yaml
imports:
    - { resource: "@DarvinAdminBundle/Resources/config/app/a2lix_translation_form.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/bazinga_js_translation.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/hwi_oauth.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/lexik_translation.yml" }
    - { resource: "@DarvinImageBundle/Resources/config/app/liip_imagine.yml" }
    - { resource: "@DarvinAdminBundle/Resources/config/app/oneup_uploader.yml" }
    - { resource: "@DarvinImageBundle/Resources/config/app/vich_uploader.yml" }
```

либо копируем настройки из указанных выше файлов в случае необходимости их изменения;

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

либо копируем настройки в случае необходимости их изменения;

- настраиваем локали в главном конфиге приложения ("app/config/config.yml"):

```yaml
parameters:
    locale: ru
    locales:
        - ru
        - en
        - de
    locale_pattern:    |de|en|ru
    admin_path_prefix: %locale_pattern%
```

- добавляем используемые в импортированных файлах параметры в файлы параметров (обычно "app/config/parameters.yml.dist"
 и "app/config/parameters.yml");

- добавляем следующие сеции в настройки роутинга (обычно это файл "app/config/routing.yml"):

```yaml
# Third party bundles
bazinga_js_translation:
    resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"

lexik_translation:
    resource:     "@LexikTranslationBundle/Resources/config/routing.yml"
    prefix:       /admin/{_locale}/translation
    requirements: { _locale: %locale_pattern% }

liip_imagine:
    resource: "@LiipImagineBundle/Resources/config/routing.xml"

oneup_uploader:
    resource: .
    type:     uploader

# Darvin bundles
darvin_admin:
    resource:     "@DarvinAdminBundle/Resources/config/routing.yml"
    prefix:       /admin/{_locale}
    requirements: { _locale: %locale_pattern% }

darvin_admin_loader:
    resource:     .
    type:         darvin_admin
    prefix:       /admin/{_locale}
    requirements: { _locale: %locale_pattern% }

hwi_oauth:
    resource: "@HWIOAuthBundle/Resources/config/routing/redirect.xml"
```

если проект не многоязычный, удаляем из роутинга все упоминания параметра "_locale";

- настраиваем непосредственно бандл в соответствии с [описанием](reference/configuration.md) его конфигурации;

- обновляем схему базы данных, выполнив команду

```shell
$ php app/console doctrine:schema:update --force
```

- создаем администратора, загрузив фикстуру с помощью

```shell
$ php app/console doctrine:fixtures:load --append
```

тогда будет создан администратор "admin" с паролем "admin", либо воспользовавшись командой

```shell
$ php app/console darvin:admin:administrator:create <логин> <пароль>
```

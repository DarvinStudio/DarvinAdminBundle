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
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),
            // Darvin bundles
            new Darvin\ConfigBundle\DarvinConfigBundle(),
            new Darvin\ContentBundle\DarvinContentBundle(),
            new Darvin\ImageBundle\DarvinImageBundle(),
            new Darvin\UserBundle\DarvinUserBundle(),
            new Darvin\UtilsBundle\DarvinUtilsBundle(),
            new Darvin\WebmailLinkerBundle\DarvinWebmailLinkerBundle(),
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
    - { resource: "@DarvinAdminBundle/Resources/config/app/knp_paginator.yml" }
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

настраиваем Assetic:

```yaml
assetic:
    debug:          %kernel.debug%
    use_controller: false
    java:           /usr/bin/java
    bundles:
        - DarvinAdminBundle
    filters:
        closure:
            jar: %kernel.root_dir%/Resources/java/compiler.jar
        cssembed:
            jar: %kernel.root_dir%/Resources/java/cssembed-0.4.5.jar
        cssrewrite: ~
        yui_css:
            jar: %kernel.root_dir%/Resources/java/yuicompressor-2.4.8.jar
```

и остальные бандлы:

```yaml
darvin_admin:
    locales: %locales%
    project:
        title: %project_title%
        url:   %project_url%

darvin_image:
    imagine_filter: %image_imagine_filter%
    upload_path:    %image_upload_path%
    
darvin_utils:
    mailer:
        from: %mailer_from%
```

- настраиваем локали в главном конфиге приложения ("app/config/config.yml"):

```yaml
parameters:
    locale: ru
    locales:
        - ru
        - en
        - de
    locale_pattern:    "|de|en|ru"
    admin_path_suffix: (%locale_pattern%)/
```

- добавляем настройки безопасности в "app/config/security.yml":

```yaml
security:
    encoders:
        Darvin\UserBundle\Entity\BaseUser:
            algorithm:      pbkdf2
            hash_algorithm: sha512

    providers:
        hwi:
            id: darvin_admin.security.user_provider.oauth
        user:
            entity:
                class:    Darvin\UserBundle\Entity\BaseUser
                property: email

    firewalls:
        admin_area:
            pattern:  ^/admin/
            provider: user
            form_login:
                check_path:          darvin_admin_security_login_check
                login_path:          darvin_admin_security_login
                default_target_path: darvin_admin_homepage
                use_referer:         true
                csrf_token_id:       %secret%
                csrf_provider:       security.csrf.token_manager
                remember_me:         true
            remember_me:
                name:     REMEMBERMEADMIN
                lifetime: 31536000 # 1 year
                secret:   %secret%
            logout:
                csrf_token_id: %secret%
                path:          darvin_admin_security_logout
                target:        darvin_admin_security_login
            anonymous: ~
            oauth:
                resource_owners:
                    darvin_auth: darvin_admin_security_login_check_darvin_auth
                login_path:   darvin_admin_security_login
                failure_path: darvin_admin_security_login
                oauth_user_provider:
                    service: darvin_admin.security.user_provider.oauth
                check_path: darvin_admin_security_login_check_oauth

    role_hierarchy:
        ROLE_GUESTADMIN: [ ROLE_ADMIN ]
        ROLE_SUPERADMIN: [ ROLE_ADMIN ]

    access_control:
        - { path: ^/admin/%admin_path_suffix%login, roles: [ IS_AUTHENTICATED_ANONYMOUSLY ] }
        - { path: ^/admin/,                         roles: [ ROLE_ADMIN ] }
```

- добавляем используемые в импортированных файлах параметры в файлы параметров (обычно "app/config/parameters.yml.dist"
 и "app/config/parameters.yml"):
 
```yaml
darvin_auth_client_id:         secret
darvin_auth_client_secret:     secret
darvin_auth_access_token_url:  http://example.com/oauth/v2/token
darvin_auth_authorization_url: http://example.com/oauth/v2/auth
darvin_auth_infos_url:         http://example.com/api/user

image_imagine_filter: darvin_thumb
image_upload_path:    files/images

mailer_from: noreply@example.com

project_title: Example
project_url:   example.com
```

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

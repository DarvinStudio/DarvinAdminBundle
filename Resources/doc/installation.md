Установка
=========

**1. Добавляем опцию "component-dir" в раздел "config" файла composer.json:**

```json
{
    "config": {
        "component-dir": "web/assets/components"
    }
}
```

**2. Устанавливаем бандл с помощью Composer, выполнив в консоли команду**

```shell
$ php composer.phar require darvinstudio/darvin-admin-bundle:^5
```

**3. Добавляем бандл и его зависимости в ядро приложения (обычно это файл "app/AppKernel.php"):**

```php
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // Third party bundles
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new FM\ElfinderBundle\FMElfinderBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new FOS\CKEditorBundle\FOSCKEditorBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            // new Lexik\Bundle\TranslationBundle\LexikTranslationBundle(), (раскомментировать при использовании "lexik/translation-bundle")
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Oneup\UploaderBundle\OneupUploaderBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
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
        ];
    }
}
```

**4. Настраиваем бандл и его зависимости:**

- настраиваем бандлы:

```yaml
darvin_admin:
    locales:            "%locales%"
    upload_max_size_mb: "%upload_max_size_mb%"
    project:
        title: "%project_title%"

darvin_image:
    upload_path: "%image_upload_path%"
    
darvin_utils:
    mailer:
        from: "%mailer_from%"
```

- настраиваем локали в главном конфиге приложения ("app/config/config.yml"):

```yaml
parameters:
    locale: ru
    locales:
        - ru
        - en
        - de
    locale_pattern: "|de|en|ru"
```

- добавляем настройки безопасности в "app/config/security.yml":

```yaml
security:
    encoders:
        Darvin\UserBundle\Entity\BaseUser:
            algorithm:      pbkdf2
            hash_algorithm: sha512

    providers:
        user:
            entity:
                class:    Darvin\UserBundle\Entity\BaseUser
                property: email

    firewalls:
        admin_area:
            pattern:  ^/admin/
            provider: user
            form_login:
                check_path:           darvin_admin_security_login_check
                login_path:           darvin_admin_security_login
                default_target_path:  darvin_admin_homepage
                use_referer:          true
                csrf_token_id:        "%secret%"
                csrf_token_generator: security.csrf.token_manager
                remember_me:          true
            remember_me:
                name:     REMEMBERMEADMIN
                lifetime: 43200 # 12 hours
                secret:   "%secret%"
            logout:
                csrf_token_id: "%secret%"
                path:          darvin_admin_security_logout
                target:        darvin_admin_security_login
                # handlers:
                    # - darvin_ecommerce.cart_item.migrate_listener (раскомментировать при использовании "darvinstudio/darvin-ecommerce-bundle")
            anonymous: ~
            oauth:
                resource_owners:
                    darvin_auth_admin: darvin_admin_security_darvin_auth_login_check
                login_path:   darvin_admin_security_login
                failure_path: darvin_admin_security_login
                oauth_user_provider:
                    service: darvin_admin.security.user_provider.oauth
                default_target_path: darvin_admin_homepage
                use_referer:         true
                check_path:          darvin_admin_security_darvin_auth_login_check
            switch_user: true

    role_hierarchy:
        ROLE_GUESTADMIN: [ ROLE_ADMIN ]
        ROLE_SUPERADMIN: [ ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH ]

    access_control:
        - { path: "^/admin/(%locale_pattern%)/login", roles: [ IS_AUTHENTICATED_ANONYMOUSLY ] }
        - { path: ^/admin/,                           roles: [ ROLE_ADMIN ] }
```

- добавляем используемые в импортированных файлах параметры в файлы параметров (обычно "app/config/parameters.yml.dist"
 и "app/config/parameters.yml"):
 
```yaml
darvin_auth_client_id:     secret
darvin_auth_client_secret: secret

image_upload_path: files/images

mailer_from: noreply@example.com

project_title: Example

upload_path:        files/uploads
upload_max_size_mb: 2
```

*после добавления параметров в файл "app/config/parameters.yml.dist" рекомендуется выполнить команду "composer install"
 для интерактивного обновления "app/config/parameters.yml*

- добавляем следующие сеции в настройки роутинга (обычно это файл "app/config/routing.yml"):

```yaml
# Third party bundles
bazinga_js_translation:
    resource: "@BazingaJsTranslationBundle/Resources/config/routing/routing.yml"

liip_imagine:
    resource: "@LiipImagineBundle/Resources/config/routing.xml"

oneup_uploader:
    resource: .
    type:     uploader

# Darvin bundles
darvin_admin:
    resource:     "@DarvinAdminBundle/Resources/config/routing.yml"
    prefix:       /admin/{_locale}
    requirements: { _locale: "%locale_pattern%" }
```

*если проект не многоязычный, удаляем из роутинга и конфигурации безопасности все упоминания параметра "_locale"*

- настраиваем непосредственно бандл в соответствии с [описанием](reference/configuration.md) его конфигурации;

- включаем компонент "Translator", раскомментировав соответствующие строки в "app/config/config.yml":

```yaml
framework:
    # translator: { fallbacks: ["%locale%"] }
```

- чтобы задействовать файлы переводов чистим кэш с помощью команды

```shell
$ php bin/console cache:clear
```

- обновляем схему базы данных, выполнив команду

```shell
$ php bin/console doctrine:schema:update --force
```

- устанавливаем CKEditor

```shell
$ php bin/console ckeditor:install
$ php bin/console assets:install --symlink web
```

- создаем пользователя, выполнив

```shell
$ php bin/console darvin:user:create admin@example.com admin
```

*в диалоге выбора роли пользователя выбираем "ROLE_SUPERADMIN"*

- проверяем успешность установки, перейдя на URL "/admin/" проекта.

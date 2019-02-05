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
$ /usr/bin/env php composer.phar require darvinstudio/darvin-admin-bundle:^5
```

**3. Добавляем бандл и его зависимости в ядро приложения:**

```php
// config/bundles.php

return [
    // Third party bundles
    A2lix\TranslationFormBundle\A2lixTranslationFormBundle::class => ['all' => true],
    Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
    FM\ElfinderBundle\FMElfinderBundle::class => ['all' => true],
    HWI\Bundle\OAuthBundle\HWIOAuthBundle::class => ['all' => true],
    FOS\CKEditorBundle\FOSCKEditorBundle::class => ['all' => true],
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true],
    Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle::class => ['all' => true],
    // Lexik\Bundle\TranslationBundle\LexikTranslationBundle::class => ['all' => true], (раскомментировать при использовании "lexik/translation-bundle")
    Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true],
    Oneup\UploaderBundle\OneupUploaderBundle::class => ['all' => true],
    Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle::class => ['all' => true],
    Vich\UploaderBundle\VichUploaderBundle::class => ['all' => true],
    // Darvin bundles
    Darvin\ConfigBundle\DarvinConfigBundle::class => ['all' => true],
    Darvin\ContentBundle\DarvinContentBundle::class => ['all' => true],
    Darvin\ImageBundle\DarvinImageBundle::class => ['all' => true],
    Darvin\UserBundle\DarvinUserBundle::class => ['all' => true],
    Darvin\UtilsBundle\DarvinUtilsBundle::class => ['all' => true],
    Darvin\WebmailLinkerBundle\DarvinWebmailLinkerBundle::class => ['all' => true],
    // Admin bundle
    Darvin\AdminBundle\DarvinAdminBundle::class => ['all' => true],
];
```

**4. Настраиваем бандл и его зависимости:**

- настраиваем бандлы:

```yaml
# config/packages/darvin_admin.yaml

darvin_admin:
    locales:            '%locales%'
    project_title:      '%env(PROJECT_TITLE)%'
    upload_max_size_mb: 100
    
# config/packages/darvin_image.yaml

darvin_image:
    upload_path: '%env(IMAGE_UPLOAD_PATH)%'
    
# config/packages/darvin_utils.yaml
    
darvin_utils:
    mailer:
        from:
            email: '%env(MAILER_FROM)%'
            name:  '%env(PROJECT_TITLE)%'
```

- настраиваем локали в главном конфиге приложения ("app/config/services.yaml"):

```yaml
parameters:
    locale: ru
    locales:
        - ru
        - en
        - de
    locale_pattern: "de|en"
```

- добавляем настройки безопасности в "app/config/security.yaml":

```yaml
security:
    encoders:
        Darvin\UserBundle\Entity\BaseUser:
            algorithm:      pbkdf2
            hash_algorithm: sha512

    providers:
        user:
            id: darvin_user.security.user_provider

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        admin_area:
            pattern:  ^/admin/
            # Or if project is multilingual
            #pattern:  ^/((%locale_pattern%)/|)admin/
            provider: user
            form_login:
                check_path:           darvin_admin_security_login_check
                login_path:           darvin_admin_security_login
                default_target_path:  darvin_admin_homepage
                use_referer:          true
                csrf_token_id:        '%kernel.secret%'
                csrf_token_generator: security.csrf.token_manager
                remember_me:          true
                success_handler:      darvin_user.authentication.success_handler
            remember_me:
                name:     REMEMBERMEADMIN
                lifetime: 43200 # 12 hours
                secret:   '%kernel.secret%'
            logout:
                csrf_token_id: '%kernel.secret%'
                path:          darvin_admin_security_logout
                target:        darvin_admin_security_login
                handlers:
                    #- darvin_ecommerce.cart_item.migrate_listener (раскомментировать при использовании "darvinstudio/darvin-ecommerce-bundle")
            anonymous: ~
            oauth:
                resource_owners:
                    darvin_auth_admin: darvin_admin_security_darvin_auth_login_check
                login_path:   darvin_admin_security_login
                failure_path: darvin_admin_security_login
                oauth_user_provider:
                    service: darvin_admin.security.oauth.darvin_auth_user_provider
                default_target_path: darvin_admin_homepage
                use_referer:         true
                check_path:          darvin_admin_security_darvin_auth_login_check
            switch_user: true

    role_hierarchy:
        ROLE_GUESTADMIN: [ ROLE_ADMIN ]
        ROLE_SUPERADMIN: [ ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH ]

    access_control:
        - { path: ^/admin/login, roles: [ IS_AUTHENTICATED_ANONYMOUSLY ] }
        - { path: ^/admin/,      roles: [ ROLE_ADMIN ] }
        # Or if project is multilingual
        #- { path: ^/((%locale_pattern%)/|)admin/login, roles: [ IS_AUTHENTICATED_ANONYMOUSLY ] }
        #- { path: ^/((%locale_pattern%)/|)admin/,      roles: [ ROLE_ADMIN ] }
```

- добавляем используемые в импортированных файлах параметры в файлы параметров:
 
```env
# .env

DARVIN_AUTH_CLIENT_ID=secret
DARVIN_AUTH_CLIENT_SECRET=secret

IMAGE_UPLOAD_PATH=files/images

MAILER_FROM=noreply@skeleton4.localhost

PROJECT_TITLE=DarvinCMS

UPLOAD_MAX_SIZE_MB=100
UPLOAD_PATH=files/uploads
```

- импортируем роутинг:

```yaml
# config/routes/darvin_admin.yaml

darvin_admin:
    resource: '@DarvinAdminBundle/Resources/config/routing.yaml'
    prefix:   /admin
```

*если проект не многоязычный, удаляем из роутинга и конфигурации безопасности все упоминания параметра "_locale"*

- настраиваем непосредственно бандл в соответствии с [описанием](reference/configuration.md) его конфигурации;

- создаем пользователя, выполнив

```shell
$ php bin/console darvin:user:create admin@example.com admin
```

*в диалоге выбора роли пользователя выбираем "ROLE_SUPERADMIN"*

- проверяем успешность установки, перейдя на URL "/admin/" проекта.

Конфигурация
============

```yaml
darvin_admin:
    ckeditor # Конфигурация CKEditor
        plugin_filename: plugin.js                                     # Название файла плагина
        plugins_path:    /bundles/darvinadmin/scripts/ckeditor/plugins # Путь до каталога с плагинами

    debug:                    false                    # Включен ли режим отладки
    upload_max_size_mb:       2                        # Максимальный размер загружаемого файла
    web_dir:                  %kernel.root_dir%/../web # Путь до web-каталога
    yandex_translate_api_key: ~                        # API-ключ сервиса "Яндекс.Переводчик"

    project # Конфигурация проекта
        title: ~ # Название проекта
        url:   ~ # URL проекта
```

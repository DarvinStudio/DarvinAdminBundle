Конфигурация
============

```yaml
darvin_admin:
    ckeditor: # Конфигурация CKEditor
        plugin_filename: plugin.js                                     # Название файла плагина
        plugins_path:    /bundles/darvinadmin/scripts/ckeditor/plugins # Путь до каталога с плагинами
    cache_clear_command_classes: # Классы команд очистки кэша для команды "darvin:admin:caches:clear"
        - Liip\ImagineBundle\Command\RemoveCacheCommand
        - Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand
    debug:                    false                         # Включен ли режим отладки
    locales:                                                # Локали (обязательно)
    search_query_min_length:  3                             # Минимальная длина поискового запроса
    translations_model_dir:   Resources/config/translations # Путь до каталога с моделями переводов
    upload_max_size_mb:       2                             # Максимальный размер загружаемого файла
    visual_assets_path:       bundles/darvinadmin           # Путь до каталога с "визуальными" ресурсами (стили, изображения и т. д.)
    yandex_translate_api_key: ~                             # API-ключ сервиса "Яндекс.Переводчик"
    project: # Конфигурация проекта
        title: ~ # Название проекта
        url:   ~ # URL проекта
    sections: # [Разделы администрирования](../admin_section_adding.md)

        # Прототип
        alias:  ~ # Псевдоним, если не задан, генерируется автоматически
        entity:   # Класс сущности, для администрирования которой создается раздел (обязательно)
        config: ~ # Путь до конфигурационного файла раздела, если не задан, то создается только конфигурация безопасности
```

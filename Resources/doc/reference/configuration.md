Конфигурация
============

```yaml
darvin_admin:
    debug:                    false                         # Включен ли режим отладки
    entity_override:          {}                            # Таблица [замен классов сущностей](../how_to_override_entity.md), пример: {AppBundle\Entity\Foo: AppBundle\Entity\Bar}
    locales:                                                # Локали (обязательно)
    search_query_min_length:  3                             # Минимальная длина поискового запроса
    translations_model_dir:   Resources/config/translations # Путь до каталога с моделями переводов
    upload_max_size_mb:       2                             # Максимальный размер загружаемого файла
    visual_assets_path:       bundles/darvinadmin           # Путь до каталога с "визуальными" ресурсами (стили, изображения и т. д.)
    yandex_translate_api_key: ~                             # API-ключ сервиса "Яндекс.Переводчик"
    cache_clear_command_classes: # Классы команд очистки кэша для команды "darvin:admin:caches:clear"
        - Liip\ImagineBundle\Command\RemoveCacheCommand
        - Symfony\Bundle\FrameworkBundle\Command\CacheClearCommand
    ckeditor: # Конфигурация CKEditor
        plugin_filename: plugin.js                                     # Название файла плагина
        plugins_path:    /bundles/darvinadmin/scripts/ckeditor/plugins # Путь до каталога с плагинами
    project: # Конфигурация проекта
        title: ~ # Название проекта
        url:   ~ # URL проекта
    sections: # [Разделы администрирования](../admin_section_adding.md)

        # Прототип
        alias:  ~ # Псевдоним, если не задан, генерируется автоматически
        entity:   # Класс сущности, для администрирования которой создается раздел (обязательно)
        config: ~ # Путь до конфигурационного файла раздела, если не задан, то создается только конфигурация безопасности
    menu: # Конфигурация меню
        groups: # Конфигурация групп элементов меню

            # Прототип
            name:       # Название группы (обязательно)
            position: ~ # Позиция группы
            colors: # Цвета
                main:    ~ Цвет группы в меню на главной странице
                sidebar: ~ Цвет группы в меню на боковой панели
            icons: # Иконки
                main:    bundles/darvinadmin/images/main_menu_stub.png    # Иконка группы в меню на главной странице
                sidebar: bundles/darvinadmin/images/sidebar_menu_stub.png # Иконка группы в меню боковой на боковой панели
```

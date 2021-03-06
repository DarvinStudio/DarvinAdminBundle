Конфигурация
============

```yaml
darvin_admin:
    frontend_path:           bundles/darvinadmin           # Путь до каталога с "визуальными" ресурсами (стили, изображения и т. д.)
    locales:                                                # Локали (обязательно)
    logo:                    ~                             # Путь до кастомного логотипа (будет передан в Twig-функцию "asset()")
    project_title:           ~                             # Название проекта
    search_query_min_length: 3                             # Минимальная длина поискового запроса
    translations_model_dir:  Resources/config/translations # Путь до каталога с моделями переводов
    upload_max_size_mb:      100                           # Максимальный размер загружаемого файла
    ckeditor: # Конфигурация CKEditor
        plugin_filename: plugin.js                                     # Название файла плагина
        plugins_path:    /bundles/darvinadmin/scripts/ckeditor/plugins # Путь до каталога с плагинами
    dashboard:
        blacklist: [] # Черный список идентификаторов сервисов виджетов на главной странице панели администрирования
    sections: # [Разделы администрирования](../admin_section_adding.md), ключ - класс сущности, для администрирования которой создается раздел

        # Прототип
        alias:  ~ # Псевдоним, если не задан, генерируется автоматически
        config: ~ # Путь до конфигурационного файла раздела, если не задан, то создается только конфигурация безопасности
    form: # Конфигурация форм
        default_field_options: # Опции полей по умолчанию
            Symfony\Component\Form\Extension\Core\Type\CheckboxType:
                required: false
            Symfony\Component\Form\Extension\Core\Type\DateType:
                widget: single_text
                format: dd.MM.yyyy
            Symfony\Component\Form\Extension\Core\Type\DateTimeType:
                widget: single_text
                format: 'dd.MM.yyyy HH:mm'
            Symfony\Component\Form\Extension\Core\Type\TimeType:
                widget: single_text
    menu: # Конфигурация меню
        groups: # Конфигурация групп элементов меню, ключ - название группы

            # Прототип
            associated_object: ~ # Класс связанного объекта
            position:          ~ # Позиция группы
```

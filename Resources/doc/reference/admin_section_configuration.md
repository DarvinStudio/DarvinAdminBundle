Конфигурация раздела администрирования
======================================

```yaml
breadcrumbs_route:   edit  # Роут для ссылки на сущность в хлебных крошках
children:            []    # Классы сущностей-потомков
index_view_new_form: false # Показывать форму добавления на индексной странице
index_view_row_attr: []    # Свойства сущности, значения которых будут помещены в data-атрибуты строки индексной таблицы
joins:               {}    # Left join'ы: ключ - алиас, значение - поле
oauth_only:          false # Раздел доступен только при аутентификации через OAuth
order_by:            {}    # Сортировка по умолчанию: ключ - свойство, значение - направление ("asc" / "desc")
route_blacklist:     []    # Отключенные роуты
single_instance:     false # Сущность в единственном экземпляре (например, главная страница)
pagination: # Конфигурация пейджинга
    enabled: true # Использовать ли пейджинг
    items:   50   # Количество элементов на индексной странице, минимум - 1
    min:     10   # Минимальное количество элементов
    max:     100  # Максимальное количество элементов
    step:    10   # Шаг изменения количества элементов
sorter: # Конфигурация сервиса сортировки сущностей
    id:     ~ # Идентификатор сервиса сортировки сущностей
    method: ~ # Метод сервиса сортировки сущностей
searchable_fields: [] # Поля для поиска
sortable_fields: # Сортируемые поля

    # Прототип
    some_field_name: some.property.path # Property path поля (если не указано - название поля)
menu: # Конфигурация меню
    group:    ~     # Название группы элементов меню
    position: ~     # Позиция в меню (позицией группы является позиция первого в ней элемента)
    skip:     false # Не показывать в меню
form: # Конфигурация форм
    index: # Конфигурация экспресс-форм на индексной странице
        fields: # Поля сущности, для которых нужно выводить экспресс-формы

            # Прототип
            some_field_name: # Название поля
                type:      ~  # Тип поля, для стандартных полей можно использовать короткое название вместо класса
                condition: ~  # Условие вывода поля (с использованием "Expression language", пример: "is_granted('ROLE_ADMIN')")
                options:   {} # Опции поля
    new: # Конфигурация формы создания, если не задана, будет скопирована из "edit"
        type: ~ # Тип формы
        fields: # Поля, не входящие в группы

            # Прототип
            some_field_name: # Название поля
                type:      ~  # Тип поля, для стандартных полей можно использовать короткое название вместо класса
                condition: ~  # Условие вывода поля (с использованием "Expression language", пример: "is_granted('ROLE_ADMIN')")
                options:   {} # Опции поля
    edit:   # Конфигурация формы редактирования, см. секцию "new" выше, если не задана, будет скопирована из "new"
    filter: # Конфигурация формы фильтра
        type:          ~ # Тип формы
        heading_field: ~ # Поле - источник заголовка H1 списка сущностей
        fields: # Поля, не входящие в группы

            # Прототип
            some_field_name: # Название поля
                type:           ~     # Тип поля, для стандартных полей можно использовать короткое название вместо класса
                condition:      ~     # Условие вывода поля (с использованием "Expression language", пример: "is_granted('ROLE_ADMIN')")
                options:        {}    # Опции поля
                compare_strict: false # Использовать строгое сравнение ("=", а не "LIKE '%foo%'")
                hidden:         false # Скрытое
view: # Конфигурация уровня представления
    index: # Конфигурация индексной страницы
        action_widgets: # Виджеты действий: ключи - алиасы, значения - опции
            show_link:   {}
            edit_link:   {}
            copy_form:   {}
            delete_form: {}
        extra_action_widgets: {} # Дополнительные виджеты действий: ключи - алиасы, значения - опции
        template:              ~ # Шаблон
        fields:                  # Поля

            # Прототип
            some_field_name: # Поле

                # Прототип
                type:       ~  # Тип поля (см. tables.md)
                size:       ~  # Размер поля
                exact_size: ~  # Точный размер поля, например "32px"
                condition:  ~  # Условие вывода содержимого поля (с использованием "Expression language", пример: "is_granted('ROLE_ADMIN')")
                attr:       {} # HTML атрибуты ячейки таблицы
                callback:     # Callback
                    class:      # Требуется, класс
                    method:     # Требуется, статический метод
                    options: {} # Опции
                # Или
                widget: # Виджет
                # Или
                service: # Сервис
                    id:         # Требуется, идентификатор сервиса
                    method:     # Требуется, метод сервиса
                    options: {} # Опции
    new: # Конфигурация индексной страницы
        action_widgets: {}

        # Остальное как в секции "index" выше
    edit: # Конфигурация страницы редактирования
        action_widgets:
            show_link:   {}
            delete_form: {}

        # Остальное как в секции "index" выше
    show: # Конфигурация страницы просмотра, если не задана, будет скопирована из "index"
        action_widgets:
            edit_link:   {}
            delete_form: {}

        # Остальное как в секции "index" выше
```

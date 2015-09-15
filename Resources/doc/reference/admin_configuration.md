Конфигурация панели администрирования
=====================================

```yaml
darvin_admin:
    menu:               # Конфигурация меню
        group:    ~     # Название группы элементов меню
        position: ~     # Позиция в меню (позицией группы элементов меню является позиция первого в ней элемента)
        skip:     false # Не показывать в меню
    breadcrumbs_entity_route: edit  # Роут для ссылки на сущность в хлебных крошках
    child_entities:           []    # Массив классов сущностей-потомков
    disabled_routes:          []    # Отключенные роуты
    entity_name:              ~     # Название сущности (если не указано, будет сгенерировано автоматически)
    index_view_new_form:      false # Показывать форму добавления на индексной странице
    order_by:                 {}    # Ключ - свойство сущности, значение - направление сортировки ("asc" или "desc")
    pagination_items:         10    # Количество элементов на индексной странице
    form: # Конфигурация форм
        index: # Конфигурация экспресс-форм на индексной странице
            fields: {} Поля сущности, для которых нужно выводить экспресс-формы

                # Прототип
                some_field_name: Название поля
                    type:    ~  Тип поля
                    options: {} Опции поля
        new: # Конфигурация формы создания
            type:         ~  Тип формы
            field_groups: {} Группы полей

                # Prototype
                some_group_name: Название группы полей

                    # Prototype
                    some_field_name: Название поля
                        type:    ~  Тип поля
                        options: {} Опции поля
            fields:       {}
        edit:
            type:         ~
            field_groups: {}
            fields:       {}
    view:
        index:
            action_widgets:
                - show_link
                - edit_link
                - copy_form
                - delete_form
            template: ~
            fields:   {}

                # Prototype
                some_field_name: ~

                    # Prototype
                    callback:
                        class:   # Required
                        method:  # Required
                        options: {}
                    # Or
                    widget_generator:
                        alias:   # Required
                        options: {}
                    # Or
                    service:
                        id:      # Required
                        method:  # Required
                        options: {}
        new:
            action_widgets: []
            template:       ~
            fields:         {}
        edit:
            action_widgets:
                - show_link
                - delete_form
            template: ~
            fields:   {}
        show:
            action_widgets:
                - edit_link
                - delete_form
            template: ~
            fields:   {}
```

Конфигурация панели администрирования
=====================================

```yaml
darvin_admin:
    menu:
        group:    ~
        position: ~
        skip:     false
    breadcrumbs_entity_route: edit
    child_entities:           []
    disabled_routes:          []
    entity_name:              ~
    index_view_new_form:      false
    order_by:                 {}    # Key: property, value: direction, "asc" or "desc"
    pagination_items:         10    # Min: 1
    form:
        index:
            type:         ~
            field_groups: {}

                # Prototype
                some_group_name:

                    # Prototype
                    some_field_name:
                        type:    ~
                        options: {}
            fields: {}

                # Prototype
                some_field_name:
                    type:    ~
                    options: {}
        new:
            type:         ~
            field_groups: {}
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
            action_widgets: {}
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

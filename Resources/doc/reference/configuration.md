Конфигурация
============

```yaml
darvin_admin:
    ckeditor
        plugin_filename: plugin.js                                     # (optional)
        plugins_path:    /bundles/darvinadmin/scripts/ckeditor/plugins # (optional)

    debug:                    false                    # (optional)
    upload_max_size_mb:       2                        # (optional)
    web_dir:                  %kernel.root_dir%/../web # (optional)
    yandex_translate_api_key: ~                        # (optional)

    project
        title: ~ # (required)
        url:   ~ # (required)
```

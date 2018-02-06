5.16.6: Append base URL to project URL in the base template.

5.16.7: Deprecate "project.url" bundle configuration parameter which is no more needed.

5.16.8: Init date-time-pickers after adding new element to form collection.

5.16.9: Enable show error page event listener only if firewall pattern not equals "^/".

5.16.10: Show error page event listener: log HTTP exceptions with "error" level instead of "critical".

5.16.11: Do not check if admin routing is already loaded (for compatibility with "jms/i18n-routing-bundle").

5.16.12: Fix case API URL in the translations generate command.

5.16.13: Resolve parameters in admin section configuration file's pathname.

5.17.0: Enable RTL mode in CKEditor according to translation entity's locale.

5.17.2: Apply "urlencode()" to word for case API in the translations generate command.

5.17.3: Remove CKEditor extra plugins from plugin blacklist.

5.17.4: Abstract form type: do not set field label if "label_format" option is provided.

5.17.5: More strict item content emptiness checking in the CRUD show widget template.

5.18.0: Add "readable_enum" view widget which passes property value to the Twig filter with the same name from
 "fresh/doctrine-enum-bundle". Usage example:
 
```yaml
view:
    show:
        fields:
            userType:
                widget:
                    alias: readable_enum
                    options:
                        enum_type: UserType
```

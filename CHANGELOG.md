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

5.18.1: Duplicate CRUD form submit buttons at top of form.

5.18.2: Optimize master-slave inputs JS.

5.18.3: Remove redundant configurations sorting from configurations form type.

5.19.0: 

- Disable descendant checkboxes in tree if ancestor checkbox is not checked.

- Allow to reload page after property form submit. Usage example:

```yaml
form:
    index:
        fields:
            position:
                options:
                    attr:
                        data-reload-page: 1
```

5.19.2: Allow property forms with array values.

5.19.3: Escape search query.

5.19.4: Make form collections script recursive.

5.19.5: Make property form errors more verbose.

5.19.6: Fix recursive form collections.

5.19.8: Admin section configuration loader: proper merging of indexed arrays. For example you have

```yaml
a: [ b ]
```

in first config and

```yaml
a: [ c ]
```

in second. Now result config will contain

```yaml
a: [ b, c ]
```

instead of

```yaml
a: [ c ]
```

before.

5.20.0:

- migrate from Bower to NPM;

- do not limit countable inputs.

5.20.1: Move Gulp tasks to the resources directory.

5.20.2: Dropzone form type: added options "image_filters", "image_width" and "image_height", which help to generate
 recommended image size description. "image_filters" can be string or array containing Imagine filter set name(s). If
 multiple Imagine filters are provided, filter with biggest thumbnail size will be used. "image_width" and "image_height"
 have higher priority than "image_filters".
 
Admin section configuration examples:

```yaml
form:
    edit:
        fields:
            photos:
                type: Darvin\AdminBundle\Form\Type\Dropzone\DropzoneType
                options:
                    uploadable_class: App\Entity\PostImage
                    image_filters:    [ post_image, post_photo ]
```

```yaml
form:
    edit:
        fields:
            photos:
                type: Darvin\AdminBundle\Form\Type\Dropzone\DropzoneType
                options:
                    uploadable_class: App\Entity\PostImage
                    image_width:      640
                    image_height:     480
```

5.20.3: CRUD index action: render batch delete form only if batch delete view widget is enabled.

5.20.4:

- move new form widget on CRUD index page above the list;

- give common name to single submit button;

- do not show top buttons in the new form widget on CRUD index page.

5.20.5: Sort criteria detector: sort tree entities by level first.

5.20.6: Allow to configure HTML attributes of index view table cell.
 
Admin section configuration example:

```yaml
view:
    index:
        fields:
            text:
                attr:
                    class: text
```

5.20.7: Do not toggle compound property form buttons.

5.20.8: Render max file size description in file type admin form fields.

5.20.9: Translation sync JS: translate all fields (not only required).

5.20.10: Render max file size description in Dropzone form.

5.20.11: Do not render new action button in index view if index view new form is enabled.

5.21.0: Integrate Ace editor. Usage example:

```yaml
form:
    edit:
        fields:
            label:
                type: Darvin\AdminBundle\Form\Type\AceEditorType
                options:
                    config:
                        mode:  ace/mode/javascript
                        theme: ace/theme/dracula
                    style:
                        width: 50%
```

5.21.1:

- optimize initialization of the Ace editor;

- replace deprecated "sameas" with "same as" in templates.

5.21.2: Add "seo" menu group.

5.21.4: Update CRUD index HTML.

5.21.9: Upgrade elFinder bundle.

5.21.10: Init commands only in "dev" environment.

5.21.11: Use index widget template from section configuration.

5.21.12: Property form JS: disable cache while reloading page.

5.21.14: Escape flash messages for Javascript.

5.21.15: Allow to use Ace editor in configuration forms.

5.21.17: Configure default options of "public_link" view widget.

5.21.18: Pass association to the "child_links" view widget template.

5.21.19: Dropzone: validate image dimensions on client-side.

5.21.20: Fix action filter in log admin section.

5.21.21: Fix multiple Dropzone initialization.

5.21.22: Add "options" option to the translatable form type.

5.21.23: Do not wrap hidden inputs with any containers.

5.21.24: Init Dropzone on form collection add.

5.21.25: Translate entity passed to translation string.

5.21.26: CRUD index action: allow to sort by association's property.

5.22.0:
 
- Preserve filter data in URL without events.

- Do not preserve filter data in menu URLs.

5.23.0: Add Darvin\AdminBundle\Form\Type\ElFinderTextType form type.

5.23.1: Fix building error message in update property action.

5.23.2: Fix child entity detection in metadata manager.

5.23.3: Allow to disable Chosen with "data-custom: 0".

5.23.6: Support multiple criteria sorting of paginated results.

6.0.0:

- Replace Doctrine cache with Symfony cache.

- Remove redundant log entry custom entity repository.

- Remove "project.url" and "entity_override" parameters.

- Upload Dropzone temporary files to /tmp instead of cache dir.

- Title-case translations.

- Replace global JS variables with data attributes.

- Use Babel.

6.1.0:

- Redesign.
 
- Generate title-case translations.

- Replace auto form event listener to support Doctrine embeddables.

6.1.1:

- Replace field blacklist manager with authorization checker.

- Replace custom expression language with authorization checker in abstract view factory.

- Remove empty columns from index view.

6.2.0:

- Move permissions' configuration to bundle's configuration:

darvin_admin:
    permissions:
        ROLE_CONTENT_ADMIN:
            default: true
            subjects:
                Darvin\ECommerceBundle\Entity\Order\OrderInterface: false
                Darvin\OrderBundle\Entity\AbstractOrder:            false
                Darvin\QuestionsBundle\Entity\QuestionInterface:    false
                Darvin\ReviewBundle\Entity\ReviewInterface:
                    edit: false
        ROLE_ORDER_ADMIN:
            default:
                create_delete: false
            subjects:
                Darvin\ECommerceBundle\Entity\Order\OrderInterface: true
                Darvin\OrderBundle\Entity\AbstractOrder:            true
                Darvin\QuestionsBundle\Entity\QuestionInterface:    true
                Darvin\ReviewBundle\Entity\ReviewInterface:         true
                
6.2.1: Support "single instance" entities.

6.2.3: Do not blacklist "copy" route.

6.2.6: Render span instead of a[href="#"] in menu.

6.2.11:
 
- Move triplebox form type to Utils package.

- Set default help for slug suffix form type.

- Require copy action confirmation.

6.2.12: Add CRUD form reload functionality.

6.2.13: Clear form errors on CRUD form reload.

6.2.14: Change default parent selector in slug suffix form type.

6.2.15: Integrate content sorting functionality.

6.2.16: Upgrade paginator bundle.

6.2.17: Pass entity IDs instead of objects to batch delete form in order to avoid entity validation.

6.2.18: Remove pagination zero page hack.

6.2.19: Add imageable entity form type.

6.2.20: Allow to group form fields with "admin_group" option.

6.2.21: Add image exterminate action.

6.2.22: Store sidebar visibility in cookie.

6.2.23: Disable autocomplete in property form checkboxes.

6.2.24: Translate items in "simple_list" view widget.

6.2.25: Cleanup list of options passed to configuration form type.

6.2.26: Recreate config edit form after successful submit.

6.2.27: Allow to hide configurations with "hidden" option.

6.2.28:
 
- Change elFinder text form field HTML.

- Change slug suffix form field HTML.

6.3.0: 
 
- Store filter state in cookie.

- Remove field groups from admin section configs.

- Add "admin_spoiler" form option.

6.3.1:
 
- Update CRUD show HTML.

- Pass view widget style in request.

6.3.2: Enhance current menu item detecting.

6.3.3: Do not generate "new" route for abstract entities.

6.3.4: Allow to configure extra action widgets.

6.3.5: Render user menu in layout template.

6.3.6: Customize file form widget theme.

6.3.7:
 
- Reconfigure user roles.

- Fix action name passed to CRUD controller event.

- Add "phone_link" view widget.

6.3.8: Add extra check for user objects in authorization voter.

6.3.9: 

- Increase "darvin_admin_thumb" thumbnail size.

- Update image form theme.

6.3.10: Render separator after compound fields.

6.3.14: Add "exact_size" option to admin section's view configuration:

```yaml
view:
    index:
        fields:
            manager:
                exact_size: 32px
```

6.3.16: Add "heading_field" parameter to filter form's configuration.

6.3.17: Allow to deselect non-required selects.

6.3.18: Do not allow slashes in page slug.

6.3.19:
 
- Simplify CKEditor routing.

- Allow to add letters to CKEditor widget icons.

- Render generic checkboxes as tumblers.

- Simplify submit button translations.

- Move some translations to frontend bundle.

6.3.20: Configure compact CKEditor as full one.

6.3.21: Do not render tumblers in expanded choice widgets.

6.3.23: Menu separators.

```yaml
darvin_admin:
    menu:
        groups:
            ecommerce:
                position: 30
                separators:
                    directories: 200000
                    discounts:   300000
                    exchange:    400000
```

6.3.25: Allow to configure extra assets.

```yaml
darvin_admin:
    assets:
        scripts:
            - assets/scripts/my-super-script.js # Pathname to pass to asset() function
        styles:
            - assets/styles/my-super-style.css # Pathname to pass to asset() function
```

6.3.26: Load JS translations from "validators" domain.

6.3.27: Allow to specify route instead of asset pathname.

6.4.0: 

- Make controllers services.

- Make most of services private.

- Add tabs support to forms.

6.4.1:

- Replace "empty()" calls with null comparisons.

- Move service configs to "services" dir.

6.4.2: Upgrade vendors.

6.4.5: Use "object" type hint.

6.4.6: Register interfaces for autoconfiguration.

6.4.7: Change image edit template.

6.4.9:
 
- Remove redundant title case usages from translation models.

- Do not allow empty values in admin section's searchable fields.

6.4.10:

- Customize translatable form theme.

6.4.12: remove "Remove" button from Edit form and moved fast show

6.4.15: Generate image URL with "vich_uploader_asset()" function instead of "image_original()" filter.

6.4.16: Cast data type passed to Doctrine stringifier to string.

6.4.17: Changed config "Upload max size MB" to scalar type

6.4.18: Integrate autocomplete functionality from Content bundle.

6.4.19: Use dashes instead of underscores in autocomplete URLs.

6.4.20: 

- Resolve entity in all section configuration's methods.

- Extract section configuration interface.

6.4.24: Require "psr/simple-cache".

6.4.25: Support custom routers in public link view widget. 

6.4.26: Customize VichUploaderBundle's form theme.

6.4.27: Upgrade "willdurand/js-translation-bundle".

6.4.28: Added ability to control cache, example:

```yaml
darvin_admin:
    cache:
        clear:
            sets:
                widget:
                    commands:
                        doctrine_result: '@doctrine.cache_clear_result_command'
                list:
                    commands:
                        doctrine_result: '@doctrine.cache_clear_result_command'
```

6.4.29: 

- Added switcher for Dark Theme. 

- Removed time limit for cache cleaner.

6.4.30: Added separate js-script for dark theme

6.4.32: Pass form to CRUD events.

6.4.34: Ensure that cloner returned non-null value.

6.4.36: Replace non-maintained "stof/doctrine-extensions-bundle" with "antishov/doctrine-extensions-bundle".

6.4.37: Fixed translations for EN and Added translator for text widget

6.5.0: Add toolbar:

```twig
{{ admin_toolbar() }}
```

6.5.1: Add homepage support to toolbar.

6.5.4: Change temporary files directory from "/tmp" to "%kernel.project_dir%/var/tmp".

6.5.5: Upgrade elFinder bundle to 10.0.

6.5.7: Render compound form field help.

6.5.8: Clear cache on crud

6.5.9: Load CKEditor plugins from single file.

6.5.11: Allow to apply contents CSS in CKEditor.

6.5.12:
 
- Change CSS class in preview link widget.

- Replace "Редактировать" with "Изменить" in russian translations model.

- Change help CSS class in compound forms.

6.5.16: Upgrade oneup/uploader-bundle.

6.5.18: Allow WebP in elFinder.

6.5.19: Support SVG images.

6.5.23: Add specific CSS classes to date, time and datetime form fields.

6.5.24: Add "url_params" view widget.

6.6.0: Disable CSRF protection.

6.6.1: Fix property forms submitting (add not mapped field named "_").

6.6.2:
 
- Do not use form types in copy form and delete form view widgets.

- Do not use templates in actions, datetime, email link, entity list view widgets.

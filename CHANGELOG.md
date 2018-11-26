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
                    uploadable_class: AppBundle\Entity\PostImage
                    image_filters:    [ post_image, post_photo ]
```

```yaml
form:
    edit:
        fields:
            photos:
                type: Darvin\AdminBundle\Form\Type\Dropzone\DropzoneType
                options:
                    uploadable_class: AppBundle\Entity\PostImage
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

6.0.0:

- Replace Doctrine cache with Symfony cache.

- Remove redundant log entry custom entity repository.

- Remove "project.url" and "entity_override" parameters.

- Upload Dropzone temporary files to /tmp instead of cache dir.

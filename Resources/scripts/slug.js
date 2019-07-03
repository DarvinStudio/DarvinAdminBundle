(() => {
    const CLASSES = {
        changed:   'js-slug-changed',
        edit:      'js-slug-edit',
        form:      'js-slug-form',
        link:      'js-slug-link',
        reset:     'js-slug-reset',
        update:    'js-slug-update',
        urlPrefix: 'js-slug-url-prefix',
        widget:    'js-slug'
    };

    let SELECTORS = {};

    for (let name in CLASSES) {
        SELECTORS[name] = '.' + CLASSES[name];
    }

    const getWidget = ($child) => {
        return $child.closest(SELECTORS.widget);
    };
    const buildUrlPrefix = ($widget) => {
        let parentSlug = $widget.closest('form').find($widget.data('parent-select')).children('option:selected').data('slug');

        return $widget.data('base-url') + ('undefined' !== typeof parentSlug ? parentSlug + '/' : '');
    };
    const buildUrl = ($widget) => {
        let slugSuffix = $widget.find(SELECTORS.form + ' input').val();

        return buildUrlPrefix($widget) + (slugSuffix ? slugSuffix : '___') + $widget.data('url-suffix');
    };
    const updateWidget = ($widget) => {
        let $input = $widget.find(SELECTORS.form + ' input'),
            $reset = $widget.find(SELECTORS.reset);

        let slugSuffix = $input.val();

        $input.data('default').toString() !== slugSuffix ? $reset.show() : $reset.hide();

        $widget.find(SELECTORS.urlPrefix).text(buildUrlPrefix($widget));

        let url = buildUrl($widget);

        $widget.find(SELECTORS.link + ' a').attr('href', url).text(url);

        if ($widget.data('default-url').toString() !== url) {
            $widget.addClass(CLASSES.changed);

            return;
        }
        if (slugSuffix) {
            $widget.removeClass(CLASSES.changed);
        }
    };

    $(document).on(App.events.ajax.html, (e, args) => {
        args.$html.find(SELECTORS.widget).each((i, widget) => {
            let $widget = $(widget);

            let $form = $widget.closest('form');

            $widget.data('base-url', $widget.data('url-prefix'));

            if ($widget.data('parent-select')) {
                let $parent = $form.find($widget.data('parent-select'));

                if ($parent.length) {
                    let parentSlug = $parent.find('option:selected').data('slug');

                    if ('undefined' !== typeof parentSlug) {
                        let baseUrl = $widget.data('url-prefix');

                        let index = baseUrl.indexOf(parentSlug);

                        if (-1 !== index) {
                            $widget.data('base-url', baseUrl.slice(0, index));
                        }
                    }
                }
            }

            updateWidget($widget);

            $form.on('change', $widget.data('parent-select'), () => {
                updateWidget($widget);
            });

            $widget.find(SELECTORS.form + ' input').textcounter({
                countSpaces: true,
                counterText: '%d',
                max:         -1
            });
        });
    });

    $('body')
        .on('click', SELECTORS.widget + ' ' + SELECTORS.edit, (e) => {
            let $widget = getWidget($(e.currentTarget));

            $widget.find(SELECTORS.form).show();
            $widget.find(SELECTORS.link).hide();
        })
        .on('click', SELECTORS.widget + ' ' + SELECTORS.reset, (e) => {
            let $widget = getWidget($(e.currentTarget));

            let $input = $widget.find(SELECTORS.form + ' input');

            $input.val($input.data('default')).trigger('change');

            $widget.find(SELECTORS.update).trigger('click');
        })
        .on('click', SELECTORS.widget + ' ' + SELECTORS.update, (e) => {
            let $widget = getWidget($(e.currentTarget));

            updateWidget($widget);

            $widget.find(SELECTORS.form).hide();
            $widget.find(SELECTORS.link).show();
        })
        .on('click', SELECTORS.widget + SELECTORS.changed + ' ' + SELECTORS.link + ' a', (e) => {
            e.preventDefault();
        });
})();

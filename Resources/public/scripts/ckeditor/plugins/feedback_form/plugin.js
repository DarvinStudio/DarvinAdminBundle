CKEDITOR.plugins.add('feedback_form', {
    requires: 'widget',
    lang:     'en,ru',
    init:     function (editor) {
        editor.widgets.add('feedback_form', {
            template:        '<div class="feedback_form">%feedback_form%</div>',
            allowedContent:  'div(!feedback_form)',
            requiredContent: 'div(feedback_form)',
            upcast:          function (element) {
                return 'div' === element.name
                    && element.hasClass('feedback_form')
                    && element.children.length > 0
                    && '%feedback_form%' === element.children[0].value;
            }
        });

        editor.ui.addButton('feedback_form', {
            label:   editor.lang.feedback_form.button,
            command: 'feedback_form',
            icon:    this.path + 'images/icon.png?' + Math.random()
        });

        editor.addContentsCss(this.path + 'styles/styles.css?' + Math.random());
    }
});

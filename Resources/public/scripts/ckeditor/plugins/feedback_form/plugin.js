CKEDITOR.plugins.add('feedback_form', {
    requires: 'widget',
    icons:    'feedback_form',
    init:     function (editor) {
        editor.addContentsCss(this.path + 'styles/feedback_form.css');

        editor.widgets.add('feedback_form', {
            button:          'Add feedback form',
            template:        '<div class="feedback_form">%feedback_form%</div>',
            allowedContent:  'div(!feedback_form)',
            requiredContent: 'div(feedback_form)',
            upcast:          function (element) {
                return 'div' === element.name && element.hasClass('feedback_form');
            }
        });
    }
});

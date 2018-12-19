(() => {
    const SELECTORS = {
        container:  '.js-permissions',
        permission: '.js-permission[data-checkbox][data-class-checked][data-class-unchecked]'
    };

    $(document).on('app.html', (e, args) => {
        args.$html.find(SELECTORS.container + ' input').hide();
    });

    $('body').on('click', SELECTORS.permission, (e) => {
        let $button = $(e.currentTarget);

        let checkbox = document.getElementById($button.data('checkbox'));

        if ($button.hasClass($button.data('class-checked'))) {
            $button
                .removeClass($button.data('class-checked'))
                .addClass($button.data('class-unchecked'));
            checkbox.checked = false;

            return;
        }

        $button
            .removeClass($button.data('class-unchecked'))
            .addClass($button.data('class-checked'));
        checkbox.checked = true;
    });
})();

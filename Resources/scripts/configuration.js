$(document).ready(function () {
    $('.main_options_container .permissions input').hide();

    $('body').on('click', '.main_options_container .permission[data-checkbox][data-class-checked][data-class-unchecked]', function() {
        var $button = $(this);
        var checkbox = document.getElementById($button.data('checkbox'));

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
});

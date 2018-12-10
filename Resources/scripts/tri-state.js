$(() => {
    const CLASSES = [
        '',
        'checked',
        'unchecked'
    ];

    const check = ($checkbox, index) => {
        if (index < 0 || index > 2) {
            index = 0;
        }

        $checkbox
            .data('index', index).addClass(CLASSES[index])
            .find('input')[index].checked = true;
    };
    const uncheck = ($checkbox, index) => {
        $checkbox.removeClass(CLASSES[index])
            .find('input')[index].checked = false;
    };

    let init;
    (init = (context) => {
        $(context || 'body').find('.js-tri-state').each((i, checkbox) => {
            let $checkbox = $(checkbox);

            $checkbox.addClass('ready').find(':not(input)').remove();

            check($checkbox, $checkbox.find('input:checked').index());
        });
    })();

    $(document).on('app.html', (e, args) => {
        init(args.$html);
    });

    $('body').on('click', '.js-tri-state', (e) => {
        let $checkbox = $(e.currentTarget);

        let index = $checkbox.data('index');

        uncheck($checkbox, index);

        index++;

        check($checkbox, index);
    });
});

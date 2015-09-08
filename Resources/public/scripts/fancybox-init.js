$(document).ready(function () {
    $('body').on('click', 'a.fancybox_ajax', function (e) {
        e.preventDefault();

        $.ajax({
            type: 'get',
            url:  $(this).attr('href')
        }).done(function (html) {
            $.fancybox({
                content: html
            });
        }).error(onAjaxError);
    });
});

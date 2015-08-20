var AJAX_LOADER = '<span class="ajax_loader"></span>';

$(document).bind('ajaxComplete', function () {
    $('.ajax_loader').remove();

    $('form').removeData('submitted');
});

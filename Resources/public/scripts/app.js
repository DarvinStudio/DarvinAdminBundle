$(document).ready(function () {
    /* показать/скрыть сайдбар */
    $('#sidebar_switcher').on('mousedown', function () {
        var switch_btn = $('#sidebar_switcher');
        var sidebar_wrap = $('.sidebar_wrap');
        var content_wrap = $('.content_wrap');
        var main_wrap = $('.main_wrap');
        sidebar_wrap.toggleClass('active');
        content_wrap.toggleClass('active');
        main_wrap.toggleClass('active');
        switch_btn.toggleClass('active');
        if (switch_btn.hasClass('active')) {
            switch_btn.text(Translator.trans('layout.menu.collapse'));
            sidebar_wrap.removeClass('noactive');
            $.cookie(COOKIE_SIDEBAR, 1);
        } else {
            switch_btn.text(Translator.trans('layout.menu.expand'));
            sidebar_wrap.addClass('noactive');
            $.cookie(COOKIE_SIDEBAR, 0);
        }
        setTimeout(function () {
            resize()
        }, 300);
    });

    /* спойлер для модулей на главной*/
    $('.spoiler_links').on('mousedown', function () {
        var spoiler_container = $(this).parents('.spoiler_container');
        if (spoiler_container.find('.spoiler_body').css("display") == "none") {
            spoiler_container.find('.spoiler_body').hide('normal');
            spoiler_container.find('.spoiler_body').toggle('normal');
        }
        else spoiler_container.find('.spoiler_body').hide('normal');
        return false;
    });

    /* клик на пункте меню главной */
    $('.main_menu .item.parent a').on('click', function () {
        var item = $(this).parents('.parent');
        if (item.hasClass('active')) {
            item.removeClass('active');
        } else {
            item.addClass('active');
        }
        resize();
        setTimeout(resize(), 500);
        return false;
    });


    jQuery(document).ready(function () {
        resize();

        show_menu_function();
    });

    function show_menu_function() {
        var count = $('.main_menu .item').length;
    //    console.log(count);
        for (i = 0; i < count; i++) {
    //        console.log(i);
            setTimeout($('.main_menu .item:eq(' + i + ')').addClass('animate_start'), 700);
        }
    }

    window.onresize = resize;

    function resize() {
        /* меню на главной */
        $('.main_menu .item').height('auto');
        $('.main_menu .item .description').height('auto');

        var maxHeightItem = 0;
        var maxHeightItemText = 0;
        var maxHeightDownDrop = 0;
        $('.main_menu .item').each(function () {
            if ($(this).height() > maxHeightItem) {
                maxHeightItem = $(this).height();
            }
        });
        $('.main_menu .item .description').each(function () {
            if ($(this).height() > maxHeightItemText) {
                maxHeightItemText = $(this).height();
            }
        });
        $('.main_menu .main_dropdown_menu').each(function () {
            if ($(this).height() > maxHeightDownDrop) {
                maxHeightDownDrop = $(this).height();
            }
        });
        $('.main_menu .item').height(maxHeightItem);
        $('.main_menu .item .description').height(maxHeightItemText);
        if ($('.main_menu .item.parent.active').length > 0) {
            $('.main_menu .item.parent.active').css({'marginBottom': maxHeightDownDrop + 5});
        } else {
            $('.main_menu .item.parent').css({'marginBottom': '10px'});
            $('.main_menu .item').height('auto');
            $('.main_menu .item .description').height('auto');
        }
    //    console.log('resize')
    }
});

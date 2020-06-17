/* показать/скрыть сайдбар */
$('#sidebar_switcher').on('mousedown', function(){
	var switch_btn = $('#sidebar_switcher');
	var sidebar_wrap = $('.sidebar_wrap');
	var content_wrap = $('.content_wrap');
	var main_wrap = $('.main_wrap');
	sidebar_wrap.toggleClass('active');
	content_wrap.toggleClass('active');
	main_wrap.toggleClass('active');
	switch_btn.toggleClass('active');
	if(switch_btn.hasClass('active')){
		switch_btn.text(Translator.trans('layout.menu.collapse'));
		sidebar_wrap.removeClass('noactive');
        $.cookie(COOKIE_SIDEBAR, 1, {
            path: '/'
        });
	} else {
		switch_btn.text(Translator.trans('layout.menu.expand'));
		sidebar_wrap.addClass('noactive');
        $.cookie(COOKIE_SIDEBAR, 0, {
            path: '/'
        });
	}
	setTimeout( function(){ $(window).resize();}, 300);
});

/* спойлер */
$('.spoiler_links').on('mousedown', function(){
	var spoiler_container = $(this).parents('.spoiler_container');
    var body = spoiler_container.find('.spoiler_body');
    if (body.css("display")=="none")
	{
		body.hide('normal');
		body.toggle('normal');
        if ('undefined' !== typeof body.data('cookie')) {
            $.cookie(body.data('cookie'), 1, {
                path: '/'
            });
        }

        $(document).trigger('spoilerOpen', body);
	} else {
        body.hide('normal');
        if ('undefined' !== typeof body.data('cookie')) {
            $.cookie(body.data('cookie'), 0, {
                path: '/'
            });
        }
    }
	spoiler_container.toggleClass('noactive');
	return false;
 });
 
 /* клик на пункте меню главной */
 $('.main_menu .item.parent > .name a, .main_menu .item.parent > .img a').on('click', function(){
	var item = $(this).parents('.parent');
	if(item.hasClass('active')){
		item.removeClass('active');
	} else {
		$('.main_menu .item.parent.active').removeClass('active');
		resize();
		item.addClass('active');
		item.find('.main_dropdown_menu').css({'maxHeight' : item.find('.main_dropdown_menu_inner').height()+50});
	}
	resize();
	setTimeout( resize(), 500);
	return false;
});

/* фикс чекбоксов без нужной вёртски*/
var checkboxesInit;
(checkboxesInit = function (context) {
 $(context || 'body').find('.input_value input[type="checkbox"]').each(function(){
	$(this).wrap("<label></label>").wrap("<span class='checkbox'></span>");
	$(this).after("<span></span>")
 });
})();
$(document).on('formCollectionAdd', function (e, form) {
    checkboxesInit(form);
});

 
 jQuery(document).ready(function(){
	resize();
	
	show_menu_function();
	
	$('.content_wrap').css({'minHeight': $('.left_menu').height() });
 });

 function show_menu_function(){
	/*var count = $('.main_menu .item').length;
	console.log(count);
	for (i = 0; i < count; i++) {	
		console.log(i);
		setTimeout( $('.main_menu .item:eq('+i+')').addClass('animate_start'), 700);
	}*/
}

 window.onresize = resize;
 
 function resize(){
	/* меню на главной */
	$('.main_menu .item').height('auto');
	$('.main_menu .item .description').height('auto');
	
	var maxHeightItem = 0;
	var maxHeightItemText = 0;
	var maxHeightDownDrop = 0;
	$('.main_menu .item').each(function(){
		if($(this).height() > maxHeightItem){ maxHeightItem = $(this).height();}
	});
	$('.main_menu .item .description').each(function(){
		if($(this).height() > maxHeightItemText){ maxHeightItemText = $(this).height();}
	});
	$('.main_menu .main_dropdown_menu').each(function(){
		if($(this).height() > maxHeightDownDrop){ maxHeightDownDrop = $(this).height();}
	});
	$('.main_menu .item').height(maxHeightItem);
	$('.main_menu .item .description').height(maxHeightItemText);
	if( $('.main_menu .item.parent.active').length > 0 ){
		$('.main_menu .item.parent.active').css({'marginBottom': maxHeightDownDrop + 5});
	} else {
		$('.main_menu .item.parent').css({'marginBottom': '10px'});
		$('.main_menu .item').height('auto');
		$('.main_menu .item .description').height('auto');
	}
//	console.log('resize')
}


/* скролбар */
/**************************************************************/
// настройки скролбара
var slyOptions = {
    horizontal: 1,
    smart: 1,
    mouseDragging: 1,
    touchDragging: 1,
    releaseSwing: 1,
    scrollBar: $('.scrollbar'),
    speed: 300,
    activatePageOn: 'click',
    scrollBy: 100,
    dragHandle: 1,
    dynamicHandle: 1,
    clickBar: 1,
    scrollSource: ' '
};

// скрывает/показывает скрол бар, если он не нужен
function switchScrollBar($container) {
	var scrollbar = $container.find('.js-scrollbar');
	var handle = scrollbar.find('.handle');
    var scrollbar_width = scrollbar.eq(0).width();
    var handle_width = scrollbar.eq(0).find('.handle').width();
    var content_width = $container.find('.section_table > table').width();

    if(content_width > scrollbar_width){
        if(scrollbar_width <= handle_width + 5 ){ // 5 - магическое число, чтобы нивелировать разницу ширин при ресайзе
            scrollbar.css({'opacity':0});
            handle.css({'display': 'none'});
        } else {
            scrollbar.css({'opacity':1});
            handle.css({'display': 'block'});
        }
	} else {
        scrollbar.css({'opacity':0});
        handle.css({'display': 'none'});
	}
}
// инициализация скролбара
function initSly( container, options ) {
    // для каждого контенера
    $(container).each(function(){
        var $self = $(this);
        if ($self.hasClass('is-init')) return;
        $self.addClass('is-init');

        var $frame = $self.find('.sly-frame');

        options.scrollBar =  $self.find('.scrollbar');
        
        var sly = new Sly( $frame, options, {
            move: function () {
                scrollSync();
            }
        });

        sly.init();
        $self.find('.scrollbar').clone(true).appendTo($self);

        scrollSync();
        switchScrollBar($self);
        $(window).resize(function() {
            scrollSync();
            switchScrollBar($self);
            sly.reload();
        });

        function scrollSync() {
            var scrollbar1_handle = $self.find('.scrollbar').eq(0).find('.handle');
            var scrollbar2_handle = $self.find('.scrollbar').eq(1).find('.handle');
            scrollbar2_handle.attr('style', scrollbar1_handle.attr('style'))
        }
    });
}
setTimeout(initSly, 1, $('.sly-container'), slyOptions);
$(document).on('searchComplete', function(e, arg) {
    setTimeout(initSly, 100, arg, slyOptions);
});
/**************************************************************/
(function () {
    var selector = '.btn_toggle[data-cookie][data-id][data-text-open][data-text-close]';

    var toggle = function ($toggle, $item) {
        $toggle.parent().next().slideToggle(100);
        $toggle.toggleClass('is-open');
        $item.toggleClass('is-open');

        if ($item.hasClass('is-open')) {
            $toggle.text($toggle.attr('data-text-open'));

            return;
        }

        $toggle.text($toggle.attr('data-text-close'));
    };

    $('.main_options_item_container ' + selector).click(function () {
        var $toggle = $(this);
        var $item = $toggle.closest('.main_options_item');

        toggle($toggle, $item);

        var cookie = $.cookie($toggle.data('cookie'));
        var expanded = 'undefined' !== typeof cookie ? cookie.split(',') : [];

        if ($item.hasClass('is-open')) {
            if (-1 === expanded.indexOf($toggle.data('id'))) {
                expanded.push($toggle.data('id'));
            }
        } else {
            expanded.splice(expanded.indexOf($toggle.data('id')), 1);

            $item.find(selector + '[data-id!="' + $toggle.data('id') + '"].is-open').each(function () {
                var $toggle = $(this);

                toggle($toggle, $toggle.closest('.main_options_item'));

                expanded.splice(expanded.indexOf($toggle.data('id')), 1);
            });
        }

        $.cookie($toggle.data('cookie'), expanded.join(','), {
            path: '/'
        });
    })
})();
/**************************************************************/

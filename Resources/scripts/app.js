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
		switch_btn.text('Свернуть меню');
		sidebar_wrap.removeClass('noactive');
        $.cookie(COOKIE_SIDEBAR, 1, {
            path: '/'
        });
	} else {
		switch_btn.text('Показать меню');
		sidebar_wrap.addClass('noactive');
        $.cookie(COOKIE_SIDEBAR, 0, {
            path: '/'
        });
	}
	setTimeout( function(){ $(window).resize();}, 300);
});

(function selectpickerInit () {
    var locales = [ 'ru' ];

    var init;
    init = function () {
        $('select').each(function () {
            var $select = $(this);

            if ($select.children('option').length > 3) {
                $select.attr('data-live-search', 'true');
                $select.attr('data-width', 'auto');
            }

            $select.selectpicker({size: 10});
        });
    };

    -1 !== locales.indexOf(LOCALE)
        ? $.getScript('/bundles/darvinadmin/scripts/bootstrap/bootstrap-select-' + LOCALE + '.js').done(init)
        : init();
})();

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
	}
	resize();
	setTimeout( resize(), 500);
	return false;
});

/* фикс чекбоксов без нужной вёртски*/
 $('.input_value input[type="checkbox"]').each(function(){
	$(this).wrap("<label></label>").wrap("<span class='checkbox'></span>");
	$(this).after("<span></span>")
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
	console.log('resize')
}


/* скролбар */
$(function() {
    $('.scroll-pane').each(
        function() {
            $(this).jScrollPane({
                showArrows: $(this).is('.arrow')
            });
            var api = $(this).data('jsp');
            var throttleTimeout;
            $(window).bind(
                'resize',
                function() {
                    if (!throttleTimeout) {
                        throttleTimeout = setTimeout(
                            function() {
                                api.reinitialise();
                                throttleTimeout = null;
                            },
                            50
                        );
                    }
                }
            );
        }
    )
});

/**************************************************************/
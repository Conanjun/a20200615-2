﻿(function($) {
	$.fn.scrollPagination = function(options) {
		var opts = $.extend($.fn.scrollPagination.defaults, options);
		var target = opts.scrollTarget;
		if (target == null) {
			target = obj
		}
		opts.scrollTarget = target;
		return this.each(function() {
			$.fn.scrollPagination.init($(this), opts)
		})
	};
	$.fn.stopScrollPagination = function() {
		return this.each(function() {
			$(this).attr('scrollPagination', 'disabled')
		})
	};
	$.fn.scrollPagination.loadContent = function(obj, opts) {
		var target = opts.scrollTarget;
		var mayLoadContent = $(target).scrollTop() + opts.heightOffset >= $(document).height() - $(target).height();
		if (mayLoadContent) {
			if (opts.beforeLoad != null) {
				opts.beforeLoad()
			}
			$(obj).children().attr('rel', 'loaded');
			curpage = curpage + 1;
			$.ajax({
				type: 'GET',
				url: opts.contentPage + curpage,
				data: opts.contentData,
				success: function(data) {
					$(obj).append(data);
					var objectsRendered = $(obj).children('[rel!=loaded]');
					if (opts.afterLoad != null) {
						opts.afterLoad(objectsRendered)
					}
				},
				dataType: 'html'
			})
		}
	};
	$.fn.scrollPagination.init = function(obj, opts) {
		var target = opts.scrollTarget;
		$(obj).attr('scrollPagination', 'enabled');
		$(target).scroll(function(event) {
			if ($(obj).attr('scrollPagination') == 'enabled') {
				$.fn.scrollPagination.loadContent(obj, opts)
			} else {
				event.stopPropagation()
			}
		});
		$.fn.scrollPagination.loadContent(obj, opts)
	};
	$.fn.scrollPagination.defaults = {
		'contentPage': null,
		'contentData': {},
		'beforeLoad': null,
		'afterLoad': null,
		'scrollTarget': null,
		'heightOffset': 0
	}
})(jQuery);
$(function() {
	$('#content_list').scrollPagination({
		'contentPage': geturl,
		'contentData': {},
		'scrollTarget': $(window),
		'heightOffset': 10,
		'beforeLoad': function() {
			$('#loading').fadeIn()
		},
		'afterLoad': function(elementsLoaded) {
			$('#loading').fadeOut();
			$(elementsLoaded).fadeInWithDelay();
			if (curpage >= totalpage) {
				$('#nomoreresults').fadeIn();
				$('#content_list').stopScrollPagination()
			}
		}
	});
	
	$.fn.fadeInWithDelay = function() {
		var delay = 0;
		return this.each(function() {
			$(this).delay(delay).animate({
				opacity: 1
			}, 300);
			delay += 100
		})
	}
});
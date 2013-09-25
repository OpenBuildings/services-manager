(function (window, $, twttr, undefined) {

	var delayed, all_delayed;

	function parseElement(elem)	{
		elem = $(elem).get(0);
		
		if (typeof(PinterestPlus) !== 'undefined' && ! $(elem).data('social-pinterest')) {
			PinterestPlus.pinit(elem);
			$(elem).data('social-pinterest', true);
		}
		if (typeof(FB) !== 'undefined' && ! $(elem).data('social-facebook')) {
			FB.XFBML.parse(elem);
			$(elem).data('social-facebook', true);
		}
		if (typeof(twttr) !== 'undefined' && ! $(elem).data('social-twitter')) {
			$(elem).find('.inactive-twitter-share-button')
				.removeClass('inactive-twitter-share-button')
				.addClass('twitter-share-button');
			twttr.widgets.load();
			$(elem).data('social-twitter', true);
		}
	}

	function parseVisible() {
		$('.social-toolbox:visible').each(function(i, elem) {
			parseElement(elem);
		});
	}

	window.ServiceSocial = {
		parseVisible: function(delay) {
			clearTimeout(all_delayed);
			all_delayed = setTimeout(function(){
				parseVisible();
			}, (delay || 0) * 1000);
		},

		parse: function(elem, delay) {
			clearTimeout(delayed);
			delayed = setTimeout(function(){
				parseElement(elem);
			}, (delay || 0) * 1000);
		},

		clearDelay: function() {
			clearTimeout(delayed);
		}
	};

	$(function() {
		ServiceSocial.parseVisible(0.5);
	});
})(window, window.jQuery, window.twttr);
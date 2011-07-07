/*
// The Meneame source code is Free Software, Copyright (C) 2005-2009 by
// Ricardo Galli <gallir at gmail dot com> and Menéame Comunicacions S.L.
*/

(function($) {

	var defaults = { minChars: 3, matchSubset: 0, params: {avatar: 1} };

	$.fn.user_autocomplete = function(options) {

		$.extend(true, defaults, options);

		function add_avatar (input) {
			var name = input.val();
			var avatar_holder = $("#"+input.attr('id')+"_avatar");
			if (name.length > 0) {
				if (input.data('url') != false) url = input.data('url');
				else url = base_url+"backend/get_avatar.php?user="+name+"&size=20"
				if (avatar_holder.attr("src") != url) {
					avatar_holder.attr("src", url).css('visibility', 'visible');
				}
			} else {
				avatar_holder.css('visibility', 'hidden').attr("src", "");
			}
			input.data("url", false);
		}

		return this.each( function() {
			var input = $(this);
			input.attr("autocomplete", "off")
			input.data("url", false);
			input.autocomplete({ url:base_url+'backend/autocomplete_user.php',
				minChars: defaults.minChars,
				matchSubset: defaults.matchSubset,
				extraParams: defaults.params,
				showResult: function(value, data) {
					if (defaults.params.avatar)
						return '<img src="'+data[0]+'" width="12" height="12" style="vertical-align:bottom"/>&nbsp;'+value;
					else
						return value;
				},
				onItemSelect: function(item) {
					if (name.length > 0 && item.data.length > 0 && item.data[0].length > 0) {
						input.data("url", item.data[0]).change();
					} else {
						input.data("url", false);
					}
				}
			});
			if (defaults.params.avatar) {
				input.change(function () {setTimeout(function() {add_avatar(input)}, 100)});
			}
		});
	}

})(jQuery);

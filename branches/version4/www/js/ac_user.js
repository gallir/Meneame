/*
var ac_extraParams = { avatar: 1 };

if (typeof ac_minChars == "undefined") ac_minChars = 3;
if (typeof ac_friends != "undefined" && ac_friends) ac_extraParams.friends=1;

$(".ac_user").attr("autocomplete", "off").each( function () {
	var input = $(this);
	input.data("url", false);
	$(this).autocomplete({ url:base_url+'backend/autocomplete_user.php',
		minChars: ac_minChars, matchSubset: 0,
		extraParams: ac_extraParams,
		showResult: function(value, data) {
			return '<img src="'+data[0]+'" width="12" height="12" style="vertical-align:bottom"/>&nbsp;'+value;
		},
		onItemSelect: function(item) {
			if (name.length > 0 && item.data.length > 0 && item.data[0].length > 0) {
				input.data("url", item.data[0]).change();
			} else {
				input.data("url", false);
			}
		}
	}).change(function () {setTimeout(function() {form_add_avatar(input)}, 100)});
});

function form_add_avatar(input) {
	name = input.val();
	avatar_holder = $("#"+input.attr('id')+"_avatar");
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

*/

(function($) {
	var ac_extraParams = { avatar: 1 };

	if (typeof ac_minChars == "undefined") ac_minChars = 3;
	if (typeof ac_friends != "undefined" && ac_friends) ac_extraParams.friends = 1;

	$.fn.user_autocomplete = function(options) {

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
				minChars: ac_minChars, matchSubset: 0,
				extraParams: ac_extraParams,
				showResult: function(value, data) {
					return '<img src="'+data[0]+'" width="12" height="12" style="vertical-align:bottom"/>&nbsp;'+value;
				},
				onItemSelect: function(item) {
					if (name.length > 0 && item.data.length > 0 && item.data[0].length > 0) {
						input.data("url", item.data[0]).change();
					} else {
						input.data("url", false);
					}
				}
			}).change(function () {setTimeout(function() {add_avatar(input)}, 100)});
		});
	}

})(jQuery);


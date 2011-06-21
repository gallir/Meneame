var ac_selected_url = false;
var ac_extraParams = { avatar: 1 };

if (typeof ac_minChars == "undefined") ac_minChars = 3;
if (typeof ac_friends != "undefined" && ac_friends) ac_extraParams.friends=1;

$(".ac_user").attr("autocomplete", "off").autocomplete({ url:base_url+'backend/autocomplete_user.php',
	minChars: ac_minChars, matchSubset: 0,
	extraParams: ac_extraParams,
	showResult: function(value, data) {
		return '<img src="'+data[0]+'" width="12" height="12" style="vertical-align:bottom"/>&nbsp;'+value;
	},
	onItemSelect: function(item) {
		if (name.length > 0 && item.data.length > 0 && item.data[0].length > 0) {
			ac_selected_url = item.data[0];
			id = $(this).attr("id");
			$("#"+id+"_avatar").attr("src", ac_selected_url);
			$("#"+id+"_avatar").css('visibility', 'visible');
		} else {
			ac_selected_url = false;
		}
	},
}).change(function (t) {id = $(this).attr("id"); setTimeout(function () {form_add_avatar(id)}, 300) });

function form_add_avatar(id) {
	name = $("#"+id).val();
	if (name.length > 0) {
		if (ac_selected_url != false) url = ac_selected_url;
		else url = base_url+"backend/get_avatar.php?user="+name+"&size=20"
		$("#"+id+"_avatar").attr("src", url);
		$("#"+id+"_avatar").css('visibility', 'visible');
	} else {
		$("#"+id+"_avatar").css('visibility', 'hidden');
	}
	ac_selected_url = false;
}

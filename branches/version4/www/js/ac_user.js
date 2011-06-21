$(".ac_user").attr("autocomplete", "off").autocomplete({ url:base_url+'backend/autocomplete_user.php',
	minChars: 3, matchSubset: 0,
	showResult: function(value, data) {
		return '<img src="'+base_url+'backend/get_avatar.php?user='+value+'&amp;time='+data[0]+'&amp;size=20" width="12" height="12" style="vertical-align:bottom"/>&nbsp;'+value;
	},
}).change(function (t) {id = $(this).attr("id"); setTimeout(function () {form_add_avatar(id)}, 300) });

function form_add_avatar(id) {
	name = $("#"+id).val();
	if (name.length > 0) {
		$("#"+id+"_avatar").attr("src", base_url+"backend/get_avatar.php?user="+name+"&size=20");
		$("#"+id+"_avatar").css('visibility', 'visible');
	} else {
		$("#"+id+"_avatar").css('visibility', 'hidden');
	}
}

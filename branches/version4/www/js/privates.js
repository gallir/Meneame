function priv_show(content) {
	$.fancybox({content: content, autoDimensions: false, width: 500, height: 'auto', transitionIn: 'none'});
}

function priv_new(user_id) {
	var url = base_url + 'backend/priv_edit.php?user_id='+user_id+"&key="+base_key;

	$.fancybox({href: url, hideOnOverlayClick: false, titleShow: false, centerOnScroll: false, scrolling: 'no', autoDimensions: false, width: 500, height: 'auto'});
}

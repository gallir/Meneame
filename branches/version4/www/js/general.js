<?
/****************************
*
* WARN
*	  this files should be called from a generalxx.js.php file
*
*****************************/
?>

function redirect(url)
{
	document.location=url;
	return false;
}

function menealo(user, id)
{
	var url = base_url + "backend/menealo.php";
	var content = "id=" + id + "&user=" + user + "&key=" + base_key + "&l=" + link_id + "&u=" + document.referrer;
	url = url + "?" + content;
	disable_vote_link(id, -1, "...", '');
	$.getJSON(url,
		 function(data) {
				parseLinkAnswer(id, data);
		}
	);
	reportAjaxStats('vote', 'link');
}

function menealo_comment(user, id, value)
{
	var url = base_url + "backend/menealo_comment.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value + "&key=" + base_key + "&l=" + link_id ;
	url = url + "?" + content;
	$.getJSON(url,
		 function(data) {
			if (data.error) {
				mDialog.notify("<? echo _('Error:') ?> "+data.error, 5);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img src="'+data.image+'"/>');
				}
			}
		}
	);
	reportAjaxStats('vote', 'comment');
}

function menealo_post(user, id, value)
{
	var url = base_url + "backend/menealo_post.php";
	var content = "id=" + id + "&user=" + user + "&value=" + value + "&key=" + base_key + "&l=" + link_id ;
	url = url + "?" + content;
	$.getJSON(url,
		 function(data) {
			if (data.error) {
				mDialog.notify("<? echo _('Error:') ?> "+data.error, 5);
				return false;
			} else {
				$('#vc-'+id).html(data.votes+"");
				$('#vk-'+id).html(data.karma+"");
				if (data.image.length > 0) {
					$('#c-votes-'+id).html('<img src="'+data.image+'"/>');
				}
			}
		}
	);
	reportAjaxStats('vote', 'post');
}


function disable_vote_link(id, value, mess, background) {
	if (value < 0) span = '<span class="negative">';
	else span = '<span>';
	$('#a-va-' + id).html(span+mess+'</span>');
	if (background.length > 0) $('#a-va-' + id).css('background', background);
}

function parseLinkAnswer (id, link) {
	$('#problem-' + id).hide();
	if (link.error || id != link.id) {
		disable_vote_link(id, -1, "<? echo _('grr...') ?>", '');
		mDialog.notify("<? echo _('Error:') ?> "+link.error, 5);
		return false;
	}
	votes = parseInt(link.votes)+parseInt(link.anonymous);
	if ($('#a-votes-' + link.id).html() != votes) {
		$('#a-votes-' + link.id).hide();
		$('#a-votes-' + link.id).html(votes+"");
		$('#a-votes-' + link.id).fadeIn('slow');
	}
	$('#a-neg-' + link.id).html(link.negatives+"");
	$('#a-usu-' + link.id).html(link.votes+"");
	$('#a-ano-' + link.id).html(link.anonymous+"");
	$('#a-karma-' + link.id).html(link.karma+"");
	disable_vote_link(link.id, link.value, link.vote_description, '');
	return false;
}

function securePasswordCheck(field) {
	if (field.value.length > 5 && field.value.match("^(?=.{6,})(?=(.*[a-z].*))(?=(.*[A-Z0-9].*)).*$", "g")) {
		if (field.value.match("^(?=.{8,})(?=(.*[a-z].*))(?=(.*[A-Z].*))(?=(.*[0-9].*)).*$", "g")) {
			field.style.backgroundColor = "#8FFF00";
		} else {
			field.style.backgroundColor = "#F2ED54";
		}
	} else {
		field.style.backgroundColor = "#F56874";
	}
	return false;
}

function checkEqualFields(field, against) {
	if(field.value == against.value) {
		field.style.backgroundColor = '#8FFF00';
	} else {
		field.style.backgroundColor = "#F56874";
	}
	return false;
}

function enablebutton (button, button2, target)
{
	var string = target.value;
	if (button2 != null) {
		button2.disabled = false;
	}
	if (string.length > 0) {
		button.disabled = false;
	} else {
		button.disabled = true;
	}
}

function checkfield (type, form, field)
{
	var url = base_url + 'backend/checkfield.php?type='+type+'&name=' + encodeURIComponent(field.value);
	$.get(url,
		 function(html) {
			if (html == 'OK') {
				$('#'+type+'checkitvalue').html('<span style="color:black">"' + encodeURI(field.value) + '": ' + html + '</span>');
				form.submit.disabled = '';
			} else {
				$('#'+type+'checkitvalue').html('<span style="color:red">"' + encodeURI(field.value) + '": ' + html + '</span>');
				form.submit.disabled = 'disabled';
			}
		}
	);
	return false;
}

function check_checkfield(fieldname, mess) {
	field = document.getElementById(fieldname);
	if (field && !field.checked) {
		mDialog.notify(mess, 5);
		// box is not checked
		return false;
	}
}

function report_problem(frm, user, id) {
	if (frm.ratings.value == 0)
		return;
	mDialog.confirm("<? echo _('¿desea votar') ?> <em>" + frm.ratings.options[frm.ratings.selectedIndex].text +"</em>?",
		function () {report_problem_yes(frm, user, id)}, function () {report_problem_no(frm, user, id)});
	return false;
}

function report_problem_no(frm, user, id) {
		frm.ratings.selectedIndex=0;
}

function report_problem_yes(frm, user, id) {
	var content = "id=" + id + "&user=" + user + '&value=' +frm.ratings.value + "&key=" + base_key  + "&l=" + link_id + "&u=" + document.referrer;
	var url=base_url + "backend/problem.php?" + content;
	$.getJSON(url,
		 function(data) {
			parseLinkAnswer(id, data);
		}
	);
	reportAjaxStats('vote', 'link');
	return false;
}

// Get voters by Beldar <beldar.cat at gmail dot com>
// Generalized for other uses (gallir at gmail dot com)
function get_votes(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type+"&key="+base_key;
	$('#'+container).load(url);
	reportAjaxStats('html', program);
}

// This function report the ajax request to stats events if enabled in your account
// http://code.google.com/intl/es/apis/analytics/docs/eventTrackerOverview.html
function reportAjaxStats(category, action) {
	if (typeof(pageTracker) !=	'undefined' && typeof(pageTracker._trackEvent) !=  'undefined') {
		pageTracker._trackEvent(category, action);
	}
}

function bindTogglePlusMinus(img_id, link_id, container_id) {
	$(document).ready(function (){
		$('#'+link_id).bind('click',
			function() {
				if ($('#'+img_id).attr("src") == plus){
					$('#'+img_id).attr("src", minus);
				}else{
					$('#'+img_id).attr("src", plus);
				}
				$('#'+container_id).slideToggle("fast");
				return false;
			}
		);
	});
}

function clk(f, id) {
	f.href=base_url + 'backend/go.php?id=' + id;
	return true;
}

function fancybox_expand_images(event) {
	if (event.shiftKey) {
		event.preventDefault();
		event.stopImmediatePropagation();

		if(!$('.zoomed').size()) {
			$('body').find('.fancybox[href*=".jpg"] , .fancybox[href*=".gif"] , .fancybox[href*=".png"]').each(
				function() {
					var title=$(this).attr('title');
					var href=$(this).attr('href');
					var img='<div style="margin:10px auto;text-align:center;" class="zoomed"><img style="margin:0 auto;max-width:80%;padding:10px;background:#fff" src="' + href + '"/></div>';
					$(this).after(img);
					$(this).next().click(function(event) { if (event.shiftKey) $('.zoomed').remove(); });
				});
		} else {
			$('.zoomed').remove();
		}
	}
}

function fancybox_gallery(type, user, link) {
	var url = base_url +'backend/gallery.php?type='+type;
	if (typeof(user) != 'undefined') url = url + '&user=' + user;
	if (typeof(link) != 'undefined') url = url + '&link=' + link;

	if (!$('#gallery').size()) $('body').append('<div id="gallery" style="display:none"></div>');
	$('#gallery').load(url);
}

/**************************************
Tooltips functions
***************************************/
/**
  Stronglky modified, onky works with DOM2 compatible browsers.
	Ricardo Galli
  From http://ljouanneau.com/softs/javascript/tooltip.php
 */

// create the tooltip object
function tooltip(){}

// setup properties of tooltip object
tooltip.offsetx = 10;
tooltip.offsety = 10;
tooltip.box=null;

tooltip.show = function (event, text) {
	  // we save text of title attribute to avoid the showing of tooltip generated by browser
	if (this.box == null) {
		$('body').append('<div id="tooltip-text"></div>');
		this.box = $('#tooltip-text');
	}
	$(document).bind('mousemove', tooltip.mouseMove);
	this.box.html(text);
	this.box.show();
	return false;
}


tooltip.setText = function (text) {
	tooltip.box.html(text);
	return false;
}

tooltip.hide = function (event) {
	$(document).unbind('mousemove');
	tooltip.box.hide();
	tooltip.box.html('');
}

// Moves the tooltip element
tooltip.mouseMove = function (e) {
	xL = e.pageX + tooltip.offsetx;
	yL = e.pageY + tooltip.offsety;
	if (tooltip.box.width() > 0  && document.documentElement.clientWidth > 0 && xL > document.documentElement.clientWidth * 0.50) {
		xL = xL - tooltip.box.width() - 2*tooltip.offsetx - 10; // Padding is 10
	}
	tooltip.box.css("left", xL +"px").css("top", yL +"px");
}


tooltip.ajax_request = function(event, script, id) {
	var url = base_url + 'backend/'+script+'?id='+id;
	this.show(event, '');
	tooltip.box.load(url, function () {reportAjaxStats('tooltip', 'ajax');});
}

tooltip.action = function (event) {
	if (event.type == 'mouseenter') {
		try {
			args = $(this).attr('class').split(' ')[1].split(':');
			key = args[0];
			value = args[1];
		}
		catch (e) {
			tooltip.hide(event);
			return;
		}
		if (key == 'u') ajax = 'get_user_info.php';
		else if (key == 'p') ajax = "get_post_tooltip.php";
		else if (key == 'c') ajax = "get_comment_tooltip.php";
		else if (key == 'l') ajax = "get_link.php";
		else tooltip.hide(event);
		tooltip.ajax_request(event, ajax, value);
	} else {
		tooltip.hide(event);
	}
}
/**
 *  Based on mDialog from:
 *
	Kailash Nadh,	http://kailashnadh.name
**/

var mDialog = new function() {
	this.closeTimer = null;
	this.width = 0; this.height = 0;

	this.divBox = null;

	//________create a confirm box
	this.confirm = function(message, callback_yes, callback_no) {
		this.createDialog(message);
		this.btYes.show(); this.btNo.show();
		this.btOk.hide(); this.btCancel.hide(); this.btClose.hide();
		this.btYes.focus();

		// just redo this everytime in case a new callback is presented
		this.btYes.unbind().click( function() {
			mDialog.close();
			if(callback_yes) callback_yes();
		});

		this.btNo.unbind().click( function() {
			mDialog.close();
			if(callback_no) callback_no();
		});
	};

	//________prompt dialog
	this.prompt = function(message, content, callback_ok, callback_cancel) {
		this.createDialog($("<p>").append(message).append( $("<p>").append( $(this.input).val(content) ) ));

		this.btYes.hide(); this.btNo.hide();
		this.btOk.show(); this.input.focus(); this.btCancel.show();

		// just redo this everytime in case a new callback is presented
		this.btOk.unbind().click( function() {
			mDialog.close();
			if(callback_ok) callback_ok(mDialog.input.val());
		});

		this.btCancel.unbind().click( function() {
			mDialog.close();
			if(callback_cancel) callback_cancel();
		});

	};

	//________create an alert box
	this.alert = function(content, callback_ok) {
		this.createDialog(content);
		this.btCancel.hide(); this.btYes.hide(); this.btNo.hide(); this.btOk.show();
		this.btOk.focus();

		// just redo this everytime in case a new callback is presented
		this.btOk.unbind().click( function() {
			mDialog.close();
			if(callback_ok)
				callback_ok();
		});
	};


	//________create a dialog with custom content
	this.content = function(content, close_seconds) {
		this.createDialog(content);
		this.divOptions.hide();
	};

	//________create an auto-hiding notification
	this.notify = function(content, close_seconds) {
		if (this.divBox == null) this.init();
		mDialog.makeCenter();
		this.content(content);
		this.btClose.focus();
		if(close_seconds)
			this.closeTimer = setTimeout(function() { mDialog.close(); }, close_seconds*1000 );
	};

	//________dialog control
	this.createDialog = function(content) {
		if (this.divBox == null) this.init();
		clearTimeout(this.closeTimer);
		this.divOptions.show();
		this.divContent.html(content);
		this.divBox.fadeIn('fast');
		this.maintainPosition();
	};

	this.close = function() {
		this.divBox.fadeOut('fast');
		$(window).unbind('scroll.mDialog');
	};

	this.makeCenter = function() {
		$(mDialog.divBox).css (
			{
				top: ( (($(window).height() / 2) - ( ($(mDialog.divBox).height()) / 2 ) )) + ($(document).scrollTop()) + 'px',
				left: ( (($(window).width() / 2) - ( ($(mDialog.divBox).width()) / 2 ) )) + ($(document).scrollLeft()) + 'px'
			}
		);
	};
	this.maintainPosition = function() {
		mDialog.makeCenter();
		$(window).bind('scroll.mDialog', function() {
			mDialog.makeCenter();
		} );

	}

	//________
	this.init = function() {
		this.divBox = $("<div>").attr({ id: 'mDialog_box' });
		this.divHeader = $("<div>").attr({ id: 'mDialog_header' });
		this.divContent = $("<div>").attr({ id: 'mDialog_content' });
		this.divOptions = $("<div>").attr({ id: 'mDialog_options' });
		this.btYes = $("<button>").attr({ id: 'mDialog_yes' }).append( document.createTextNode("<? echo _('Sí') ?>") );
		this.btNo = $("<button>").attr({ id: 'mDialog_no' }).append( document.createTextNode("<? echo _('No') ?>") );
		this.btOk = $("<button>").attr({ id: 'mDialog_ok' }).append( document.createTextNode("<? echo _('Vale') ?>") );
		this.btCancel = $("<button>").attr({ id: 'mDialog_ok' }).append( document.createTextNode("<? echo _('Cancelar') ?>") );
		this.input = $("<input>").attr({ id: 'mDialog_input' });
		this.btClose = $("<span>").attr({ id: 'mDialog_close' }).append( document.createTextNode('X') ).click(
							function() {
								mDialog.close();
							});
		this.divHeader.append(  this.btClose );
		this.divBox.append(this.divHeader).append( this.divContent ).append(
			this.divOptions.append(this.btYes).append(this.btNo).append(this.btOk).append(this.btCancel)
		);

		this.divBox.hide();
		$('body').append( this.divBox );
	};

};

$(document).ready(function (){
	$('.tooltip').live('mouseenter mouseleave', tooltip.action);
	mDialog.init();
});

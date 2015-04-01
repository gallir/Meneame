var base_url="{{ globals.base_url_general }}",
	base_cache="{{ globals.cache_dir }}",
	version_id="v_{{ globals.v }}",
	base_static="{{ globals.base_static_noversion }}",
	is_mobile={{ globals.mobile }},
	touchable=false,
	loadedJavascript = [],
	{% if globals.allow_partial %}
		do_partial=true;
	{% else %}
		do_partial=false;
	{% endif %}

if (typeof window.history == "object"
		&& (do_partial || navigator.userAgent.match(/meneame/i)) ) {
	do_partial = true;
}

var now = (new Date);
var now_ts = now.getTime();

function to_date(index) {
		var str;
		var $e = $(this);
		var ts = $e.data('ts');
		if (typeof ts != 'number' || ! ts > 0) {
			return;
		}

		ts *= 1000;

		var d = new Date(ts);

		var dd = function (d) {
			if (d < 10) return "0"+d;
			else return d;
		};

		var diff = Math.floor((now_ts - ts)/1000);
		if (diff < 3600 && diff > 0) {
			if (diff < 60) {
				str = "{% trans _('hace') %} " + diff + " {% trans _('seg') %}";
			} else {
				str = "{% trans _('hace') %} " + Math.floor(diff/60) + " {% trans _('min') %}";
			}
		} else {
			str = "";
			if (diff > 43200 ) { /* 12 hs */
				str += dd(d.getDate())+"/"+dd(d.getMonth() + 1);
				if (now.getFullYear() != d.getFullYear()) {
					str += "/"+d.getFullYear();
				}
			}
			str += " " + dd(d.getHours())+":"+dd(d.getMinutes());
		}

		$e.attr('title', ($e.attr('title') || '') + str);
		if (! $e.hasClass("novisible")) {
			$e.html(str);
		}
}

function redirect(url) {
	document.location=url;
	return false;
}

function menealo(user, id) {
	var url = base_url + "backend/menealo";
	var content = "id=" + id + "&user=" + user + "&key=" + base_key + "&l=" + link_id + "&u=" + encodeURIComponent(document.referrer);
	url = url + "?" + content;
	disable_vote_link(id, -1, "...", '');
	$.getJSON(url,
		 function(data) {
			parseLinkAnswer(id, data);
		}
	);
	reportAjaxStats('vote', 'link');
}

function menealo_comment(user, id, value) {
	var url = base_url + "backend/menealo_comment";
	var content = "id=" + id + "&user=" + user + "&value=" + value + "&key=" + base_key + "&l=" + link_id ;
	url = url + "?" + content;
	respond_comment_vote(id, value);
	$.getJSON(url,
		 function(data) {
			update_comment_vote(id, value, data);
		}
	);
	reportAjaxStats('vote', 'comment');
}

function menealo_post(user, id, value) {
	var url = base_url + "backend/menealo_post";
	var content = "id=" + id + "&user=" + user + "&value=" + value + "&key=" + base_key + "&l=" + link_id ;
	url = url + "?" + content;
	respond_comment_vote(id, value);
	$.getJSON(url,
		 function(data) {
				update_comment_vote(id, value, data);
		}
	);
	reportAjaxStats('vote', 'post');
}

function respond_comment_vote(id, value) {
	$('#vc-p-'+id).addClass('voted').attr('onclick','').unbind('click');
	$('#vc-n-'+id).addClass('voted').attr('onclick','').unbind('click');
}


function update_comment_vote(id, value, data) {
	if (data.error) {
		mDialog.notify("{% trans _('Error:') %} "+data.error, 5);
		return false;
	} else {
		$('#vc-'+id).html(data.votes+"");
		$('#vk-'+id).html(data.karma+"");
		if (value < 0) {
			$('#vc-p-'+id).hide();
		} else {
			$('#vc-n-'+id).hide();
		}
	}
}

function disable_vote_link(id, value, mess, background) {
	if (value < 0) span = '<span class="negative">';
	else span = '<span>';
	$('#a-va-' + id).html(span+mess+'</span>');
	if (background.length > 0) $('#a-va-' + id).css('background', background);
}

function parseLinkAnswer (id, link) {
	var votes;
	$('#problem-' + id).hide();
	if (link.error || id != link.id) {
		disable_vote_link(id, -1, "{% trans _('grr...') %}", '');
		mDialog.notify("{% trans _('Error:') %} "+link.error, 5);
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

function enablebutton (button, button2, target) {
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

function checkfield (type, form, field) {
	var url = base_url + 'backend/checkfield?type='+type+'&name=' + encodeURIComponent(field.value);
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
	var field = document.getElementById(fieldname);
	if (field && !field.checked) {
		mDialog.notify(mess, 5);
		/* box is not checked */
		return false;
	}
}

function report_problem(frm, user, id) {
	if (frm.ratings.value == 0) return;
	mDialog.confirm("{% trans _('¿desea votar') %} <em>" + frm.ratings.options[frm.ratings.selectedIndex].text +"</em>?",
		function () {report_problem_yes(frm, user, id)}, function () {report_problem_no(frm, user, id)});
	return false;
}

function report_problem_no(frm, user, id) {
		frm.ratings.selectedIndex=0;
}

function report_problem_yes(frm, user, id) {
	var content = "id=" + id + "&user=" + user + '&value=' +frm.ratings.value + "&key=" + base_key	+ "&l=" + link_id + "&u=" + encodeURIComponent(document.referrer);
	var url = base_url + "backend/problem?" + content;
	$.getJSON(url,
		 function(data) {
			parseLinkAnswer(id, data);
		}
	);
	reportAjaxStats('vote', 'link');
	return false;
}

function pref_input_check (id) {
	var $e = $('#'+id);
	var value;
	var key = $e.val();
	var backend = base_url + 'backend/pref';

	$.post(backend,
		{"id": {{ current_user.user_id }}, "key": key, "control_key": base_key },
		function (data) {
			if (data) $e.prop('checked', true);
			else $e.prop('checked', false);

			$e.on('change', onChange);
		},
	'json');

	function onChange() {
		if (this.checked) value = 1;
		else value = 0;
		$.post(backend,
			{"id": {{ current_user.user_id }}, "value": value, "key": this.value, "set": 1, "control_key": base_key},
			function (data) {
				if (data) this.checked = true;
				else this.checked = false;
			},
		'json');
	}
}


function add_remove_sub(id, change) {
	var url = base_url + 'backend/sub_follow';

	change = (change ? 1 : 0);

	$.post(url,
		{ id: id, key: base_key, change: change },
		function(data) {
			if (data.error) {
				mDialog.notify("{% trans _('Error:') %}"+data.error, 5);
				return;
			}
			$button = $('#follow_b_'+id);
			if (data.value) {
				$button.addClass("on").removeClass("off");
			} else {
				$button.addClass("off").removeClass("on");
			}
		}
	, "json");
	reportAjaxStats('html', "sub_follow");
}

function add_remove_fav(element, type, id) {
	var url = base_url + 'backend/get_favorite';
	$.post(url,
		{ id: id, user: user_id, key: base_key, type: type },
		 function(data) {
				if (data.error) {
					mDialog.notify("{% trans _('Error:') %} "+data.error, 5);
					return;
				}
				if (data.value) {
					$('#'+element).addClass("on");
				} else {
					$('#'+element).removeClass("on");
				}
		}
	, "json");
	reportAjaxStats('html', "get_favorite");
}

/* Get voters by Beldar <beldar.cat at gmail dot com>
** Generalized for other uses (gallir at gmail dot com)
*/
function get_votes(program,type,container,page,id) {
	var url = base_url + 'backend/'+program+'?id='+id+'&p='+page+'&type='+type+"&key="+base_key;
	$e = $('#'+container);
	$e.load(url, function () {
		$e.trigger("DOMChanged", $e);
	});
	reportAjaxStats('html', program);
}

function readStorage(key) {
	if(typeof(Storage)!=="undefined") {
		return localStorage.getItem(key);
	} else {
		return readCookie(key);
	}
}

function writeStorage(key, value) {
	if(typeof(Storage)!=="undefined") {
		localStorage.setItem(key, value);
	} else {
		createCookie("n_"+user_id+"_ts", value, 0);
	}
}


function createCookie(name,value,days,path) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	} else var expires = "";

	if (path == null)  path="/";

	document.cookie = name+"="+value+expires+"; path=" + path;
}

function readCookie(name, path) {
	var ca = document.cookie ? document.cookie.split('; ') : [];
	for(var i=0; i < ca.length; i++) {
		var c = ca[i];
		var parts = ca[i].split('=');
		var key = parts.shift();
		if (name == key) {
			var value = parts.join('=');
			return value;
		}
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}


/* This function report the ajax request to stats events if enabled in your account
** http://code.google.com/intl/es/apis/analytics/docs/eventTrackerOverview.html
*/
function reportAjaxStats(category, action, url) {
	if (typeof(ga) != 'undefined') {
		if (category && action) {
			ga('send', 'event', category, action);
		}
		if (typeof url == 'string') {
			ga('send', 'pageview', url);
		}
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
	var is_public = parseInt({{ globals.media_public }}) > 0;
	if (! is_public && ! user_id > 0) {
		mDialog.notify('{% trans _('Debe estar autentificado para visualizar imágenes') %}', 5);
		return;
	}
	var url = base_url +'backend/gallery?type='+type;
	if (typeof(user) != 'undefined') url = url + '&user=' + user;
	if (typeof(link) != 'undefined') url = url + '&link=' + link;

	if (!$('#gallery').size()) $('body').append('<div id="gallery" style="display:none"></div>');
	$('#gallery').load(url);
}

/**
  Strongly modified, onky works with DOM2 compatible browsers.
	Ricardo Galli
  From http://ljouanneau.com/softs/javascript/tooltip.php
 */

(function ($){

	var x = 0;
	var y = 0;
	var offsetx = 7;
	var offsety = 0;
	var reverse = false;
	var top = false;
	var box = null;
	var timer = null;
	var active = false;
	var last = null;
	var ajaxs = {'u': 'get_user_info',
				'p': "get_post_tooltip",
				'c': "get_comment_tooltip",
				'l': "get_link",
				'b': "get_ban_info",
				'w': "get_comment_warn_tooltip"};


	$.extend({
		tooltip: function () {
			if (! is_mobile) start();
		}
	});

	function stop() {
		hide();
		$(document).off('mouseenter mouseleave', '.tooltip');
		$(document).off('touchstart', stop);
		touchable = true;
	}

	function start(o) {
		if (box == null) {
			box = $("<div>").attr({ id: 'tooltip-text' });
			$('body').append( box );
		}
		$(document).on('touchstart', stop); /* Touch detected, disable tooltips */
		$(document).on('mouseenter mouseleave', '.tooltip',
			function (event) {
				if (event.type == 'mouseenter') {
					try {
						var args = $(this).attr('class').split(' ');
						var i = args.indexOf('tooltip');
						args = args[i+1].split(':');
						var key = args[0];
						var value = args[1];
						var ajax = ajaxs[key];
						init(event);
						timer = setTimeout(function() {ajax_request(event, ajax, value)}, 200);
					} catch (e) {
						hide();
						return false;
					}
				} else if (event.type == 'mouseleave') {
					hide();
				}
				event.preventDefault();
			}
		);
	}

	function init(event) {
		if (timer || active) hide();
		active = true;

		$(document).on('onAjax', hide);
		$(document).on('mousemove.tooltip', function (e) { mouseMove(e) });
		if (box.outerWidth() > 0) {
			if ($(window).width() - event.pageX < box.outerWidth() * 1.05) reverse = true;
			else reverse = false;
			if ($(window).height() - (event.pageY - $(window).scrollTop()) < 200) top = true;
			else top = false;
		}
	}

	function show(html) {
		if (active) {
			if(typeof html == 'string')	box.html(html);
			if (box.html().length > 0) {
				position();
				box.show();
				box.trigger("DOMChanged", box);
			} else {
				hide();
			}
		}
	}

	function hide () {
		if (timer != null) {
			clearTimeout(timer);
			timer = null;
		}
		$(document).off('mousemove.tooltip');
		active = false;
		box.hide();
	}

	function position() {
		if (reverse) xL = x - (box.outerWidth() + offsetx);
		else xL = x + offsetx;
		if (top) yL = y - (box.outerHeight() + offsety);
		else yL = y + offsety;
		box.css({left: xL +"px", top: yL +"px"});
	}

	function mouseMove(e) {
		x = e.pageX;
		y = e.pageY;
		position();
	}

	function ajax_request(event, script, id) {
		timer = null;
		var url = base_url + 'backend/'+script+'?id='+id;
		if (url == last) {
			show();
		} else {
			$.ajax({
				url: url,
				dataType: "html",
				success: function(html) {
					last = url;
					show(html);
					reportAjaxStats('tooltip', script);
				}
			});
		}
	}
})(jQuery);

/**
 *	Based on jqDialog from:
 *	Kailash Nadh, http://plugins.jquery.com/project/jqDialog
**/

function strip_tags(html) {
	return html.replace(/<\/?[^>]+>/gi, '');
}

var mDialog = new function() {
	this.closeTimer = null;
	this.divBox = null;


	this.std_alert = function(message, callback) {
		alert(strip_tags(message));
		if (callback) callback();
	};

	this.std_confirm = function(message, callback_yes, callback_no) {
		if (confirm(strip_tags(message))) {
			if (callback_yes) callback_yes();
		} else {
			if (callback_no) callback_no();
		}
	};

	this.std_prompt = function(message, content, callback_ok, callback_cancel) {
		var res = prompt(message, content);
		if (res != null) {
			if (callback_ok) callback_ok(res);
		} else {
			if (callback_cancel) callback_cancel(res);
		}
	};

	this.confirm = function(message, callback_yes, callback_no) {
		if (is_mobile) {
			this.std_confirm(message, callback_yes, callback_no);
			return;
		}
		this.createDialog(message);
		this.btYes.show(); this.btNo.show();
		this.btOk.hide(); this.btCancel.hide(); this.btClose.hide();
		this.btYes.focus();

		/* just redo this everytime in case a new callback is presented */
		this.btYes.unbind().click( function() {
			mDialog.close();
			if(callback_yes) callback_yes();
		});

		this.btNo.unbind().click( function() {
			mDialog.close();
			if(callback_no) callback_no();
		});
	};

	this.prompt = function(message, content, callback_ok, callback_cancel) {
		if (is_mobile) {
			this.std_prompt(message, content, callback_ok, callback_cancel);
			return;
		}

		this.createDialog($("<p>").append(message).append( $("<p>").append( $(this.input).val(content) ) ));

		this.btYes.hide(); this.btNo.hide();
		this.btOk.show(); this.btCancel.show();
		this.input.focus();

		/* just redo this everytime in case a new callback is presented */
		this.btOk.unbind().click( function() {
			mDialog.close();
			if(callback_ok) callback_ok(mDialog.input.val());
		});

		this.btCancel.unbind().click( function() {
			mDialog.close();
			if(callback_cancel) callback_cancel();
		});
	};

	this.alert = function(content, callback_ok) {
		if (is_mobile) {
			this.std_alert(content, callback_ok);
			return;
		}
		this.createDialog(content);
		this.btCancel.hide(); this.btYes.hide(); this.btNo.hide();
		this.btOk.show();
		this.btOk.focus();

		this.btOk.unbind().click( function() {
			mDialog.close();
			if(callback_ok) callback_ok();
		});
	};


	this.content = function(content, close_seconds) {
		if (is_mobile) {
			this.std_alert(content, false);
			return;
		}
		this.createDialog(content);
		this.divOptions.hide();
	};

	this.notify = function(content, close_seconds) {
		if (is_mobile) {
			this.std_alert(content, false);
			return;
		}
		this.content(content);
		this.btClose.show().focus();
		if(close_seconds)
			this.closeTimer = setTimeout(function() { mDialog.close(); }, close_seconds*1000 );
	};

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
				top: ( (($(window).height() / 2) - ( mDialog.h / 2 ) )) + ($(document).scrollTop()) + 'px',
				left: ( (($(window).width() / 2) - ( mDialog.w / 2 ) )) + ($(document).scrollLeft()) + 'px'
			}
		);
	};

	this.maintainPosition = function() {
		mDialog.w = mDialog.divBox.width();
		mDialog.h = mDialog.divBox.height();
		mDialog.makeCenter();
		$(window).bind('scroll.mDialog', function() {
			mDialog.makeCenter();
		} );

	};

	this.init = function() {
		if (is_mobile) return;
		this.divBox = $("<div>").attr({ id: 'mDialog_box' });
		this.divHeader = $("<div>").attr({ id: 'mDialog_header' });
		this.divContent = $("<div>").attr({ id: 'mDialog_content' });
		this.divOptions = $("<div>").attr({ id: 'mDialog_options' });
		this.btYes = $("<button>").attr({ id: 'mDialog_yes' }).text("{% trans _('Sí') %}");
		this.btNo = $("<button>").attr({ id: 'mDialog_no' }).text("{% trans _('No') %}");
		this.btOk = $("<button>").attr({ id: 'mDialog_ok' }).text("{% trans _('Vale') %}");
		this.btCancel = $("<button>").attr({ id: 'mDialog_ok' }).text("{% trans _('Cancelar') %}");
		this.input = $("<input>").attr({ id: 'mDialog_input' });
		this.btClose = $("<span>").attr({ id: 'mDialog_close' }).text('X').click(
							function() {
								mDialog.close();
							});
		this.divHeader.append(	this.btClose );
		this.divBox.append(this.divHeader).append( this.divContent ).append(
			this.divOptions.append(this.btNo).append(this.btCancel).append(this.btOk).append(this.btYes)
		);

		this.divBox.hide();
		$('body').append( this.divBox );
	};

};

function comment_edit(id, DOMid) {
	$target=$('#' + DOMid).parent();
	$.getJSON(base_url_sub + 'comment_ajax', { id: id }, function (data) {
		if ( ! data.error ) {
			$target.html(data.html);
			$target.find('textarea').setFocusToEnd();
			$target.trigger('DOMChanged', $target);
			var options = {
				async: false,
				dataType: 'json',
				success: function (data) {
					if (! data.error) {
						$target.html(data.html);
					} else {
						mDialog.notify("error: " + data.error, 5)
					}
					$target.trigger('DOMChanged', $target);
				},
				error: function () {
					mDialog.notify("error", 3);
				},
			};
			$('#c_edit_form').ajaxForm(options);
		} else {
			mDialog.notify("error: " + data.error, 5);
		}
	});
}

function comment_reply(id, prefix) {
	prefix != null ? prefix : '';
	var $parent = $("#cid-"+prefix+id).parent();
	if ($parent.find('#comment_ajax_form').length > 0) {
		return;
	}

	$('#comment_ajax_form').remove();
	var $target = $('<div class="threader"></div>');
	$parent.append($target);

	$.getJSON(base_url_sub + 'comment_ajax', { reply_to: id }, function (data) {
		if ( ! data.error ) {
			var $e = $('<div id="comment_ajax_form" style="margin: 10px 0 20px 0"></div>');
			$e.append(data.html);
			$target.append($e).find('textarea').setFocusToEnd();

			var options = {
				async: false,
				dataType: 'json',
				success: function (data) {
					if (! data.error) {
						$e.remove();
						$target.append(data.html);
					} else {
						mDialog.notify("error: " + data.error, 5);
					}
					$target.trigger('DOMChanged', $target);
				},
				error: function () {
					mDialog.notify("error", 3);
				},
			};
			$('#c_edit_form').ajaxForm(options);
		} else {
			mDialog.notify("error", 3);
		}
		$target.trigger('DOMChanged', $target);
	});
}

function post_load_form(id, container) {
	var url = base_url + 'backend/post_edit?id='+id+"&key="+base_key;
	$.get(url, function (html) {
			if (html.length > 0) {
				if (html.match(/^ERROR:/i)) {
					mDialog.notify(html, 2);
				} else {
					$('#'+container).html(html).trigger('DOMChanged', $('#'+container));
				}
				reportAjaxStats('html', 'post_edit');
			}
		});
}


function post_new() {
	post_load_form(0, 'addpost');
}

function post_edit(id) {
	post_load_form(id, 'pcontainer-'+id);
}

function post_reply(id, user) {
	var ref = '@' + user + ',' + id + ' ';
	var others = '';
	var regex = /get_post_url(?:\.php){0,1}\?id=([a-z0-9%_\.\-]+(\,\d+){0,1})/ig; /* TODO: delete later (?:\.php)*/
	var text = $('#pid-'+id).html();
	var startSelection, endSelection, textarea;

	var myself = new RegExp('^'+user_login+'([\s,]|$)', 'i' );
	while (a = regex.exec(text)) { /* Add references to others */
		u = decodeURIComponent(a[1]);
		if (! u.match(myself)) { /* exclude references to the reader */
			others = others + '@' + u + ' ';
		}
	}
	if (others.length > 0) {
		startSelection = ref.length;
		endSelection = startSelection + others.length;
		ref = ref + others;
	} else {
		startSelection = endSelection = 0;
	}
	textarea = $('#post');
	if (textarea.length == 0) {
		post_new();
	}
	post_add_form_text(ref, 1, startSelection, endSelection);
}

function post_add_form_text(text, tries, start, end) {
	if (! tries) tries = 1;
	var textarea = $('#post');
	if (tries < 20 && textarea.length == 0) {
			setTimeout(function () { post_add_form_text(text,tries+1,start,end) }, 100);
			return false;
	}
	if (textarea.length == 0 ) {
			return false;
	}
	var re = new RegExp(text);
	var oldtext = textarea.val();
	if (oldtext.match(re)) return false;
	var offset = oldtext.length;
	if (oldtext.length > 0 && oldtext.charAt(oldtext.length-1) != ' ') {
		oldtext = oldtext + ' ';
		offset = offset + 1;
	}
	textarea.val(oldtext + text);
	var obj = textarea[0];
	obj.focus();
	if ('selectionStart' in obj && start > 0 && end > 0) {
		obj.selectionStart = start + offset;
		obj.selectionEnd = end + offset;
	}
}

/* See http://www.shiningstar.net/articles/articles/javascript/dynamictextareacounter.asp?ID=AW */
var textCounter = function (field,cntfield,maxlimit) {
	if (textCounter.timer) return;
	textCounter.timer = setTimeout( function () {
		textCounter.timer = false;
		var length = field.value.length;
		if (length > maxlimit) {
			field.value = field.value.substring(0, maxlimit);
			length = maxlimit;
		}
		if (textCounter.length != length) {
			cntfield.value = maxlimit - length;
			textCounter.length = length;
		}
	}, 300);
};
textCounter.timer = false;
textCounter.length = 0;

/*
  Code from http://www.gamedev.net/community/forums/topic.asp?topic_id=400585
  strongly improved by Juan Pedro López for http://meneame.net
  2006/10/01, jotape @ http://jplopez.net
*/

function applyTag(caller, tag) {
	/* find first parent form and the textarea */
	var obj = $(caller).parents("form").find("textarea")[0];
	if (obj) wrapText(obj, tag, tag);
	return false;
}

function wrapText(obj, tag) {
	obj.focus();
	if(typeof obj.selectionStart == 'number') {
		/* Mozilla, Opera and any other true browser */
		var start = obj.selectionStart;
		var end   = obj.selectionEnd;

		if (start == end || end < start) return false;
		obj.value = obj.value.substring(0, start) +  replaceText(obj.value.substring(start, end), tag) + obj.value.substring(end, obj.value.length);
	} else if(document.selection) {
		/* Damn Explorer */
		/* Checking we are processing textarea value */
		var range = document.selection.createRange();
		if(range.parentElement() != obj) return false;
		if (range.text == "") return false;
		if(typeof range.text == 'string')
			document.selection.createRange().text =  replaceText(range.text, tag);
	} else {
		obj.value += text;
	}
}

function replaceText(text, tag) {
		return '<'+tag+'>'+text+'</'+tag+'>';
}

/* Privates */
function priv_show(content) {
	$.colorbox({html: content, width: 500, transition: 'none', scrolling: false});
}

function priv_new(user_id) {
	var w, h;
	var url = base_url + 'backend/priv_edit?user_id='+user_id+"&key="+base_key;
	if (is_mobile) {
		w = h = '100%';
	} else {
		w = '600px';
		h = '350px';

	}
	$.colorbox({href: url,
		onComplete: function () {
			if (user_id > 0) $('#post').focus();
			else $("#to_user").focus();
		},
		'onOpen': function () {
			historyManager.push('priv_new', $.colorbox.close);
		},
		'onClosed': function () {
			historyManager.pop('priv_new');
		},
		overlayClose: false,
		transition: 'none',
		title: false,
		scrolling: true,
		open: true,
		width: w,
		height: h
	});
}

/* Answers */
function get_total_answers_by_ids(type, ids) {
	$.ajax({
		type: 'POST',
		url: base_url + 'backend/get_total_answers',
		dataType: 'json',
		data: { "ids": ids, "type": type },
		success: function (data) { $.each(data, function (ids, answers) { show_total_answers(type, ids, answers) } ) }
	});
	reportAjaxStats('json', 'total_answers_ids');
}

function get_total_answers(type, order, id, offset, size) {
	$.getJSON(base_url + 'backend/get_total_answers', { "id": id, "type": type, "offset": offset, "size": size, "order": order },
		function (data) { $.each(data, function (ids, answers) { show_total_answers(type, ids, answers) } ) });
	reportAjaxStats('json', 'total_answers');
}

function show_total_answers(type, id, answers) {
	if (type == 'comment') dom_id = '#cid-'+ id;
	else dom_id = '#pid-'+ id;
	element = $(dom_id).siblings(".comment-meta").children(".comment-votes-info");
	element.append('&nbsp;<span onClick="javascript:show_answers(\''+type+'\','+id+')" title="'+answers+' {% trans _('respuestas') %}" class="answers"><span class="counter">'+answers+'</span></span>');
}

function show_answers(type, id) {
	var program, dom_id, answers;

	if (type == 'comment') {
		program = 'get_comment_answers';
		dom_id = '#cid-'+ id;
	} else {
		program = 'get_post_answers';
		dom_id = '#pid-'+ id;
	}
	answers = $('#answers-'+id);
	if (answers.length == 0) {
		$.get(base_url + 'backend/'+program, { "type": type, "id": id }, function (html) {
			element = $(dom_id).parent().parent();
			element.append('<div class="comment-answers" id="answers-'+id+'">'+html+'</div>');
			element.trigger('DOMChanged', element);
		});
		reportAjaxStats('html', program);
	} else {
		answers.toggle();
	}
}

function share_fb(e) {
	var $e = $(e);

	window.open(
		'https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent($e.parent().data('url')),
		'facebook-share-dialog',
		'width=626,height=436');
	return false;
}

function share_tw(e) {
	var $e = $(e);
	window.open(
		'https://twitter.com/intent/tweet?url='+encodeURIComponent($e.parent().data('url'))+'&text='+encodeURIComponent($e.parent().data('title')),
		'twitter-share-dialog',
		'width=550,height=420');
	return false;
}

/* scrollstop plugin for jquery +1.9 */
(function(){
	var latency = 75;
	var handler;
	$.event.special.scrollstop = {
		setup: function() {
			var timer;
			handler = function(evt) {
				var _self = this,
					_args = arguments;

				if (timer) {
					clearTimeout(timer);
				}
				timer = setTimeout( function(){
					timer = null;
					evt.type = 'scrollstop';
					$(_self).trigger(evt, [_args]);
				}, latency);
			};

			$(this).on('scroll', handler);
		},
		teardown: function() {
			$(this).off('scroll', handler);
		}
	};

})(jQuery);

(function () {
	var panel = false;


	$("#nav-menu").on('click', function() {
		prepare();
		if (panel.is(":visible")) {
			$('html').off('click', click_handler);
			panel.hide();
		} else {
			$('html').on('click', click_handler);
			panel.show();
		}
	});

	function prepare() {
		if (panel == false) {
			panel = $('<div id="nav-panel"></div>');
			panel.appendTo("body");
			$(window).on('unload onAjax', function() {
				panel.empty();
				panel.hide();
			});
		} else if ( panel.children().length > 0 ) {
			return;
		}

		if (is_mobile) {
			panel.append($('#searchform'));
			panel.append($('#header-menu .header-menu01'));
		} else {
			panel.append($('#searchform').clone());
			panel.append($('#header-menu .header-menu01').clone());
		}
	};

	function click_handler(e) {
		if (! panel.is(":visible")) return;
		if ($(e.target).closest('#nav-panel, #nav-menu').length == 0) {
			panel.hide();
			e.preventDefault();
		}
	};
})();

/* Drop an image file
** Modified from http://gokercebeci.com/dev/droparea
*/
(function( $ ){
	var s;
	var m = {
		init: function(e){},
		start: function(e){},
		complete: function(r){},
		error: function(r){ mDialog.alert(r.error); return false; },
		traverse: function(files, area) {

			var form = area.parents('form');
			form.find('input[name="tmp_filename"], input[name="tmp_filetype"]').remove();

			if (typeof files !== "undefined") {
				if (m.check_files(files, area)) {
					for (var i=0, l=files.length; i<l; i++) {
						m.upload(files[i], area);
					}
				}
			} else {
				mDialog.notify("{% trans _('formato no reconocido') %}", 5);
			}
		},

		check_files: function(files, area) {
			if (typeof File != "undefined"	&& files != undefined) {
				for (var i = 0; i < files.length; i++) {
					/* File type control */
					if (files[i].type.length > 0 && !files[i].type.match('image.*')) {
						mDialog.notify("{% trans _('sólo se admiten imágenes') %}", 5);
						return false;
					}
					if (files[i].fileSize > s.maxsize) {
						mDialog.notify("{% trans _('tamaño máximo excedido') %}" + ":<br/>" + files[i].fileSize + " > " + s.maxsize + " bytes", 5);
						return false;
					}
				}
			}
			return true;
		},

		upload: function(file, area) {
			var form = area.parents('form');
			var thumb = form.find('.droparea_info img').attr('src', s.loaderImage).show();
			var submit = form.find(':submit');

			submit.attr('disabled', 'disabled');

			var xhr = new XMLHttpRequest();

			/* File uploaded */
			xhr.addEventListener("load", function (e) {
				var r = jQuery.parseJSON(e.target.responseText);
				if (typeof r.error === 'undefined') {
					thumb.attr('src', r.thumb).show();
					form.find('input[name="tmp_filename"], input[name="tmp_filetype"]').remove();
					form.append('<input type="hidden" name="tmp_filename" value="'+r.name+'"/>');
					form.append('<input type="hidden" name="tmp_filetype" value="'+r.type+'"/>');
					s.complete(r);
				} else {
					thumb.hide();
					s.error(r);
				}
				submit.removeAttr('disabled');
			}, false);

			xhr.open("post", s.post, true);

			/* Set appropriate headers */
			xhr.setRequestHeader("Content-Type", "multipart/form-data-alternate");
			if (typeof file.fileSize != "undefined") {
				xhr.setRequestHeader("X-File-Size", file.fileSize);
			}
			xhr.send(file);
		}
	};
	$.fn.droparea = function(o) {
		/* Check support for HTML5 File API */
		if (!window.File) return;

		/* Settings */
		s = {
			'post': base_url + 'backend/tmp_upload',
			'init': m.init,
			'start': m.start,
			'complete': m.complete,
			'error': m.error,
			'maxsize': 500000, /* Bytes */
			'show_thumb': true,
			'hide_delay': 2000,
			'backgroundColor': '#AFFBBB',
			'backgroundImage': base_static + version_id + '/img/common/upload-2x.png',
			'loaderImage': base_static + version_id + '/img/common/uploading.gif'
		};

		this.each(function(){
			if(o) $.extend(s, o);
			var form = $(this);

			s.init(form);

			form.find('input[type="file"]').change(function () {
				m.traverse(this.files, $(this));
				$(this).val("");
			});

			if (s.show_thumb) {
				var thumb = $('<img width="32" height="32"/>').hide();
				form.find('.droparea_info').append(thumb);
			}

			form.find('.droparea')
			.bind({
				dragleave: function (e) {
					var area = $(this);
					e.preventDefault();
					area.css(area.data('bg'));
				},

				dragenter: function (e) {
					e.preventDefault();
					$(this).css({
						'background-color': s.backgroundColor,
						'background-image': 'url("'+s.backgroundImage+'")',
						'background-position': 'center',
						'background-repeat': 'no-repeat'
						});
				},

				dragover: function (e) {
					e.preventDefault();
				}
			})
			.each(function() {
				var bg;
				var area = $(this);

				bg = {
					'background-color': area.css('background-color'),
					'background-image': area.css('background-image'),
					'background-position': area.css('background-position')
				};
				area.data("bg", bg);
				this.addEventListener("drop", function (e) {
					e.preventDefault();
					s.start(area);
					m.traverse(e.dataTransfer.files, area);
					area.css(area.data('bg'));
				},false);
			});
		});
	};
})( jQuery );

/*
	FileInput bsed on jQuery.NiceFileInput.js
	By Jorge Moreno - @alterebro
*/
(function($) {
	$.fn.nicefileinput = function(options) {
		var settings = {
			label : '',
			title : '{% trans _("subir imagen") %}',
		};
		if(options) { $.extend(settings, options); };

		return this.each(function() {
			var self = this;

			if ($(self).attr('data-styled') === undefined) {

				var r = Math.round(Math.random()*10000);
				var d = new Date();
				var guid = d.getTime()+r.toString();

				var wrapper = $("<div>")
					.css({
						'overflow': 'hidden',
						'position': 'relative',
						'display': 'inline-block',
						'white-space': 'nowrap',
						'text-align': 'center'
					})
					.addClass('uploadFile-button upload'+guid).html(settings.label).attr("title", settings.title);

				$(self).wrap(wrapper);

				$('.uploadFile'+guid).wrapAll('<div class="uploadFile-wrapper" id="upload-wrapper-'+guid+'" />');
				$('.uploadFile-wrapper').css({
					'overflow': 'auto',
					'display': 'inline-block'
				});
				$("#uploadFile-wrapper-"+guid).addClass($(self).attr("class"));

				$(self)
					.css({
						'visibility': 'visible',
						'opacity': 0,
						'position': 'absolute',
						'border': 'none',
						'margin': 0,
						'padding': 0,
						'top': 0,
						'right': 0,
						'cursor': 'pointer',
						'height': '30px'
					})
					.addClass('uploadFile-current').attr("title", settings.title);
				$(self).on("change", function() {});
				$(self).attr('data-styled', true);
			}
		});

	};
})(jQuery);


var historyManager = new function () {
	var history = [];

	if (typeof window.history.pushState != "function") return;

	$(window).on("popstate", function(e) {
		if (history.length == 0) return;
		var state = history.pop();
		if (typeof state.callback == "function") {
			state.callback(state);
		}
	});

	this.push = function (name, callback) {
		if (typeof window.history.pushState != "function") return;

		var state = { id: history.length, name: name, href: location.href};
		var new_href;
		if (name.charAt(0) == "/")  {
			new_href = name;
		} else {
			new_href = "#" + name;
		}
		window.history.pushState(state, '', new_href);
		state.callback = callback;
		history.push(state);
		reportAjaxStats('', '', new_href);
	};

	this.pop = function (name) {
		if (history.length == 0) return;

		window.history.go(-1);
	};

};

var fancyBox = new function () {
	this.parse = function ($e) {
		var iframe = false, title = false, html = false, href = false, innerWidth = false, innerHeight = false, maxWidth, maxHeight, onLoad = false, onComplete = false, v, myClass, width = false, height = false, overlayClose = true, target = '';
		var myHref = $e.data('real_href') || $e.attr('href');
		var myTitle, photo = false;
		var ajaxName = "image";

		if ($e.attr('target')) {
			target = ' target="'+$e.attr('target')+'"';
		}

		if ((v = myHref.match(/(?:youtube\.com\/(?:embed\/|.*v=)|youtu\.be\/)([\w\-_]+).*?(#.+)*$/))) {
			if (is_mobile || touchable) return false;
			iframe = true;
			title = '<a target="_blank" href="'+myHref+'"'+target+'>{% trans _('vídeo en Youtube') %}</a>';
			href = 'https://www.youtube.com/embed/'+v[1];
			if (typeof v[2] != "undefined") href += v[2];
			innerWidth = 640;
			innerHeight = 390;
			maxWidth = false;
			maxHeight = false;
			ajaxName = "youtube";
		} else if ( (v = myHref.match(/twitter\.com\/.+?\/(?:status|statuses)\/(\d+)/)) ) {
			title = '<a target="_blank" href="'+myHref+'"'+target+'>{% trans _('en Twitter') %}</a>';
			html=" ";
			if (is_mobile)  {
				width = '100%';
				height = '100%';
			} else {
				innerWidth = 550;
				innerHeight = 500;
			}
			maxWidth = false;
			maxHeight = false;
			ajaxName = "tweet";
			onComplete = function() {
				var options = { s: "tweet", id: v[1] };
				$.getJSON(base_url+"backend/json_cache", options,
					function (data) {
						if (typeof data.html != "undefined" && data.html.length > 0 ) {
							$('#cboxLoadedContent').html(data.html);
						} else {
							$('#cboxLoadedContent').html('<a target="_blank" href="'+myHref+'">Not found</a>');
						}
					});
			};
		} else if ( (v = myHref.match(/(?:vimeo\.com\/(\d+))/)) ) {
			title = '<a target="_blank" href="'+myHref+'"'+target+'>{% trans _('vídeo en Vimeo') %}</a>';
			if (is_mobile)  {
				width = '100%';
				height = '100%';
			} else {
				innerWidth = 640;
				innerHeight = 400;
			}
			maxWidth = "100%";
			maxHeight = "100%";
			ajaxName = "vimeo";
			href = '//player.vimeo.com/video/'+v[1];
			iframe = true;
		} else if ( (v = myHref.match(/(?:vine\.co\/v\/(\w+))/)) ) {
			title = '<a target="_blank" href="'+myHref+'"'+target+'>{% trans _('vídeo en Vine') %}</a>';
			if (is_mobile)  {
				innerWidth = 320;
				innerHeight = 320;
			} else {
				innerWidth = 480;
				innerHeight = 480;
			}
			maxWidth = false;
			maxHeight = false;
			ajaxName = "vine";
			href = 'https://vine.co/v/'+v[1]+'/embed/simple';
			iframe = true;
		} else {
			if (myHref.match(/\.(x\-){0,1}(gif|jpeg|jpg|pjpeg|pjpg|png|tif|tiff)$/)) {
				photo = true;
			}
			myTitle = $e.attr('title');
			if (myTitle && myTitle.length > 0 && myTitle.length < 30) title = myTitle;
			else title = '{% trans _('enlace original') %}';
			title = '<a target="_blank" href="'+myHref+'"'+target+'>'+title+'</a>';
			href = myHref;
			if (is_mobile) {
				width = '100%';
				height = '100%';
			} else {
				maxWidth = '75%';
				maxHeight = '75%';
			}
		}

		myClass = $e.attr('class');
		if ( typeof myClass == "string" && (linkId = myClass.match(/l:(\d+)/))) {
			/* It's a link, call go.php */
			var link = linkId[1];
			setTimeout(function() {
				$.get(base_url_sub + 'go?quiet=1&id='+link);
			}, 10);
		}

		$.colorbox({
			'html': html,
			'photo': photo,
			'href': href,
			'transition': 'none',
			'width': width,
			'height': height,
			'maxWidth': maxWidth,
			'maxHeight': maxHeight,
			'opacity': 0.5,
			'title': title,
			'iframe': iframe,
			'innerWidth': innerWidth,
			'innerHeight': innerHeight,
			'overlayClose': overlayClose,
			'onLoad': onLoad,
			'onOpen': function () {
				historyManager.push(ajaxName, $.colorbox.close);
			},
			'onComplete': onComplete,
			'onClosed': function () {
				 historyManager.pop(ajaxName);
			}
		});
		return true;
	};
};

/* notifier */
(function () {
	var timeout = false;
	var area;
	var panel_visible = false;
	var current_count = -1;
	var has_focus = true;
	var check_counter = 0;
	var base_update = 15000;
	var last_connect = null;

	if (! user_id > 0 || (area = $('#notifier')).length == 0) return;
	$(window).on('unload onAjax', function() { hide(); });

	area.click(click);
	$(window).on("DOMChanged", function () {current_count = -1; restart(); });
	$(window).focus(restart);

	$(window).blur(function() {
		has_focus = false;
	});

	setTimeout(update, 1500); /* We are not in a hurry */


	function click_handler(e) {
		if (! panel_visible) return;
		if ($(e.target).closest('#notifier_panel').length == 0) {
			/* click happened outside */
			hide();
			e.preventDefault();
		}
	};

	function click() {
		if (! panel_visible) {
			panel_visible = true;
			$e = $('<div id="notifier_panel"> </div>');
			$e.appendTo("body");
			$('html').one('click', click_handler);

			data = decode_data(readStorage("n_"+user_id));

			var a = ['privates', 'posts', 'comments', 'friends'];
			for (var i=0; i < a.length; i++) {
				field = a[i];
				var counter = (data && data[field]) ? data[field] : 0;
				$e.append("<div class='"+field+"'><a href='"+base_url_sub+"go?id="+user_id+"&what="+field+"'>" + counter + " " + field_text(field) + "</a></div>");
			}
			$e.show();
			check_counter = 0;
		} else {
			hide();
			update();
		}
		return false;
	};


	function hide() {
		$("#notifier_panel").remove();
		panel_visible = false;
	};

	function update() {
		var next_update;
		var now;

		now = new Date().getTime();
		var last_check = readStorage("n_"+user_id+"_ts");
		if (last_check == null
				|| (check_counter == 0 && now - last_check > 3000) /* Avoid too many refreshes */
				|| now - last_check > base_update + check_counter * 20) {
			writeStorage("n_"+user_id+"_ts", now);
			connect();
		} else {
			update_panel();
		}

		if (! has_focus) {
			next_update = 8000;
		} else {
			next_update = 4000;
		}

		if (is_mobile) next_update *= 2;

		if ( (is_mobile && check_counter < 1) /* one network update for mobiles */
				|| (! is_mobile && check_counter < 3*3600*1000/base_update)) {
			timeout = setTimeout(update, next_update);
		} else {
			timeout = false;
		}
	};

	function update_panel() {
		var count;
		var posts;

		data = decode_data(readStorage("n_"+user_id));
		if (! data) {
			return;
		}
		if (data.total == current_count) {
			return;
		}

		document.title = document.title.replace(/^\(\d+\) /, '');
		area.html(data.total);
		 $('#p_c_counter').html(data.posts);
		if (data.total > 0) {
			area.addClass('nonzero');
			document.title = '('+data.total+') ' + document.title;
		} else {
			area.removeClass('nonzero');
		}
		current_count = data.total;
	};

	function connect() {
		var next_check;

		var connect_time = new Date().getTime();

		if (connect_time - last_connect < 2000) { /* to avoid flooding */
			return;
		}

		check_counter++;
		last_connect = connect_time;

		$.getJSON(base_url+"backend/notifications.json?check="+check_counter+"&has_focus="+has_focus,
			function (data) {
				var now;
				now = new Date().getTime();
				writeStorage("n_"+user_id+"_ts", now);
				if (current_count == data.total) return;
				writeStorage("n_"+user_id, encode_data(data));
				update_panel();
			});
	};

	function restart() {
		check_counter = 0;
		has_focus = true;
		if (timeout) {
			clearTimeout(timeout);
			timeout = false;
		}
		update();
	}

	function decode_data(str) {
		if (! str) return null;
		var a = str.split(",");
		return {total: a[0], privates: a[1], posts: a[2], comments: a[3], friends: a[4]};
	}

	function encode_data(data) {
		var a = [data.total, data.privates, data.posts, data.comments, data.friends];
		return a.join(",");
	}

	function field_text(field) {
		var a = {
			privates: "{% trans _('privados nuevos') %}",
			posts: "{% trans _('respuestas a notas') %}",
			comments: "{% trans _('respuestas a comentarios') %}",
			friends: "{% trans _('nuevos amigos') %}"
		};
		return a[field];
	}
})();


/**
 * jQuery Unveil modified and improved to accept options and base_url
 * Heavely optimized with timer and checking por min movement between scroll
 * http://luis-almeida.github.com/unveil
 * https://github.com/luis-almeida
 */

(function($) {

  $.fn.unveil = function(options, callback) {

	var settings = {
		threshold: 10,
		base_url: '',
		version: false,
		cache_dir: false
	};

	var $w = $(window),
		timer,
		retina = window.devicePixelRatio > 1.2,
		images = this,
		selector = $(this).selector,
		loaded;

	if (options) {
		$.extend(settings, options);
	}

	if (settings.base_url.charAt(settings.base_url.length-1) != '/') {
		settings.base_url += "/";
	}

	var cache_regex;
	if (settings.cache_dir) {
		cache_regex = new RegExp("^"+settings.cache_dir+"/");
	}


	this.one("unveil", handler);

	/* We trigger a DOMChanged event when we add new elements */
	$w.on("DOMChanged", function(event, parent) {
		var $e = $(parent);
		var n = $e.find(selector).not(images).not(loaded);
		if (n.length == 0) return;
		n.one("unveil", handler);
		images = images.add(n);
		n.trigger("unveil");
	});

	function handler() {
		var $e = $(this);
		var source = $e.data("src");
		if (! source) return;

		if (source.charAt(0) == "/" && source.charAt(1) != "/") source = source.substr(1);

		if (retina) {
			var high = $e.data('2x');
			if (high) {
				if (high.indexOf("s:") == 0) {
					var parts = high.split(":");
					source = source.replace(parts[1], parts[2]);
				} else {
					source = high;
				}
			}
		}

		var version_prefix;
		var base_url = settings.base_url;
		if (settings.version && settings.base_url.length > 1 && source.substr(0,4) != 'http' && source.substr(0,2) != '//') {
			if (! cache_regex || ! cache_regex.test(source)) {
				base_url += settings.version + "/";
			}
			source = base_url + source;
		}
		$e.attr("src", source);
		if (typeof callback === "function") callback.call(this);
	}

	function unveil() {
		var wt = $w.scrollTop();
		var wb = wt + $w.height();

		var inview = images.filter(":visible").filter(function() {
			var $e = $(this);

			var et = $e.offset().top,
				eb = et + $e.height();

			return eb >= wt - settings.threshold && et <= wb + settings.threshold;
		});

		loaded = inview.trigger("unveil");
		images = images.not(loaded);
	}

	$w.on('scrollstop resize', unveil);
	unveil();

	return this;

  };

})(jQuery);


function analyze_hash(force) {
	if (location.hash && (m = location.hash.match(/#([\w\-]+)$/)) && (target = $('#'+m[1])).length > 0 ) {
		target.css('opacity', 0.2);
		{# Highlight a comment if it is referenced by the URL. Currently double border, width must be 3 at least #}
		if (link_id > 0 && (m2 = m[1].match(/^c-(\d+)$/)) && m2[1] > 0) {
			/* it's a comment */
			if ( target.length > 0) {
				var e = $("#"+m[1]).find(".comment-body");
				e.css("border-style","solid").css("border-width","1px");
				{# If there is an anchor in the url, displace 80 pixels down due to the fixed header #}
			} else {
				/* It's a link to a comment, check it exists, otherwise redirect to the right page */
				canonical = $("link[rel^='canonical']");
				if (canonical.length > 0) {
					self.location = canonical.attr("href") + "/c0" + m2[1] + '#c-' + m2[1];
					return;
				}
			}
		}
		if (force) {
			setTimeout(function () {animate(target, force)}, 10);
		} else {
			/* Delay scrolling until the document is shown */
			$(window).load(function () {animate(target)});
		}
	}

	function animate(target, force) {
		var $h = $('#header-top');
		if (force || $h.css('position') == 'fixed' && $(document).scrollTop() > target.offset().top - $h.height() ) {
			$(window).scrollTop(target.offset().top - $h.height() - 10);
		}
		target.animate({opacity: 1.0}, 200);
	}

}

(function($){
	$.fn.setFocusToEnd = function() {
		this.focus();
        var $initialVal = this.val();
		this.val('').val($initialVal);
		return this;
	};
})(jQuery);

(function () { /* partial */
	$(document).on("click mousedown touchstart", "a", parse);
	if (do_partial) {
		console.log("Enabled partial");
		var sequence = 0;
		var last = 0;


		String.prototype.decodeHTML = function() {
			return $("<div>", {html: "" + this}).html();
		};

		$(window).on("popstate", function(e) {
			state = e.originalEvent.state;
			if (state != null) {
				if (state.name == "partial" && state.sequence != last ) {
					load(location.href, e.originalEvent.state);
    			}
			}
		});
	}

	function parse(e) {
		var m;
		var $a = $(this);
		var href = $a.attr("href");
		var aClass = $a.attr("class") || '';

		if (e.type != "click") {
			if ($a.data('done')) return true;
			if ((m = aClass.match(/l:(\d+)/)) && ! aClass.match(/tooltip/) ) {
				$a.attr('href', base_url_sub + "go?id=" + m[1]);
				$a.data('done', 1);
				$a.data('real_href', href);
			}
			return true;
		}

		var real_href = $a.data('real_href') || $a.attr('href');
		if ( (aClass.match(/fancybox/)
				|| real_href.match(/\.(gif|jpeg|jpg|pjpeg|pjpg|png|tif|tiff)$|vimeo.com\/\d+|vine\.co\/v\/\w+|youtube.com\/(.*v=|embed)|youtu\.be\/.+|twitter\.com\/.+?\/(?:status|statuses)\/\d+/i))
			&& ! aClass.match(/cbox/)
			&& ! $a.attr("target")) {
			if (fancyBox.parse($a)) return false;
		}

		if (! do_partial) return true;

		/* Only if partial */
		var re = new RegExp("^/|^\\?|//"+location.hostname);
		if ((location.protocol == "http:" || location.protocol == "https:" ) && re.test(href) && ! href.match(/\/backend\/|\/login|\/register|\/profile|\/sneak|rss2/)) {
			href = href.replace(/partial&|\?partial$|&partial/, '');
			load(href, null);
			return false;
		}
	}

	function load(href, state) {
		var currentState;
		var a = href;

		a = a.replace(/#.*/, '');

		if (a.indexOf("?") < 0) a += "?";
		else  a += "&";
		a += "partial";

		$e = $("#variable");
		$("body").css('cursor', 'progress').trigger('onAjax');
		if (! state) {
			currentState = {name: "partial", scroll:  $(window).scrollTop() };
			if ( ! history.state) {
				currentState.sequence = 0;
			} else {
				currentState.sequence = history.state.sequence;
			}
			history.replaceState(currentState, '', location.href);

			sequence++;
			last = sequence;
			currentState.sequence = last;
			currentState.scroll = 0;
			history.pushState(currentState, '', href);
		} else {
			currentState = state;
			last = currentState.sequence;
		}

		$.ajax(a, {
			cache: true,
			dataType: "html",
			success: function (html) {
				$("body").css('cursor', 'default');
				console.log("Loaded: " + href + " scroll: " + currentState.scroll);
				var finalHref = loaded($e, href, html);
				if (! state && href != finalHref) {
					history.replaceState(currentState, '', finalHref);
				}
				if (! finalHref) return false;
				if ('scroll' in currentState) {
					window.scrollTo(0, currentState.scroll);
				}
				execOnDocumentLoad();
				$e.trigger("DOMChanged", $e);
				analyze_hash(true);
			},
			error: function () {
				location.href = href;
				return false;
			}
		});

	}

	function loaded($e, href, html) {
		$e.html(html);
		var $info = $e.find("#ajaxinfo");
		if ($info.length) {
			if ($info.data('uri')) {
				var uri = $info.data('uri');
				uri = uri.replace(/partial&|\?partial$|&partial/, '');
				if (href.match(/#.*/)) {
					var hash = href.replace(/.*(#.*)/, "$1");
					uri = uri + hash;
				}
				href = uri;
			}
			if ($info.data('title')) {
				document.title = $info.data('title');
			}
		} else {
			/* Bad data */
			console.log("Bad data, location to: " + location.href);
			location.href = href;
			return false;
		}
		return href;
	}

})();


function loadJS(url) {
	return $.ajax({ url: url,
		dataType: "script",
		async: true,
		cache: true,
		success: function () {
			loadedJavascript.push(this.url);
		}
	});
}

function execOnDocumentLoad() {
	var deferred = $.Deferred();
	deferred.resolve();

	$.each(postJavascript, function(ix, url) {
		if ($.inArray(url, loadedJavascript) < 0) {
			deferred = deferred.then(function () {
				return loadJS(url);
			});
		}
	});

	deferred.then(function () {
		postJavascript = [];

		$.each(onDocumentLoad, function (ix, code) {
			try {
				if (typeof code == "function") {
					code();
				} else {
					eval(code);
				}
			} catch(err) {
				console.log(err);
			}
		});
		onDocumentLoad = [];
	});
}

/* *=*=* Menemoji Keyboard *=*=* */
var emojiKey = new function() {
	var $panel = null;
	var $html = null;
	var $textarea;


	this.keyboard = function (caller) {
		$(caller).toggleClass('active');
		var commentObj = $(caller).closest('form');
		$textarea = commentObj.find('textarea');
		if(commentObj.find('.emoji-kbd').length) {
			emojiKey.close();
		} else {
			emojiKey.close();
			if (! $html) {
				$.ajax({
					method: "GET",
					url: base_url + 'backend/menemoji_kbd',
					data: { v: version_id },
					cache: true,
					success: function (data) {
							$html = $(data);
							$panel = $html.insertAfter($textarea);
							emojiKey.open();
						},
					});
			} else {
				$panel = $html.insertAfter($textarea);
				emojiKey.open();
			}
		}
		$textarea.setFocusToEnd();
		return false;
	};

	this.open = function() {
		/* Evento de botones emoji */
		$panel.find('.emoji-btn').on('click', function(e) {
			e.preventDefault();
			var emojiCode = $(this).data('emoji');
			emojiKey.insert(emojiCode);
		});

		/* Evento de tabs de teclado emoji */
		$panel.find('.emoji-tab').on('click', function(e){
			e.preventDefault();
			$panel.find('.emoji-tab').removeClass('active');
			$panel.find('.emoji-panel').removeClass('active');
			var emojiPanel = $(this).data('target');
			$(this).addClass('active');
			$panel.find('#'+emojiPanel).addClass('active');
		});
	};

	this.close = function() {
		if ($panel) {
			$panel.remove();
			$panel = null;
		}
	};

	this.insert = function(emojiCode) {
		var caretPos = $textarea[0].selectionStart;
		var textAreaTxt = $textarea.val();
		var txtToAdd = '{'+emojiCode+'} ';
		$textarea.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos) );
		$textarea.setFocusToEnd();
	};
};

$(document).ready(function () {
	var m, m2, target, canonical;

	/* Put dates */
	$('span.ts').each(to_date);
	$.ajaxSetup({ cache: false });

	$(window).on("DOMChanged",
		function(event, parent) {
			$(parent).find('span.ts').each(to_date);
			execOnDocumentLoad();
		}
	);

	mDialog.init();

	analyze_hash();

	execOnDocumentLoad();

	$('img.lazy').unveil({base_url: base_static, version: version_id, cache_dir: base_cache, threshold: 100});

	$.tooltip();

	$('.showmytitle').on('click', function () {
		mDialog.content('<span style="font-size: 12px">'+$(this).attr('title')+'</span>');
	});

	if (! readCookie("sticky") && ! readCookie("a") ) {
		setTimeout(function() {
			$.ajax({
				cache: true,
				url: base_static + "js/cookiechoices.js",
				dataType: "script",
				success: function () {
					cookieChoices.showCookieConsentBar('Nos obligan a molestarte con la obviedad de que este sitio usa cookies',
					'cerrar', 'más información', base_url + "legal#cookies");
					}
				});
			}, 2000);
	}

});


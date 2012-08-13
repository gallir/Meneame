<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <meta content="text/html; charset=UTF8" http-equiv="content-type">
  <link rel="stylesheet" href="{{globals.base_static}}css/es/nivea_2012.css" type="text/css" media="screen" />
  <link href='http://fonts.googleapis.com/css?family=Open+Sans|Open+Sans+Condensed:300' rel='stylesheet' type='text/css'>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js" type="text/javascript"></script> 
  <script src="{{globals.base_url}}js/{{globals.js_main}}" type="text/javascript"></script>
  <title>menéame / Nivea For Men</title>

</head>

{%spacefull%}
<script type="text/javascript">
if(top.location != self.location)top.location = self.location;
base_key="{{ globals.security_key }}";
user_id = {{ current_user.user_id }};
user_login = '{{ current_user.user_login }}';
mobile_client = {{ globals.mobile }};
</script>

<script>
function disable_league_votes(text)
{
	$('.team-votes-menealo').addClass('disabled').text(text);
}

$(document).ready(function() {
	var values = {A: 1, B: 2, C: 0},
		vstatus = ["{% trans _("presionado") %}", "{% trans _("menéalo") %}"],
		url	 = "{{globals.base_url}}backend/league_vote.php?";

	$('.team-votes-menealo').click(function(e) {
		e.preventDefault();
		var self = $(this),
            match  = self.parents('div.game'),
			parent = self.parents('div.team');
            link_id = match.attr('id').match(/match-([0-9]+)/)[1],
			vote = values[parent.attr('class').match(/team.*-([abc])$/i)[1]];

		var objects = {
			local: $('.team-A .team-votes-number', match),
			visitor: $('.team-B .team-votes-number', match),
			tied: $('.team-votes-box-C .team-votes-number', match)
		};

		if (self.hasClass('disabled')) {
			return;
		}

		var content = {id: link_id, user: user_id, key: base_key, u: document.referrer, vote: vote};
		$.getJSON(url + $.param(content), function(data) {
			if (data.error) {
				disable_league_votes('{% trans _("grr...")%}');
				/**  fixme! (mDialog doesn't work) */
				mDialog.notify("{% trans _('Error:') %} "+ data.error, 5);
				alert("{% trans _('Error:') %} "+ data.error, 5);
				return;
			}
			objects.local.text(data.local);
			objects.visitor.text(data.visitor);
			objects.tied.text(data.tied);

			var target;
			switch (parseInt(data.voted)) {
			case 1:
				target = objects.local;
				break;
			case 2:
				target = objects.visitor;
				break;
			case 0:
				target = objects.tied;
				break;
			}

			if (target) {
				$('.team .team-votes-menealo', match).text("{% trans _("menéalo") %}");
                $('.team-votes-button-A > div', match).addClass('team-votes-button-B');
				target
                  .parents('.team')
                  .find('.team-votes-button-A > div').attr('class', '').end()
                  .find('.team-votes-menealo').text("{% trans _("presionado") %}");
			}
		});
	});
});
</script>
{% endspacefull %}

<body>

<!--Facebook stuff-->
<div id="fb-root"></div>
<img class="banner-top" src="{{globals.base_static}}img/nivea_2012/banner_00.png" width="386" height="200" alt="Nivea For Men" title="Logo Nivea For Men" />

<div class="bases">
	<p>Presentación / Enlace a las bases / Información legal</p>
</div><!--class bases-->

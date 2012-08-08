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
link_id = {{ match.id }};
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
		objects = {
			local: $('.team-A .team-votes-number'),
			visitor: $('.team-B .team-votes-number'),
			tied: $('.team-votes-box-C .team-votes-number')
		};

	$('.team-votes-menealo').click(function(e) {
		e.preventDefault();
		var self = $(this),
			parent = self.parents('div.team');
			vote = values[parent.attr('class').match(/team.*-([abc])$/i)[1]];

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
				$('.team .team-votes-menealo a').text("{% trans _("menéalo") %}");
				target.parents('.team').find('.team-votes-menealo a').text("{% trans _("presionado") %}");
			}
		});
	});
});
</script>
{% endspacefull %}

<!--Facebook stuff-->
<div id="fb-root"></div>
<body>

<img class="banner-top" src="{{globals.base_static}}img/nivea_2012/banner_00.png" width="386" height="200" alt="Nivea For Men" title="Logo Nivea For Men" />

<div class="bases">
	<p>Presentación / Enlace a las bases / Información legal</p>
</div><!--class bases-->

<div class="game">

	<h1>{{match.local_name}} // {{match.visitor_name}}</h1>

	<div class="teams"><!--to group 2 teams-->

		 <div class="team team-A">
		<img class="team-A-logo" src="{{globals.base_static}}img/liga_f_ES_2012/{{match.local_short|lower}}_G.png" alt="{{match.local_name}}" title="{{match.local_name}}" widht="150" height="150" />
		<div class="team-votes-box-A">
			<div class="team-votes-count">
				<div class="team-votes-number">{{match.votes_local}}</div>
				<div class="team-votes-meneos">meneos</div>
			</div><!--team-votes-count-->
			{% if current_user.user_id != 0 %}
			<div class="team-votes-button-A"><div class="team-votes-button-B">
				<div class="team-votes-menealo {% if !match.is_votable%}disabled{%endif%} {% if match.vote===1%}team-voted{%endif%}">
					{% if (match.vote == 1) %}
						{% trans _("presionado") %}
					{% else %}
						{% if match.is_votable %}
							{% trans _("menéalo") %}
						{% else %}
							{% trans _("cerrado") %}
						{% endif %}
					{% endif %}
				</div>
			</div></div><!--team-votes-button-* / B with 3D effect-->
			{% endif %}
		</div><!--team-votes-box-A-->
		 </div><!--team-A-->

		<div class="team team-votes-box-C">
			<div class="team-votes-count">
				<div class="team-votes-number">{{match.votes_tied}}</div>
				<div class="team-votes-meneos">meneos</div>
			</div><!--team-votes-count-->
			{% if current_user.user_id != 0 %}
			<div class="team-votes-button-A"><div class="team-votes-button-B">
				<div class="team-votes-menealo {% if !match.is_votable%}disabled{%endif%} {% if match.vote===0%}team-voted{%endif%}">
					{% if (match.vote === 0) %}
						{% trans _("presionado") %}
					{% else %}
						{% if match.is_votable %}
							{% trans _("empate") %}
						{% else %}
							{% trans _("cerrado") %}
						{% endif %}
					{% endif %}
				</div>
			</div></div><!--team-votes-button-* / B with 3D effect-->
			{% endif %}
		</div><!--team-votes-box-C-->

		<div class="team team-B">
	  		<img class="team-B-logo" src="{{globals.base_static}}img/liga_f_ES_2012/{{match.visitor_short|lower}}_G.png" alt="{{match.visitor_name}}" title="{{match.visitor_name}}" widht="150" height="150" />
		<div class="team-votes-box-B">
			<div class="team-votes-count">
				<div class="team-votes-number">{{match.votes_visitor}}</div>
				<div class="team-votes-meneos">meneos</div>
			</div><!--team-votes-count-->
			{% if current_user.user_id != 0 %}
			<div class="team-votes-button-A">
				<div class="team-votes-menealo {% if !match.is_votable%}disabled{%endif%} {% if match.vote===2%}team-voted{%endif%}">
					{% if (match.vote == 2) %}
						{% trans _("presionado") %}
					{% else %}
						{% if match.is_votable %}
							{% trans _("menéalo") %}
						{% else %}
							{% trans _("cerrado") %}
						{% endif %}
					{% endif %}
					</div>
			</div><!--team-votes-button-*-->
			{% endif %}
		</div><!--team-votes-box-B-->
		</div><!--team-B-->
		

	</div><!--teams-->

	{% include "league/voters.tpl" %}

	<div class="game-voters-pages">
		{% exec do_pages match.total_votes, match.limit_votes as foo %}
	</div><!--game-voters-pages-->

	<div class="game-footer">
		<p>Encuentro el {{match.ts_date|date:"d-m-Y"}} a las {{match.ts_date|date:"H:i"}}. 
		Puedes votar hasta el {{match.ts_vote_until|date:"d-m-Y"}} a las {{match.ts_vote_until|date:"H:i"}}</p>
	</div><!--game-footer-->

</div><!--game-->

<div class="pages-ad">
    {% exec do_pages_reverse_tpl league.total, league.current, "league/pagination.tpl" as foo %}
</div>

</body>
</html>

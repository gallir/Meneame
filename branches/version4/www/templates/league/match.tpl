<div id="match-{{match.id}}" class="game">

	<h1><a class="match-title" href="?match={{match.id}}">{{match.local_name}} - {{match.visitor_name}}</a></h1>
	{% if match.score_local %}
		<h2>Resultado: {{match.score_local}} - {{match.score_visitor}}</h2>
	{% endif %}

	<div class="teams"><!--to group 2 teams-->
		<div class="team team-A">
		<img class="team-A-logo" src="{{globals.base_static}}img/liga_f_ES_2012/{{match.local_short|lower}}_G.png" alt="{{match.local_name}}" title="{{match.local_name}}" widht="150" height="150" />
		<div class="team-votes-box-A">
			<div class="team-votes-count">
				<div class="team-votes-number">{{match.votes_local}}</div>
				<div class="team-votes-meneos">meneos</div>
			</div><!--team-votes-count-->
			{% if current_user.user_id != 0 %}
			<div class="team-votes-button-A"><div class="{% if match.vote!==1%}team-votes-button-B{%endif%}">
				<div class="team-votes-menealo {% if !match.is_votable%}disabled{%endif%} {% if match.vote===1%}team-voted{%endif%}">
					{% if (match.vote == 1) %}
						{% trans _("¡votado!") %}
					{% else %}
						{% if match.is_votable %}
							{% trans _("gana") %}
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
			<div class="team-votes-button-A"><div class="{% if match.vote!==0%}team-votes-button-B{%endif%}">
				<div class="team-votes-menealo {% if !match.is_votable%}disabled{%endif%}">
					{% if (match.vote === 0) %}
						{% trans _("¡votado!") %}
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
			<div class="team-votes-button-A"><div class="{% if match.vote!==2%}team-votes-button-B{%endif%}">
				<div class="team-votes-menealo {% if !match.is_votable%}disabled{%endif%} {% if match.vote===2%}team-voted{%endif%}">
					{% if (match.vote == 2) %}
						{% trans _("¡votado!") %}
					{% else %}
						{% if match.is_votable %}
							{% trans _("gana") %}
						{% else %}
							{% trans _("cerrado") %}
						{% endif %}
					{% endif %}
					</div>
			</div></div><!--team-votes-button-*-->
			{% endif %}
		</div><!--team-votes-box-B-->
		</div><!--team-B-->
		

	</div><!--teams-->

    {{ match.get_votes_box }}

	<div class="game-footer">
		<p>Encuentro el {{match.ts_date|date:"d-m-Y"}} a las {{match.ts_date|date:"H:i"}}.
        {% if !match.is_played %}
			{% if match.is_votable %}
				Fin de las votaciones: {{match.ts_date_end_votes|date:"d-m-Y"}} a las {{match.ts_date_end_votes|date:"H:i"}}.
			{% else %}
				{% if match.not_votable_before_start %}
					Votos cerrados, el partido está a punto de empezar.
				{% else %}
					Puedes votar a partir del {{match.ts_vote_starts|date:"d-m-Y"}} a las {{match.ts_vote_starts|date:"H:i"}}
				{% endif %}
			{% endif %}
		{% else %}
			Finalizado.
        {% endif %}
        </p>
	</div><!--game-footer-->

</div><!--game-->

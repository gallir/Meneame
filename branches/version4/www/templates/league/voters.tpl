<div class="game-voters-list">
	{% for vote in match.votes %}
		<div class="item">
			<a title="{{vote.name}} {{vote.date}} valor: {{values[vote.value]}}" href="{{globals.base_url}}user/{{vote.user_login}}">
				<img class="avatar" widht="20" heigh="20" alt="" src="{% exec get_avatar_url vote.user_id vote.avatar 20 %}" />
			    {{ vote.name }}
			</a>
		</div>
	{% empty %}
		{% trans  _("Nadie ha votado!") %}
	{% endfor %}
</div>

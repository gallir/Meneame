{% set base_url = globals.league_url %}
<div class="pages margin">
	{% if total < 0 || current < total %}	
		{% set i = current+1 %}
		<span class="nextprev">
			<a href="{{base_url}}{{i}}" {% if i > 10 %} rel="nofollow"{% endif %}>
				&#171; {% trans _('siguiente') %}
			</a></span>
	{% else %}
		<span class="nextprev">&#171; {% trans _('siguiente') %}</span>
	{% endif %}

    {% if total > 0 %}
        {% for i in pages %}
            {% if i == current %}
				<span class="current">{{i}}</span>
            {% else %}
				<a href="{{base_url}}{{i}}" title="{%trans _('ir a página') %}{{i}}">{{i}}</a>
            {% endif %}
        {% endfor %}
        {% if start > 1 %}
			<span>...</span>
			<a href="{{base_url}}1" title="{% trans _('ir a página') %} 1">1</a>
        {% endif %}
    {% endif %}

	{% if current == 1 %}
		<span class="nextprev">{% trans _('anterior') %} &#187;</span>
	{% else %}
		{% set i = current - 1 %}
		<span class="nextprev">
			<a href="{{base_url}}{{i}}" {% if i > 10 %} rel="nofollow"{% endif %}>{% trans _('anterior') %} &#187;</a>
		</span>
	{% endif %}
</div>

{% extends "league/form.tpl" %}

{% block fields %}
{% if data.id %}
<input type="hidden" name="team_id" value="{{ data.id }}" />
{% endif %}
<p>
  <label>{% trans _('Siglas') %}:</label><br/>
  <input type="text" name="shortname" id="shortname" value="{{ data.shortname|default:""|escape }}" />
</p>
<p>
  <label>{% trans _('nombre') %}:</label><br/>
  <input type="text" name="name" id="name" value="{{ data.name|default:""|escape }}" />
</p>
{% endblock %}

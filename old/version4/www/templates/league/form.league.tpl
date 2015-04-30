{% extends "league/form.tpl" %}

{% block fields %}
{% if data.id %}
<input type="hidden" name="match_id" value="{{ data.id }}" />
{% endif %}
<p>
  <label>{% trans _('nombre de la liga') %}:</label><br/>
  <input type="text" name="name" id="name" value="{{ data.name|default:""|escape }}" />
</p>
{% endblock %}

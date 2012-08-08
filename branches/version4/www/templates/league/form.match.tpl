{% extends "league/form.tpl" %}

{% block fields %}
{% if data.id %}
<input type="hidden" name="match_id" value="{{ data.id }}" />
{% endif %}
<p>
  <label>{% trans _('Liga') %}:</label><br/>
  <select name="league">
    {% for league in data.leagues %}
    <option value="{{league.id}}"{% if data.league == league.id%} selected{%endif%}>{{league.name}}</option>
    {% endfor %}
  </select>
</p>
<p>
  <label>{% trans _('Local') %}:</label><br/>
  <select name="local">
    {% for team in data.teams %}
    <option value="{{team.id}}"{% if data.local == team.id%} selected{%endif%}>{{team.name}}</option>
    {% endfor %}
  </select>
</p>
<p>
  <label>{% trans _('Visitante') %}:</label><br/>
  <select name="visitor">
    {% for team in data.teams %}
    <option value="{{team.id}}"{% if data.visitor == team.id%} selected{%endif%}>{{team.name}}</option>
    {% endfor %}
  </select>
</p>
<p>
  <label>{% trans _('Hora') %}:</label><br/>
  <input type="text" class="date" name="date" value="{{ data.date|default:""|escape }}" />
</p>
<p>
  <label>{% trans _('Fecha limite') %}:</label><br/>
  <input type="text" class="date" name="vote_until" value="{{ data.vote_until|default:""|escape }}" />
</p>
{% endblock %}

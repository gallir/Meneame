{% extends "assert_templates/nested_block.tpl" %}

{% block outer %}
{{ block.super }}
new stuff
{% block inner2 %}
new inner2
{% endblock inner2 %}
{% endblock outer %}


{% extends "assert_templates/nested_block.tpl" %}

{% block outer %}
{{ block.super }}
new stuff
{% endblock outer %}

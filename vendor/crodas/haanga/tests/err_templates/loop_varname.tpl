{% extends "assert_templates/base.tpl" %}

{% block foo %}
    {% for i in block.super %}
    {% endfor %}
{% endblock %}

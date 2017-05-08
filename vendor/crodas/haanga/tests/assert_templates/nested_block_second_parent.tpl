{% extends "assert_templates/nested_block.tpl" %}

{% block inner2 %}
    inner2's new value
    {% block inner2_1 %}
        2.1
    {% endblock %}
{% endblock %}

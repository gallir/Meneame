{% extends "assert_templates/base.tpl" %}

{% block title %}My Title - {{ block.super }}{% endblock %}

{% block main.menu %}
    {{ block.super}}

    :-)
{% endblock %}

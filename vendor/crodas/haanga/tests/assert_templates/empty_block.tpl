{% extends "assert_templates/empty_block_base.tpl"%}


{% block outer %}
{% block inner1 %}
this is inner1
{% endblock inner1 %}
{% block inner2 %}
this is inner2
{% endblock inner2 %}
{% endblock outer %}

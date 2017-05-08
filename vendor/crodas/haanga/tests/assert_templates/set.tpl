{% set foo = 5+1 %}
{% set bar = 'testing' %}
{{ foo }}
{% include "assert_templates/sub_set.tpl" %}

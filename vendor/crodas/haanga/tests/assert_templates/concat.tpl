{% set bar = "bar" %}
{% set foo = "foo" . "bar" . bar %}
{{ foo }}

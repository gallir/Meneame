{{ variable }}
{{ variable|escape }}
{ {{ variable|safe }} }
{% autoescape off %}
    {{ variable }}
    {% autoescape on %}
        {{ variable }}
    {% endautoescape %}
{% endautoescape %}
{{ variable }}

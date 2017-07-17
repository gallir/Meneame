{% for w in text|explode %}
    1: {{ w }}
{% endfor %}
{% for w in text|explode:"," %}
    w: {{ w }}
{% endfor %}

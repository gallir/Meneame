{% for k,val in obj %}
    {% if val|null %}
        {{ k }} is null
    {% else %}
        {{ k }} is not null
    {% endif %}
{% endfor %}

{% for a in obj.endpoint %}
    {{ base.endpoint }}

    {% if forloop.last %}
        test
    {% endif %}
{% endfor %}

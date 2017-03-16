{% for i in array %}
    {{ forloop.revcounter0 }}
{% endfor %}

{% for i in array %}
    {{ forloop.revcounter }}
{% endfor %}

{% for i in array %}{% filter trim %}
    {% if forloop.last %} Last {{ i }}{% endif %}

{% endfilter %}{% endfor %}

{% for k,sub in array_nested %}{% filter trim %}

    {% for arr in sub %}
        {% for val in arr %}
            {% if forloop.parentloop.parentloop.last %} Last {{ k }}{% endif %}
        {% endfor %}
    {% endfor %}

{% endfilter %}{% endfor %}

{% for k,sub in array_nested %}{% filter trim %}

    {% for arr in sub %}
        {% for val in arr %}
            {% if forloop.parentloop.parentloop.first %} first {{ k }}{% endif %}
        {% endfor %}
    {% endfor %}

{% endfilter %}{% endfor %}

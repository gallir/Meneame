{% for prop,value in obj %}
    {{ prop }} {{ value }}
{% endfor %}

{% for i in objects %}
    {{ i.foo }}
{% endfor %}

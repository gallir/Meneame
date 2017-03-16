{% for id,user in users %}
    {{ forloop.last }}
    {{ forloop.counter0 }}
    Inside loop
{% empty %}
    Else
{% endfor %}

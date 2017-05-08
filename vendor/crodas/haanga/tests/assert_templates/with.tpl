{% for user in users %}
    {% with user.name as name %}
        {{name|upper}} == {{user.name|upper}}
    {% endwith %}
{% endfor %}

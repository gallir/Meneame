{% for user in array %}
    {% cycle 'uno' 'dos' 'tres' %}
{% endfor %}
-----------------------------------------------
{% cycle 'uno' 'dos' 'tres' as foo %}
{% cycle foo %}
{% cycle foo %}
{% cycle foo %}
{% cycle foo %}

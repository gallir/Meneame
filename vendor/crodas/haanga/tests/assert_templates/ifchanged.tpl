{% dictsort users regroup_field as sorted_users %}
{% for user in sorted_users %}
    {% ifchanged %}Users with {{user['age'] }} years{% endifchanged %}
    {{ user['name'] }}
{% endfor %}

{% for user in sorted_users %}
    {% ifchanged user['age'] user['foo'] %}Users with {{user['age']}} years{% else %}continue{% endifchanged %}
    {{ user['name'] }}
{% endfor %}

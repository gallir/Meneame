{# Test regroup with filters, and without filters #}
{% regroup users|dictsort:regroup_by by age as sorted_users %}
{% dictsort users regroup_by as t_users %}
{% regroup t_users by age as sorted_users1 %}

{% if sorted_users != sorted_users1 %}
    Error
{% endif %}

{% for user in sorted_users %}
    {{user['grouper'] }}
    {% for u in user['list'] %}
        {{forloop.counter}}-{{forloop.revcounter}}-{{forloop.revcounter0}} ({{forloop.parentloop.counter}}). {{ u['name']|capfirst }} ({% if forloop.first %}first{% else %}{% if forloop.last %}last{% endif %}{% endif %})
    {% endfor %}
{% endfor %}

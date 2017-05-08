{% load "../../contrib/meneame_pagination.php" %}
{% meneame_pagination page results_per_page total %}
{% if mnm_prev %}
    <span class="nextprev">&#171; Previous</span>
{% else %}
    <a href="?page{{ mnm_prev }}">&#171; Previous</a>
{% endif %}

{% if mnm_start > 1 %}
    <a href="?page=1">1</a>';
    <span>...</span>
{% endif %}

{% for page in mnm_pages %}
    {% if mnm_current == page %}
        <span class="current">{{page}}</span>
    {% else %}
        <a href="?page={{page}}">{{page}}</a>
    {% endif %}
{% endfor %}

{% if mnm_total > mnm_end %}
    <span>...</span>
    <a href="?page={{ mnm_total}}">{{ mnm_total }}</a>
{% endif %}

{% if mnm_next %}
    <a href="?page={{mnm_next}}">&#187; Next</a>
{% else %}
    <span class="nextprev">&#187; Next</span>
{% endif %}

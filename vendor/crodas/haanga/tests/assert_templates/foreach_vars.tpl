<div class='container'>
{% for category in categoriesArray %}
        <div class='mainCategoryList'>{{ category.cat.categoria }}
                <ul>
                {% for secondaryCategory in category.subCategories %}
                        <li class='secondaryCategoryList'>
                        {% if forloop.last %}
                                {{ secondaryCategory.categoria }}</li>
                        {% else %}
                                {{ secondaryCategory.categoria }},</li>
                        {% endif %}
                {% endfor %}
                </ul>
        </div>
{% endfor %}
</div>

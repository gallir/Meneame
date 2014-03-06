<ul class="tabsub">
    {% for link in links %}
	    <li{% if link.active %} class="tabsub-this"{% endif %}>
            <a href="{{link.url}}">{{link.name}}</a>
        </li>
    {% endfor %}
</ul>

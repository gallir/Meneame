<a href="{{page}}?action=create">{% trans _("Crear") %}</a>
<table class="decorated" style="font-size: 10pt">
<tr>
    {% for name in cols %}
    <th width="25%">{{ name }}</th>
    {% endfor %}
    <th>{% trans _('Editar / Borrar') %}</th>
</tr>
{% for row in rows %}
<tr>
    {% for i, name in cols %}
        <td>{{row[i]}}</td>
    {% endfor %}
    <td>
        <a href="{{page}}?action=update&id={{row.id}}">{%
            trans _("Editar") %}</a>
    </td>
</tr>
{%  endfor %}
</table>

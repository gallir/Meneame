<div class="genericfields" style="margin: 0 50px">
  {% if form.error %}
	<div class="form-error-submit">&nbsp;&nbsp;{{ form.error }}</div>
  {% endif %}
  <form  enctype="multipart/fields-data" action="{{ form.url|default:""}}" method="post" id="thisfields" name="thisfields" AUTOCOMPLETE="off">
    <fieldset>
    <legend>
      <span class="sign">{{form.title}}</span>
    </legend>
    <input type="hidden" name="process" value="1" />
    <input type="hidden" name="form_hash" value="{{ form.hash }}" />
    <input type="hidden" name="form_time" value="{{ form.time }}" />
    {% block fields %} {% endblock %}
    {% block save %}
      <p>
        <input type="submit" name="save_profile" value="{% trans _('actualizar') %}" class="button" />
      </p>
    {% endblock %}
  </fieldset>
  </form>
</div>

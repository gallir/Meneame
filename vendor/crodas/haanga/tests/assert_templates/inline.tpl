{% buffer html %}
Foobar text
{% inline "assert_templates/partial.tpl" %}
{% inline "assert_templates/inline_buffer.tpl" %}
{% endbuffer %}
{{ html }}

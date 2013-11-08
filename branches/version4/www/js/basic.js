var  base_key="{{globals.security_key}}",
    {% if globals.link %}link_id = {{ globals.link.id }},{% endif %}
    user_id = {{ current_user.user_id }},
    user_login = '{{ current_user.user_login }}',
    mobile_client = {{ globals.mobile }};

    {#
    mnm_start_time=(new Date()).getTime());
    #}

var base_url="{{ globals.base_url }}",
	base_static="{{ globals.base_static }}",
	mobile_client = false,
	is_mobile = {{ globals.mobile }},
	touchable = false,
	base_key, link_id = 0, user_id, user_login;


var onDocumentLoad = [];
function addPostCode(code) {
	onDocumentLoad.push(code);
}


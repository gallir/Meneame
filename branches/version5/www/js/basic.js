var base_key="{{globals.security_key}}",
    link_id = {% if globals.link_id %}{{ globals.link_id }}{% else %}0{% endif %},
    user_id={{ current_user.user_id }},
    user_login='{{ current_user.user_login }}';

    {#
    mnm_start_time=(new Date()).getTime());
    #}

var onDocumentLoad = [], postJavascript = [];
function addPostCode(code) {
	onDocumentLoad.push(code);
}


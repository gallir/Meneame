{% exec set_global_template %}
{{ test_global.b }} {{global1.foo.foo.bar}} {{global1.bar.xxx.yyyy|default:"yyy"}}
{{ test_global['b'] }} {{global1['foo']['foo']['bar']}}
{{ global1['foo'][index.name.str]['bar'] }}
{{ global1['foo'][indexstr]['bar'] }}

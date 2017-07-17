<?php
$globals['skip_check_ip_noaccess'] = true;
require_once __DIR__.'/config.php';

echo "IP: " .$globals['user_ip'] . "<br/>";
echo "User-Agent: " .$_SERVER['HTTP_USER_AGENT'] . "<br/>";
echo "Mobile: ";
if ($globals['mobile']) {
    echo "Yes";
} else {
    echo  "No";
}

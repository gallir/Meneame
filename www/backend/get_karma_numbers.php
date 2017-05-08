<?php
require_once __DIR__ . '/../config.php';

if (empty($current_user->user_login)) {
    die(_('usuario no identificado'));
}

if (!empty($_GET['id']) && $current_user->user_level === 'god') {
    $user = new User(intval($_GET['id']));
} else {
    $user = new User($current_user->user_id);
}

if (!$user->karma_log) {
    die(_('no hay registros para este usuario'));
}

echo '<div style="text-align: left">';
echo '<strong>'._('última modificación').':</strong> '.get_date_time($user->karma_calculated);
echo '<ul>';

foreach (preg_split("/\n/", $user->karma_log) as $line) {
    if ($line = trim($line)) {
        echo "<li>$line</li>\n";
    }
}

echo '</ul>';
echo '</div>';

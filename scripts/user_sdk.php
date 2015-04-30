#! /usr/bin/env php
<?php
include(dirname(__FILE__).'/../www/config.php');

if (count($argv) != 4) {
	print_usage();
}

$sdk = (float) $argv[2];
if ($sdk <= 0 || $sdk > 20) print_usage("karma should be > 0 and < 20");

$mess = $db->escape($argv[3]);
if (empty($mess) || mb_strlen($mess) < 6 ) print_usage("message is empty or too short");


$user = new User();
$user->username = $argv[1];
if (! $user->read()) print_usage('User not found');

echo "karma: $user->karma ";
$user->add_karma(-$sdk, $mess);
$user->read();
echo "-> $user->karma\n";





function print_usage($mess = false) {
	if ($mess) {
		echo "Error: $mess\n";
	}
	echo 'Usage: '.basename(__FILE__).' username karma_to_discount message'."\n";
	die;
}


#! /usr/bin/perl

$step=shift;

use Time::Local;
$initial = timelocal(0, 0, 0, 7, 11, 105);
$start = $initial + 86400*$step;
$end = $start + 86400*30;

($seconds, $minutes, $hours, $day_of_month, $month, $year, $wday, $yday, $isdst) = localtime($start);
$strstart = sprintf("%02d-%02d-%04d", $day_of_month, $month+1, $year+1900);

($seconds, $minutes, $hours, $day_of_month, $month, $year, $wday, $yday, $isdst) = localtime($end);
$strend = sprintf("%02d-%02d-%04d", $day_of_month, $month+1, $year+1900);

print "$strstart $strend\n"

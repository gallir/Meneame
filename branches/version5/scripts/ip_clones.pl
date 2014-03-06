#! /usr/bin/perl
#

use DBI;
use FindBin qw($RealBin);
use lib "$RealBin";

require "dbconf.pl";
my $dbh = DBI->connect ("DBI:mysql:$DBNAME;host=$DBSLAVE", $DBUSER, $DBPASS);
$dbh->do("set character set utf8");
$dbh->do("set names utf8");


my $ip = shift;

my $counter = 0;

$ip =~ s/([0-9]\.[0-9]\.[0-9])\.[0]*$/$1/;
my @integers = split /\./, $ip;

if (scalar(@integers) < 2 || scalar(@integers) > 4) {
	print "Usage: ip_clones.pl s.x.[y[.z]]\n";
	exit;
}

if (scalar(@integers) <= 3) {
	if (scalar(@integers) == 3) {
		$cminor = "$ip.0";
		$cmajor = "$ip.255";
	} else {
		$cminor = "$ip.0.0";
		$cmajor = "$ip.255.255";
	}
	$sql = "select distinct user_login, user_email, inet_ntoa(vote_ip_int), user_level from votes, users where user_id=vote_user_id and vote_type in ('links','comments') and vote_ip_int between inet_aton('$cminor') and inet_aton('$cmajor');";
} else {
	$sql = "select distinct user_login, user_email, inet_ntoa(vote_ip_int), user_level from votes, users where user_id=vote_user_id and vote_type in ('links','comments') and vote_ip_int = inet_aton('$ip');";
}

my $sth = $dbh->prepare($sql);

$sth->execute();
while (($login, $email, $vote_ip, $level) = $sth->fetchrow_array ) {
	print "$login\t$email\t$vote_ip\t$level\n";
	$counter++;
}

$dbh->disconnect();

if ($counter) {
	exit(0);
}

exit (1);


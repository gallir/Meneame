#! /usr/bin/perl
#

use DBI;

my $ip = shift;

$ip =~ s/([0-9]\.[0-9]\.[0-9])\.[0]*$/$1/;
my @integers = split /\./, $ip;

if (scalar(@integers) < 3 || scalar(@integers) > 4) {
	print "Usage: ip_clones.pl s.x.y[.z]\n";
	exit;
}

if (scalar(@integers) == 3) {
	$sql = "select distinct user_login, user_email, inet_ntoa(vote_ip_int) from votes, users where user_id=vote_user_id and vote_type in ('links','comments') and vote_date > date_sub(now(), interval 30 day) and vote_ip_int between inet_aton('$ip.0') and inet_aton('$ip.255');";
} else {
	$sql = "select distinct user_login, user_email, inet_ntoa(vote_ip_int) from votes, users where user_id=vote_user_id and vote_type in ('links','comments') and vote_date > date_sub(now(), interval 30 day) and vote_ip_int = inet_aton('$ip');";
}

my $dbh = DBI->connect ('DBI:mysql:meneame', 'meneame', '');

my $sth = $dbh->prepare($sql);

$sth->execute();
while (($login, $email, $vote_ip) = $sth->fetchrow_array ) {
	print "$login\t$email\t$vote_ip\n";
}

$dbh->disconnect();


#! /usr/bin/perl
# Example perl program that detect clones by IP in vote and comments
# and stores them in the clones table.
#
# USAGE: ./clones-example.pl N
#
# where N is the period in hour to analyze
#
# Recommended to put ti on the cron tab to execute earch hour as shown below
# 17 * * * * /path_to_script/clones-example.pl 1

use DBI;

my $hours = int(shift); 

if ($hours <= 0) { $hours = 1 }


my $dbh = DBI->connect ('DBI:mysql:meneame', 'meneame', '');
$dbh->do("set character set utf8");
$dbh->do("set names utf8");

$counter = 0;
print STDERR "Getting users and ips...\n";


#IPs from votes
$sql = "select distinct vote_user_id, vote_ip_int from votes where vote_type in ('links', 'comments', 'posts') and vote_user_id != 0 and vote_date > date_sub(now(), interval $hours hour)";
$sth = $dbh->prepare($sql);
$sth->execute();
while (($user_id, $ip_int) = $sth->fetchrow_array ) {
	if ($ip_int > 0) {
		add($user_id, $ip_int);
	}
	$counter++;
}

#IPs from comments
$sql = "select distinct comment_user_id, inet_aton(comment_ip) from comments where comment_date > date_sub(now(), interval $hours hour)";
$sth = $dbh->prepare($sql);
$sth->execute();
while (($user_id, $ip_int) = $sth->fetchrow_array ) {
	if ($ip_int > 0) {
		add($user_id, $ip_int);
	}
	$counter++;
}

print STDERR "got $counter\n";

if ($counter <= 0) { exit };

$search_from = int((15*24 + $hours)/24);
while ( ($u, $ips) =  each %activity) {
	$ips =~ s/[, ]+$//;
	$sql = "select distinct vote_user_id, vote_ip_int from votes where vote_ip_int in ($ips) and vote_user_id != $u and vote_user_id > 0 and vote_date > date_sub(now(), interval $search_from day)";
	# $sql = "select distinct vote_user_id, vote_ip_int from votes where vote_ip_int in ($ips) and vote_user_id != $u and vote_user_id > 0";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	while (($u1, $ip1) = $sth->fetchrow_array ) {
		$ip_a = num2ip($ip1);
		print "$u1 $ip1 $ip_a\n";
		$sql_insert = "REPLACE INTO clones (clon_from, clon_to, clon_ip) VALUES ($u, $u1, '$ip_a')";
		$dbh->do($sql_insert);
		$sql_insert = "INSERT IGNORE INTO clones (clon_to, clon_from, clon_ip) VALUES ($u, $u1, '$ip_a')";
		$dbh->do($sql_insert);
	}
}

$dbh->disconnect();


sub add {
	$u = shift;
	$ip = shift;
	if ( $activity{$u} !~ /$ip/ ) {
		#print "Adding $u: $ip " . num2ip($ip) . " \n";
		$activity{$u} .= "$ip,";
	}
}

sub ip2num { 
  return(unpack("N",pack("C4",split(/\./,$_[0]))));
}

sub num2ip {
    return(join(".",unpack("C4",pack("N",$_[0]))));
}  

#! /usr/bin/perl
#

use strict;
use DBI;

my %users;
my %user_votes;
my %user_links;
my %user_done;
my %shared_links;
my %voted_links;
my %count_links;

my $dbh = DBI->connect ('DBI:mysql:meneame', 'meneame', '');

my $from = time() - 3600*24*30; # Last month

# Count total of votes for evey user

my $sql = "select user_id, count(*) as votes from users, votes where user_id = vote_user_id and vote_date > FROM_UNIXTIME($from) group by user_id";
my $sth = $dbh->prepare($sql);

my $userid;
my $votes;
my $key;

$sth->execute();
while (($userid, $votes) = $sth->fetchrow_array ) {
	$users{$userid}=1;
	if (! defined($user_votes{$userid})) {
		$user_votes{$userid} = $votes;
		#print "$userid, $votes \n";
	}
}
my $sql = "select user_id, count(*) as links from users, links where user_id = link_author group by user_id";
my $sth = $dbh->prepare($sql);
$sth->execute();
my $links_user;
while (($userid, $links_user) = $sth->fetchrow_array ) {
		$user_links{$userid} = $links_user;
		$users{$userid}=1;
}


&link_votes;
&store;

$dbh->disconnect();





sub store {
	my  ($userid, $userid_b);
	my $key;
	
	print "Phase 3\n";
	$dbh->do("delete from friends where friend_type='affiliate'");
	foreach $key (keys %shared_links) {
		($userid, $userid_b) = split ('-', $key);
#		print "$shared_links{$key}\n";
		if ($shared_links{$key} > 0) {
			$dbh->do("insert into friends (friend_type, friend_from, friend_to, friend_value ) values ('affiliate', $userid, $userid_b, $shared_links{$key})");
		}
	}
}



sub link_votes {
	# Each user loop
	# for  links sent by every author
	print "Phase 2\n";
	my $userid_b;
	my $linkid;
	my $value;

	foreach $userid (keys %users) {
#		print "$userid, $user_votes{$userid}: ";
		# Each voted link
		$sql = "select link_author, vote_value from votes, links where vote_user_id = $userid and vote_date > FROM_UNIXTIME($from) and link_id = vote_link_id";
		$sth = $dbh->prepare($sql);
		$sth->execute();
		while( ($userid_b, $value) = $sth->fetchrow_array ) {
#			print "2: $userid_b, $value\n";
			if ($value < 0) { $value = -2} else {$value = 1};
			$voted_links{"$userid-$userid_b"} += $value;
			$count_links{"$userid-$userid_b"}++;
		}
	#	print "\n----------------\n";
	}
	
	foreach $key (keys %voted_links) {
		($userid, $userid_b) = split ('-', $key);
		next if $userid == $userid_b;
		#if ($user_links{$userid_b} < 2) { next; }
		#my $scale = 100*$voted_links{$key}/$user_votes{$userid};
		$shared_links{$key} += $voted_links{$key}/$user_links{$userid_b} ;
		print "$key: $shared_links{$key}\n";
	}
}

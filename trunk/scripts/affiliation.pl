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

my $from = time() - 3600*24*60; # Last 2 months

# Count total of votes for evey user

my $sql = "select SQL_NO_CACHE user_id, count(*) as votes from users, votes where vote_type='links' and user_id = vote_user_id and vote_date > FROM_UNIXTIME($from) group by user_id";
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
my $sql = "select SQL_NO_CACHE user_id, count(*) as links from users, links where user_id = link_author group by user_id";
my $sth = $dbh->prepare($sql);
$sth->execute();
my $links_user;
while (($userid, $links_user) = $sth->fetchrow_array ) {
		$user_links{$userid} = $links_user;
		$users{$userid}=1;
}


#&crossed_votes;
&link_votes;
&store;

$dbh->disconnect();





sub store {
	my  ($userid, $userid_b);
	my $key;
	my (%user_total, %user_total_n, $average);
	
	print "Phase 3\n";
	$dbh->do("delete from friends where friend_type='affiliate'");
	foreach $key (keys %shared_links) {
		($userid, $userid_b) = split ('-', $key);
#		print "$shared_links{$key}\n";
		if ($shared_links{$key} > 0) {
			$user_total{$userid} += $shared_links{$key};
			$user_total_n{$userid}++;
			$dbh->do("insert delayed into friends (friend_type, friend_from, friend_to, friend_value ) values ('affiliate', $userid, $userid_b, $shared_links{$key})");
		}
	}
	foreach $userid (keys %user_total) {
		$average = $user_total{$userid}/$user_total_n{$userid};
		print "Average $userid: $average\n";
		$dbh->do("insert delayed into friends (friend_type, friend_from, friend_to, friend_value ) values ('affiliate', $userid, 0, $average)");
	}
}




sub crossed_votes {
	# Each user loop
	# for crossed votes. 
	my $userid_b;
	my $key;
	my $scale;
	my $linkid;
	my $value;
	my $value_b;

	print "Phase 1\n";
	my $userid_b;
	foreach $userid (keys %users) {
	
	#	print "$userid, $user_votes{$userid}: ";
		# Each voted link
		$sql = "select SQL_NO_CACHE vote_link_id, vote_value from votes where vote_user_id=$userid and vote_date > FROM_UNIXTIME($from)";
		$sth = $dbh->prepare($sql);
		$sth->execute();
		while( ($linkid, $value) = $sth->fetchrow_array ) {
			if ($value < 0) { $value = -1} else {$value = 1};
	
			# Select each user that voted to the same link
			$sql = "select distinct SQL_NO_CACHE vote_user_id, vote_value from votes where vote_type='links' and vote_link_id = $linkid and vote_user_id > 0 and vote_user_id != $userid and vote_date > FROM_UNIXTIME($from)";
			my $sth1 = $dbh->prepare($sql);
			$sth1->execute();
			while( ($userid_b, $value_b) = $sth1->fetchrow_array ) {
				if ($user_votes{$userid_b} < 5 || $user_votes{$userid} < 5 ) {
					next;
				}
				if ($value_b < 0) { $value_b = -1} else {$value_b = 1};
	#			print "$linkid-$userid_b ";
				$shared_links{"$userid-$userid_b"} += $value * $value_b;
			}
		}
	#	print "\n----------------\n";
	}
	
	foreach $key (keys %shared_links) {
		($userid, $userid_b) = split ('-', $key);
		if ($user_votes{$userid_b} < 5 || $user_votes{$userid} < 5 ) {
			next;
		}
		$scale = $user_votes{$userid} > $user_votes{$userid_b} ? $user_votes{$userid} : $user_votes{$userid_b};
		print "$key => $shared_links{$key} ($user_votes{$userid}, $user_votes{$userid_b})   ";
		$shared_links{$key} = $shared_links{$key}/$scale;
		print "$shared_links{$key} ($scale)\n";
	}
}
	

sub link_votes {
	# Each user loop
	# for  links sent by every author
	print "Phase 2\n";
	my $userid_b;
	my $linkid;
	my $value;
	my $ignored;

	# Each voted link
	$sql = "select SQL_NO_CACHE vote_user_id, link_author, vote_value from votes, links where vote_type='links' and vote_date > FROM_UNIXTIME($from) and vote_user_id > 0 and link_id = vote_link_id";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	while( ($userid, $userid_b, $value) = $sth->fetchrow_array ) {
		if ($value < 0) { $value = -1} else {$value = 1};
		$voted_links{"$userid-$userid_b"} += $value;
		$count_links{"$userid-$userid_b"}++;
#		print qq{2: $userid-$userid_b: $value ($user_votes{$userid}, $voted_links{"$userid-$userid_b"}, $count_links{"$userid-$userid_b"})\n};
	}
	
	foreach $key (keys %voted_links) {
		($userid, $userid_b) = split ('-', $key);
		next if $userid == $userid_b;
		if ($user_votes{$userid} < 10 ) {
			$ignored++;
			print "Ignoring user $userid (" . $user_votes{$userid} . ") $ignored\n";
			next;
		}
		#if ($user_links{$userid_b} < 2) { next; }
		#my $scale = 100*$voted_links{$key}/$user_votes{$userid};
		#$shared_links{$key} += (2*$voted_links{$key}-$user_links{$userid_b})/$user_votes{$userid}; #$user_links{$userid_b};
		$shared_links{$key} += $voted_links{$key}/$user_votes{$userid}; #$user_links{$userid_b};
		#print "$key: $shared_links{$key}\n";
	}
}

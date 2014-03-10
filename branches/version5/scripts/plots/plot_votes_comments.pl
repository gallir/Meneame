#! /usr/bin/perl
#

use DBI;


my $year = shift;
my $month = shift;

my $dbh = DBI->connect ('DBI:mysql:meneame', 'meneame', '');


# comments per day
$sql = "select day(comment_date) as day, count(*) from comments where year(comment_date) = $year and month(comment_date) = $month group by day;";
$sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$comments{$day}=$count;
}

# link votes
$sql = "select day(vote_date) as day, count(*) from votes where vote_type='links' and year(vote_date) = $year and month(vote_date) = $month group by day";
$sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$vlinks{$day}=$count;
}

# comment votes
$sql = "select day(vote_date) as day, count(*) from votes where vote_type='comments' and year(vote_date) = $year and month(vote_date) = $month group by day";
$sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$vcomments{$day}=$count;
}


# total users
foreach $day (sort {$a <=> $b} keys %vlinks) {
	$sql="select count(*) from users where user_date <=  '$year-$month-$day 23:59:59'";
	$sth = $dbh->prepare($sql);
	$sth->execute();
	while (($count) = $sth->fetchrow_array ) {
		$users{$day}=$count;
	}
}

$dbh->disconnect();

print "#date          comments   link_votes  comment_votes users\n";
foreach $day (sort {$a <=> $b} keys %vlinks) {
	printf("%02d-%02d-%04d\t%6d\t%6d\t%6d\t%6d\n", $day, $month,$year, $comments{$day}, $vlinks{$day}, $vcomments{$day}, $users{$day});
}


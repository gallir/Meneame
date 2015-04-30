#! /usr/bin/perl
#

use DBI;


my $year = shift;
my $month = shift;

my $dbh = DBI->connect ('DBI:mysql:meneame', 'meneame', '');


# sent per day
$sql = "select day(link_date) as day, count(*) from links where year(link_date) = $year and month(link_date) = $month group by day;";
my $sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$sent{$day}=$count;
}

# published per day
$sql = "select day(link_published_date) as day, count(*) from links where year(link_published_date) = $year and month(link_published_date) = $month and link_status='published' group by day;";
my $sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$published{$day}=$count;
}

# discarded per day
$sql = "select day(link_date) as day, count(*) from links where year(link_date) = $year and month(link_date) = $month and link_status='discard' group by day;";
my $sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$discard{$day}=$count;
}


#average published karma
$sql = "select day(link_published_date) as day, avg(link_karma) from links where year(link_published_date) = $year and month(link_published_date) = $month and link_status='published' group by day;";
my $sth = $dbh->prepare($sql);
$sth->execute();
while (($day, $count) = $sth->fetchrow_array ) {
	$karma{$day}=$count;
}

$dbh->disconnect();

print "#date          links  discarded  published average(karma)\n";
foreach $day (sort {$a <=> $b} keys %sent) {
	printf("%02d-%02d-%04d\t%6d\t%6d\t%6d\t%6d\n", $day, $month,$year, $sent{$day}, $discard{$day}, $published{$day}, $karma{$day});
}


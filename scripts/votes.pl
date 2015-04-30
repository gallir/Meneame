#! /usr/bin/perl
#

use DBI;

use strict;
use warnings;

my $id = shift;

$id =~ s/[^0-9]//g;

my $sql = "select user_login, vote_date, vote_value from users, votes where vote_type='links' and vote_link_id = $id and user_id=vote_user_id order by vote_date asc";


my $dbh = DBI->connect ('DBI:mysql:meneame', 'meneame', '');

my $sth = $dbh->prepare($sql);

$sth->execute();
while (($login, $date, $value) = $sth->fetchrow_array ) {
	printf "%20s\t%20s\t%3d\n", $login, $date, $value;
}

$dbh->disconnect();


# The source code packaged with this file is Free Software, Copyright (C) 2005 by
# Ricardo Galli <gallir at uib dot es>.
# http://meneame.net/
# It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
# You can get copies of the licenses here:
#      http://www.affero.org/oagpl.html
# AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
#

use strict;
package MnmDB;

use DBI;
use Encode;
use HTML::Entities;


our $dbh; 
our $ops = 0; 
our $max_ops = 1000000;

our ($dsn, $dbuser, $dbpassword);


sub reconnect {
	if($ops > 0 ) {
		$dbh->disconnect;
		undef $dbh;
		print "* Re-connecting to database\n";
	}
	$dbh = DBI->connect($dsn, $dbuser, $dbpassword);
	$dbh->do("set character set utf8");
	$dbh->do("set names utf8");
	$ops = 0;
}

sub init {
	my %conf = @_;
	$dsn = "DBI:mysql:database=$conf{dbname};host=$conf{dbhost}";
	$dbuser  = $conf{dbuser};
	$dbpassword  = $conf{dbpassword};
	reconnect;
}

sub prepare {
	reconnect if $ops > $max_ops;
	$ops++;
	return $dbh->prepare(@_);
}

sub do {
	reconnect if $ops > $max_ops;
	$ops++;
	return $dbh->do(@_);
}

sub row_array {
	reconnect if $ops > $max_ops;
	$ops++;
	return $dbh->selectrow_arrayref(@_);
}

sub row_hash {
	reconnect if $ops > $max_ops;
	$ops++;
	return $dbh->selectrow_hashref(@_);
}

sub last_insert_id {
	return $dbh->{'mysql_insertid'};
}

sub disconnect {
	$dbh->disconnect(@_);
}

sub escape {
	my $str = $dbh->quote(shift);
	return $str;
}

sub utf8 {
	if (! utf8::is_utf8($_[0])) {
		utf8::decode($_[0]);
	}
	return $_[0];
}

# Used to get the text content for stories and comments
sub clean_text {
	$_ = shift;
	chomp;
	$_ = decode_entities($_);
	# Replace two "-" by a single longer one, to avoid problems with xhtml comments
	s/--/–/;
	return encode_entities($_);
}

sub read_configuration {
	my $file = shift;
	my $hash = shift;
	open (CONFIG, $file) || return undef;
	while (<CONFIG>) {
		chomp;                  # no newline
		s/#.*//;                # no comments
		s/^\s+//;               # no leading white
		s/\s+$//;               # no trailing white
		next unless length;     # anything left?
		my ($var, $value) = split(/\s*=\s*/, $_, 2);
		$hash->{$var} = $value;
		#print "Conf: $var->$value\n";
	}
	close(CONFIG);
	return 1;
}


1;
__END__

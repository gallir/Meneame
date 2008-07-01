#! /usr/bin/perl

use utf8;

#use Unicode::String qw(utf8 latin1 utf16);
use Encode;
use LWP::UserAgent;
use strict;

#my $url = 'http://antoli/meneame/backend/post_sms_store.php';
my $url = 'http://meneame.net/backend/post_sms_store.php';

my $number = shift;
my $date = shift;

my $text;

while (<>) {
#	Encode::from_to($_, "iso-8859-1", "utf8");
	$text .= $_;
}

$text = decode("iso-8859-1", $text);
$number =~ s/^\+00//;
$number =~ s/-\d+$//;

print "SMS #$number, Date:$date, Text: $text\n";
$number = encodeUrl($number);
$date = encodeUrl($date);
$text = encodeUrl($text);

my $ua = LWP::UserAgent->new;
$ua->agent("Meneame SMS");
my $req = HTTP::Request->new(POST => $url);
$req->content_type('application/x-www-form-urlencoded');
$req->content("phone=$number&date=$date&text=$text");
my $res = $ua->request($req);

if ($res->is_success && $res->content =~ /^OK/) {
	print "OK: ". $res->content;
	exit 0;
}

print "KO: ".$res->content." (".$res->status_line.")";
print "\n";

exit 1;


sub encodeUrl {
	my $str = shift;
	$str =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;
	return $str;
}

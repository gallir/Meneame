#! /usr/bin/perl
# The source code packaged with this file is Free Software, Copyright (C) 2005 by
# Ricardo Galli <gallir at uib dot es>.
# http://meneame.net/
# It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
# You can get copies of the licenses here:
#      http://www.affero.org/oagpl.html
# AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
#
# MODULES packages:
# libhtml-parser-perl 
# libnet-jabber-perl
# libproc-daemon-perl
# libdbi-perl

use HTML::Entities;
use MnmJabber;
use strict;
use utf8;

my $timestamp;
my $jabber = new MnmJabber;
$jabber->init("posts.conf");
$jabber->setCallBacks(message=>\&InMessage, timeout=>\&ReadPosts);
$jabber->connect();
print "END\n";



sub ReadPosts {
	my ($sql, $sth, $hash);
	my $poster;

	if ($timestamp == 0) {
		$timestamp = time - 60;
	}
	$sql = qq{SELECT user_login, UNIX_TIMESTAMP(post_date), post_content, post_src, post_id from users, posts WHERE post_date > FROM_UNIXTIME($timestamp) and user_id = post_user_id ORDER BY post_date asc limit 50};
	$sth = MnmDB::prepare($sql);
	$sth->execute ||  die "Could not execute SQL statement: $sql";
	while (my ($username, $date, $content, $src, $postid) = $sth->fetchrow_array) {
		$content = MnmDB::utf8($content);
		$content = MnmDB::clean_pseudotags(decode_entities($content));
		$src = 'jabber' if ($src eq 'im');
		#print "Post -> $username: $content\n";
		$poster = new MnmUser(user=>$username);
		$timestamp = $date;
		foreach my $u ($jabber->users()) {
			if ($u->is_friend($poster) || $u == $poster) {
				$jabber->SendMessage($u, "$poster->{user} ($src): $content -- http://meneame.net/notame/$poster->{user}/$postid ");
			}
		}
		#BroadCast($poster->{user}.": $content -- http://meneame.net/notame/".$poster->{user}." ");
	}
}

sub InMessage {
	my $poster = shift;
	my $body = shift;
	my ($sql, $sth, $array);

	my $id = $poster->id;

	if (length($body) < 10) {
		$jabber->SendMessage($poster, "mensaje muy corto");
		return;
	}
	if (length($body) > 300) {
		$jabber->SendMessage($poster, "mensaje demsiado largo (long max: 300)");
		return;
	}

	$sql = qq{SELECT UNIX_TIMESTAMP(post_date) from posts where post_user_id = $id order by post_date desc limit 1};
	my $timestamp=0;
	$array = MnmDB::row_array($sql);
	if ($array) {
		$timestamp = $array->[0];
	}

	my $remaining = int((120 - (time-$timestamp))/60);
	if ($remaining > 0) { # 2 minutes
		$jabber->SendMessage($poster, "ya has enviado una nota hace pocos minutos, debes esperar $remaining minutos");
		return;
	}
	$body = MnmDB::clean_text($body);
	$sth = MnmDB::prepare(qq{INSERT INTO posts (post_user_id, post_src, post_ip_int, post_randkey, post_content) VALUES (?, ?, ?, ?, ?) });
	$sth->execute($poster->id, 'im', 0, int(rand(1000000)), $body);
	my $last_id = MnmDB::last_insert_id;
	$sth = MnmDB::prepare(qq{insert into logs (log_date, log_type, log_ref_id, log_user_id, log_ip) VALUES (FROM_UNIXTIME(?), ?, ?, ?, ?) });
	$sth->execute(time, 'post_new', $last_id, $poster->id, 0);
}


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
use Commands;
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
			next if $u->get_pref('posts-off');
			my $username = $u->{user};
			# Send the note if the user is the poster is a friend, the same user or has answered him with a @username at the begining
			if ($u->friend($poster) > 0 || $u == $poster || $content =~ /(^|\W)\@$username\W/i) {
				$jabber->SendMessage($u, "$poster->{user} ($src): $content -- http://meneame.net/notame/$poster->{user}/$postid ");
			}
		}
	}
}

sub InMessage {
	my $poster = shift;
	my $body = shift;
	my ($sql, $sth, $array);

	my $id = $poster->id;

	if ($poster->karma < 5.5) {
		$jabber->SendMessage($poster, "no tienes suficiente karma");
		return;
	}
	chomp($body);
	if ($body =~ /^ *!/) {
		ExecuteCommand($poster, $body);
		return;
	}


	my $ascii = $body;
	$ascii =~ s/[^a-z]//ig;
	if (length($ascii) < 8) {
		$jabber->SendMessage($poster, "mensaje muy corto o sin caracteres válidos");
		return;
	}
	if (length($body) > 500) {
		$jabber->SendMessage($poster, "mensaje demasiado largo (long max: 500)");
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
	UpdateConversation($id, $last_id, $body);
}

sub ExecuteCommand {
	my $poster = shift;
	$_ = shift;
	my $mess;

	$_ =~ s/^ +//;

	if (/^!help/) {
		$jabber->SendMessage($poster, "»» Comandos:\n!help: esta ayuda\n!off: deshabilita la recepción de todas las notas\n!on: vuelve a habilitar la recepción de las notas \n!whoami: te dice tu nombre de usuario en el menéame\n!who: lista los amigos conectados al jabber de notas (deben ser amigos mutuos)\n!gs http://un.enlace.muy.largo etiqueta: crea enlace corto (la etiqueta es opcional)");
	} elsif (/^!off/) {
		$poster->store_prefs('posts-off', 1);
		$jabber->SendMessage($poster, '»» recepción de mensajes deshabilitados');
	} elsif (/^!on/) {
		$poster->store_prefs('posts-off', '');
		$jabber->SendMessage($poster, '»» recepción de mensajes habilitados');
	} elsif (/^!whoami/) {
		$jabber->SendMessage($poster, "»» " . $poster->{user});
	} elsif (/^!gs/) {
		my @args = split;
		$jabber->SendMessage($poster, "»» " . Commands::fon_gs($args[1], $args[2]));
	} elsif (/^!who/) {
		$mess .= '»» Amigos conectados: ';
		foreach my $u ($jabber->unique_users()) {
			my $username = $u->{user};
			# Send the note if the user is the poster is a friend, the same user or has answered him with a @username at the begining
			if ($u->friend($poster) > 0 && $poster->friend($u) > 0) {
				$mess .= $u->user." ";
			}
		}
		$jabber->SendMessage($poster, $mess);
	}
}

sub UpdateConversation() {
	my $me = shift;
	my $id = shift;
	my $text = shift;
	my @matches;
	my %visited;
	my ($sth, $sql, $foo, $user, $user_id);

	$sql = qq{delete from conversations where conversation_type='post' and conversation_from=$id};
	$sth = MnmDB::prepare($sql);
	$sth->execute;

	if (@matches = $text =~ /(?:^|\s)@([\S\.\-]+[\w])/gm) {
		foreach $user (@matches) {
			next if $visited{$user};
			$visited{$user} = 1;
			$sql = qq{select user_id from users where user_login = '$user'};
			($user_id) = $MnmDB::dbh->selectrow_array($sql);
			next if $me == $user_id;
			$sth = MnmDB::prepare(qq{insert into conversations (conversation_user_to, conversation_type, conversation_time, conversation_from, conversation_to) values (?, ?, now(), ?, 0)});
			$sth->execute($user_id, 'post', $id);
		}
	} 
}

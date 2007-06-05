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

my $event_timestamp;
my $chat_timestamp;
my $counter_timestamp;
my %link_status = qw(link_new Nueva link_publish Publicada link_edit Editada link_discard Descartada);

$chat_timestamp = time;
$event_timestamp = time - 60;


my $jabber = new MnmJabber;
$jabber->init("sneaker.conf");
$jabber->setCallBacks(message=>\&InMessage, timeout=>\&Process);
$jabber->connect();
print "END\n";



sub Process {
	UpdateCounters();
	ReadEvents();
}

sub InMessage {
	my $user = shift;
	my $body = shift;

	return if length($body) < 3;

	if ($body =~ /^ *!/) {
		ExecuteCommand($user, $body)
	} else {
		StoreChat($user, $body);
		ReadEvents();
	}

}

sub UpdateCounters {
	my $now = time;
	my $key;
	my $sth;

	if ($now - $counter_timestamp < 15) {
		return;
	}
	$counter_timestamp = $now;
	#replace into sneakers (sneaker_id, sneaker_time, sneaker_user) values ('$key', $now, $current_user->user_id)"
	foreach my $u ($jabber->users()) {
		$sth = MnmDB::prepare(qq{replace into sneakers (sneaker_id, sneaker_time, sneaker_user) values (?, ?, ?)});
		$key = "jabber/" . scalar($u->id);
		$sth->execute($key, $now, $u->id);
	}
}

sub ReadEvents {
	my ($sql, $sth, $sth2, $log, $link, $hash);
	my $poster;
	my $content;
	my $status;

	########### Link events
	my @time = localtime($event_timestamp);
	my $dbtime = sprintf("%4d%02d%02d%02d%02d%02d", $time[5] + 1900, $time[4]+1, $time[3],     $time[2], $time[1], $time[0]);
	$sql = qq{select UNIX_TIMESTAMP(log_date) as time, log_type, log_ref_id, log_user_id from logs where log_date > '$dbtime' and log_type in ('link_new','link_publish') order by log_date asc limit 10};
	$sth = MnmDB::prepare($sql);
	$sth->execute ||  die "Could not execute SQL statement: $sql";
	while ($log = $sth->fetchrow_hashref) {
		## Get the link
		$sql = qq(select link_uri, link_title, link_content, user_login from links, users where link_id = $log->{log_ref_id} and user_id = $log->{log_user_id});
		if (($link = MnmDB::row_hash($sql))) {
			$event_timestamp = $log->{time};
			$link->{user_login} = MnmDB::utf8($link->{user_login});
			$link->{link_title} = MnmDB::clean_pseudotags(decode_entities(MnmDB::utf8($link->{link_title})));
			$status = $link_status{$log->{log_type}};
			$content = MnmDB::clean_pseudotags(decode_entities(MnmDB::utf8($link->{link_content})));
			$content .= "\n";
			foreach my $u ($jabber->users()) {
				next if $u->get_pref('jabber-off');
				if ($u->get_pref('jabber-text')) {
					$jabber->SendMessage($u, "$status ($link->{user_login}): $link->{link_title}\n$content http://meneame.net/story/$link->{link_uri}\n");
				} else {
					$jabber->SendMessage($u, "$status ($link->{user_login}): $link->{link_title}\n http://meneame.net/story/$link->{link_uri}\n");
				}
			}
		}
	}

	########## Chats
	$sql = qq{select * from chats where chat_time > $chat_timestamp order by chat_time asc limit 20};
	$sth = MnmDB::prepare($sql);
	$sth->execute ||  die "Could not execute SQL statement: $sql";
	while ($hash = $sth->fetchrow_hashref) {
		$content = MnmDB::utf8($hash->{chat_text});
		$content = MnmDB::clean_pseudotags(decode_entities($content));
		$poster = new MnmUser(user=>$hash->{chat_user});
		$chat_timestamp = $hash->{chat_time};
		foreach my $u ($jabber->users()) {
			if (!$u->get_pref('jabber-off') && $u->get_pref('jabber-chat') && $u != $poster && $u->friend($poster) >= 0 && ($hash->{chat_room} eq 'all' || $poster->friend($u) > 0 )) {
				$jabber->SendMessage($u, "$poster->{user}: $content");
			}
		}
	}
	#print "$dbtime-$chat_timestamp\n";
}

sub ExecuteCommand {
	my $poster = shift;
	$_ = shift;
	my $mess;

	$_ =~ s/^ +//;
	if (/^!time/) {
		$jabber->SendMessage($poster, time);
	} elsif (/^!help/) {
		$jabber->SendMessage($poster, "»» Comandos:\n!help: esta ayuda\n!whoami: te dice tu nombre de usuario en el menéame\n!prefs: muestra las preferencias\n!off: deshabilita la recepción de todos los mensajes\n!on: vuelve a habilitar la recepción de mensajes\n!chat: muestra los mensajes de chat de la fisgona\n!nochat: no muestra los mensajes de chat de la fisgona\n!text: muestra el texto de las noticias\n!notext: no muestra el texto de las noticias\n!who: lista los totales de usuarios y los amigos conectados (deben ser amigos mutuos)\n!gs http://un.enlace.muy.largo etiqueta: crea enlace corto (la etiqueta es opcional)");
	} elsif (/^!prefs/) {
		my $key;
		$mess = '»» ';
		foreach $key (keys %{$poster->{prefs}}) {
			$mess .= "$key -> $poster->{prefs}{$key}\n";
		}
		$jabber->SendMessage($poster, $mess);
	} elsif (/^!off/) {
		$poster->store_prefs('jabber-off', 1);
		$jabber->SendMessage($poster, '»» recepción de mensajes deshabilitados');
	} elsif (/^!on/) {
		$poster->store_prefs('jabber-off', '');
		$jabber->SendMessage($poster, '»» recepción de mensajes habilitados');
	} elsif (/^!chat/) {
		$poster->store_prefs('jabber-chat', 1);
		$jabber->SendMessage($poster, '»» chat habilitado');
	} elsif (/^!nochat/) {
		$poster->store_prefs('jabber-chat', '');
		$jabber->SendMessage($poster, '»» chat deshabilitado');
	} elsif (/^!text/) {
		$poster->store_prefs('jabber-text', 1);
		$jabber->SendMessage($poster, '»» mostrará el texto de las noticias');
	} elsif (/^!notext/) {
		$poster->store_prefs('jabber-text', '');
		$jabber->SendMessage($poster, '»» no mostrará el texto de las noticias');
	} elsif (/^!whoami/) {
		$jabber->SendMessage($poster, "»» " . $poster->{user});
	} elsif (/^!gs/) {
		my @args = split;
		$jabber->SendMessage($poster, "»» " . Commands::fon_gs($args[1], $args[2]));
	} elsif (/^!who/) {
		## List total of connected users
		my ($ccntu) = $MnmDB::dbh->selectrow_array("select count(*) from sneakers where sneaker_user > 0 and sneaker_id not like 'jabber/%'");
		my ($ccntj) = $MnmDB::dbh->selectrow_array("select count(*) from sneakers where sneaker_user > 0 and sneaker_id like 'jabber/%'");
		my ($ccnta) = $MnmDB::dbh->selectrow_array("select count(*) from sneakers where sneaker_user = 0");
		my $total_users = $ccntu + $ccntj + $ccnta;
		$mess = "»» Conectados: $total_users, usuarios en web: $ccntu, usuarios en jabber: $ccntj, anónimos: $ccnta\n";

		## List connected friends
		# The relationshipt must be bi-directional
		my ($sql, $sth, $hash, $user);
		$sql = qq{select distinct user_login from sneakers, users where sneaker_user > 0 and user_id = sneaker_user};
		$sth = MnmDB::prepare($sql);
		$sth->execute ||  die "Could not execute SQL statement: $sql";
		$mess .= '»» Amigos conectados: ';
		while (my $hash = $sth->fetchrow_hashref) {
			$user = new MnmUser(user=>$hash->{user_login});
			if(!$user->get_pref('jabber-off') && $poster->friend($user) > 0 && $user->friend($poster) > 0) {
				$mess .= " ".$user->user." ";
			}
		}
		$jabber->SendMessage($poster, $mess);
	}

}

sub StoreChat {
	my $poster = shift;
	my $body = shift;
	my ($sql, $sth, $array);

	my $id = $poster->id;

	if ($poster->karma < 5.5) {
		$jabber->SendMessage($poster, "no tienes suficiente karma");
		return;
	}
	if (! $poster->get_pref('jabber-chat')) {
		$jabber->SendMessage($poster, "tiene deshabilitado el chat, puedes habilitarlo con el comando !chat");
		return;
	}
	if ($poster->get_pref('jabber-off')) {
		$jabber->SendMessage($poster, "tiene deshabilitado recepción de mensajes, puedes habilitarlo con el comando !on");
		return;
	}
	if (length($body) < 3) {
		$jabber->SendMessage($poster, "mensaje muy corto");
		return;
	}
	if (length($body) > 250) {
		$jabber->SendMessage($poster, "mensaje demasiado largo (long max: 250)");
		return;
	}


	my $period = time - 9;
	$sql = qq{select count(*) from chats where chat_time > $period and chat_uid = $id};
	my $array = MnmDB::row_array($sql);
	if ($array->[0] > 0) {
		$jabber->SendMessage($poster, "tranquilo charlatán :-)");
		return;
	}
	my $now = time;
	my $room = 'all';
	$body = MnmDB::clean_text($body);
	$sth = MnmDB::prepare(qq{insert into chats (chat_time, chat_uid, chat_room, chat_user, chat_text) values (?, ?, ?, ?, ?)});
	$sth->execute($now, $poster->id, $room, $poster->user, $body);
	my $last_id = MnmDB::last_insert_id;
}


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

use FindBin;
use HTML::Entities;
use Net::XMPP;
use lib "$FindBin::Bin/";
use Proc::Daemon;

use MnmDB;
use MnmUsers;
use MnmUser;
use strict;
use utf8;

my %AccountConfig;

if (!MnmDB::read_configuration("$FindBin::Bin/sneaker.conf", \%AccountConfig)) {
	print "Configuration file sneaker.conf was not found\n";
	exit(1);
}

print "Data dir: $FindBin::Bin\n";

if ($ARGV[0] eq "-d") {
	my $outfile;
	if (defined($AccountConfig{logfile})) {
		$outfile = $FindBin::Bin.'/'.$AccountConfig{logfile};
	} else {
		$outfile = '/dev/null';
	}
	print "Redirecting output to: $outfile\n";
	# Go to daemon mode
	Proc::Daemon::Init;
	open STDIN, '/dev/null'   or die "Can't read /dev/null: $!";
	open STDOUT, ">$outfile" or die "Can't write to $outfile: $!";
	open STDERR, ">$outfile" or die "Can't write to $outfile: $!";
}

my $server = $AccountConfig{server};
my $port = $AccountConfig{port};
my $username = $AccountConfig{username};
my $componentname = $AccountConfig{componentname};
my $password = $AccountConfig{password};
my $resource = $AccountConfig{resource};
my $myJid = "$username\@$componentname";

$SIG{HUP} = 'IGNORE';
$SIG{KILL} = \&Stop;
$SIG{TERM} = \&Stop;
$SIG{INT} = \&Stop;

my $Connection;
my $Users;
my $event_timestamp;
my $chat_timestamp;
my $counter_timestamp;

my %link_status = qw(link_new Nueva link_publish Publicada link_edit Editada link_discard Descartada);

MnmDB::init(%AccountConfig);

while (1) {
	$Users = new MnmUsers;
	$Connection =  new Net::XMPP::Client(debuglevel=>0);

	$Connection->SetCallBacks(message=>\&InMessage,
							presence=>\&InPresence,
							iq=>\&InIQ);

	$chat_timestamp = time;
	$event_timestamp = time - 60;
	my $status = $Connection->Connect(hostname=>$server,
									port=>$port,
									componentname=>$componentname,
									tls=>1
								);

	if (!(defined($status))) {
		print "ERROR:  Jabber server is down or connection was not allowed.\n";
		print "        ($!)\n";
		next;
		sleep(15);
	}

	# Change hostname
	my $sid = $Connection->{SESSION}->{id};
	$Connection->{STREAM}->{SIDS}->{$sid}->{hostname} = $componentname;

	my @result = $Connection->AuthSend(username=>$username,
										password=>$password,
										resource=>$resource,
									);

	if ($result[0] ne "ok") {
    	print "ERROR: Authorization failed: $result[0] - $result[1]\n";
		next;
		sleep(60);
	}

	print "Logged in to $server:$port...\n";

	#print "Getting Roster to tell server to send presence info...\n";
	$Connection->RosterGet();

	print "Sending presence to tell world that we are logged in...\n";
	$Connection->PresenceSend();
	print "presence sent...\n";

	while(defined($status = $Connection->Process(5))) { 
		if(defined($status) && $status == 0)  {
			UpdateCounters();
			ReadEvents();
		}

	}

	print "ERROR: The connection was killed...\n";
	$Connection->Disconnect();
	sleep(15);
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
	foreach my $u ($Users->users()) {
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
	$sql = qq{select UNIX_TIMESTAMP(log_date) as time, log_type, log_ref_id, log_user_id from logs where log_date > '$dbtime' and log_type in ('link_new','link_publish','link_discard') order by log_date asc limit 10};
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
			foreach my $u ($Users->users()) {
				if ($u->get_pref('jabber-text')) {
					SendMessage($u, "$status ($link->{user_login}): $link->{link_title}\n$content http://meneame.net/story/$link->{link_uri}\n");
				} else {
					SendMessage($u, "$status ($link->{user_login}): $link->{link_title}\n http://meneame.net/story/$link->{link_uri}\n");
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
		foreach my $u ($Users->users()) {
			if ($u->get_pref('jabber-chat') && $u != $poster && ($hash->{chat_room} eq 'all' || $poster->is_friend($u))) {
				SendMessage($u, "$poster->{user}: $content");
			}
		}
	}
	#print "$dbtime-$chat_timestamp\n";
}

sub ExecuteCommand {
	my $poster = shift;
	$_ = shift;

	if (/^!time/) {
		SendMessage($poster, time);
	} elsif (/^!help/) {
		SendMessage($poster, "Comandos:\n!help: esta ayuda\n!prefs: muestra las preferencias\n!chat: muestra los mensajes de chat de la fisgona\n!nochat: no muestra los mensajes de chat de la fisgona\n!text: muestra el texto de las noticias\n!notext: no muestra el texto de las noticias");
	} elsif (/^!prefs/) {
		my $key;
		my $mess;
		foreach $key (keys %{$poster->{prefs}}) {
			$mess .= "$key -> $poster->{prefs}{$key}\n";
		}
		SendMessage($poster, $mess);
	} elsif (/^!chat/) {
		$poster->store_prefs('jabber-chat', 1);
		SendMessage($poster, 'chat habilitado');
	} elsif (/^!nochat/) {
		$poster->store_prefs('jabber-chat', '');
		SendMessage($poster, 'chat deshabilitado');
	} elsif (/^!text/) {
		$poster->store_prefs('jabber-text', 1);
		SendMessage($poster, 'mostrará el texto de las noticias');
	} elsif (/^!notext/) {
		$poster->store_prefs('jabber-text', '');
		SendMessage($poster, 'no mostrará el texto de las noticias');
	}

}

sub StoreChat {
	my $poster = shift;
	my $body = shift;
	my ($sql, $sth, $array);

	my $id = $poster->id;

	if (! $poster->get_pref('jabber-chat')) {
		SendMessage($poster, "tiene deshabilitado el chat");
		return;
	}
	if (length($body) < 3) {
		SendMessage($poster, "mensaje muy corto");
		return;
	}
	if (length($body) > 250) {
		SendMessage($poster, "mensaje demasiado largo (long max: 250)");
		return;
	}

	if ($poster->karma < 5.5) {
		SendMessage($poster, "no tienes suficiente karma");
		return;
	}

	my $period = time - 9;
	$sql = qq{select count(*) from chats where chat_time > $period and chat_uid = $id};
	my $array = MnmDB::row_array($sql);
	if ($array->[0] > 0) {
		SendMessage($poster, "tranquilo charlatán :-)");
		return;
	}
	my $now = time;
	my $room = 'all';
	$body = MnmDB::clean_text($body);
	$sth = MnmDB::prepare(qq{insert into chats (chat_time, chat_uid, chat_room, chat_user, chat_text) values (?, ?, ?, ?, ?)});
	$sth->execute($now, $poster->id, $room, $poster->user, $body);
	my $last_id = MnmDB::last_insert_id;
}


sub Stop
{
	print "Exiting...\n";
	$Connection->Disconnect();
	exit(0);
}


sub InMessage
{
	my $sid = shift;
	my $message = shift;
    
	my $type = $message->GetType();
	my $from = $message->GetFrom();
    
	my $subject = $message->GetSubject();
	my $body = $message->GetBody();
	my $user;
	if(!($user = $Users->get($from))) {
		print "ERROR: user not found $from -- $user\n";
		$user = new MnmUser(jid=>$from);
		if ( $user->id > 0) {
			$Users->add($user);
			print "recovered $user\n";
		} else {
			JidReject($from);
			return;
		}
	}
	if ( $type ne 'chat' ) {
		print "Error type '$type' from $from\n";
		$Users->delete($user);
		return;
	}
	if ($body =~ /^!/) {
		ExecuteCommand($user, $body)
	} else {
		StoreChat($user, $body);
		ReadEvents();
	}
}

sub BroadCast {
	my $body = shift;
	foreach my $u ($Users->jids()) {
		print "Broadcast: $u\n";
		SendMessage($u, $body);
	}
}

sub InIQ {

	return;
}

sub InPresence {
	my $sid = shift;
	my $presence = shift;

	my $from = $presence->GetFrom();
	my $to = $presence->GetTo();
	my $type = $presence->GetType();
	my $status = $presence->GetStatus();

	return if !defined($from) || $from eq $myJid;

	my $user = new MnmUser(jid=>$from);
	if ( $user->id > 0) {
		if ($type eq 'subscribe' || $type eq 'subscribed') {
			print "Subscription: $user\n";
			$Connection->Subscription(to=>$user, type=>"subscribed");
			$Users->add($user);
			print "Sent: $user->user:$type\n";

		} elsif ($type eq 'unsubscribe') {
			$Users->delete($user);
			JidReject($user);
			#print "Sent: $user->user:$type\n";
		} elsif ($type eq 'unavailable') {
			$Users->delete($user);
			#print "Sent: $user->user:$type\n";
		} elsif ($type eq '') {
			$Users->add($user);
			#print "Presence: ";
			#foreach my $active ($Users->users()) {
			#	print "$active, ";
			#}
			#print "\n";
		}
	} else {
		$Users->delete($user);
		JidReject($user);
	}
}

sub JidReject {
	(my $jid, my $rs) = split /\//, shift;
	$Connection->Subscription(to=>$jid, type=>"unsubscribed");
	print "Sent ---> unsuscribed $jid\n";
}

sub SendMessage {
	my $to = shift;
	my $message = shift;
	
	my $msg = Net::XMPP::Message->new();
	$msg->SetMessage(to=>$to, "body"  => $message, type=>'chat' );
	$Connection->Send($msg);
}

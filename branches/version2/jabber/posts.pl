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

if (!MnmDB::read_configuration("$FindBin::Bin/posts.conf", \%AccountConfig)) {
	print "Configuration file posts.conf was not found\n";
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
my $timestamp;

MnmDB::init(%AccountConfig);

while (1) {
	$Users = new MnmUsers;
	$Connection =  new Net::XMPP::Client(debuglevel=>0);

	$Connection->SetCallBacks(message=>\&InMessage,
							presence=>\&InPresence,
							iq=>\&InIQ);

	$timestamp=0;
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

	#$Connection->SetPresenceCallBacks(subscribe=>\&InPresence, 
					#unsubscribe=>\&InPresence,
					#available=>\&InPresence,
					#unavailable=>\&InPresence,
					#);
	while(defined($status = $Connection->Process(10))) { 
		if(defined($status) && $status == 0)  {
			ReadPosts();
		}

	}

	print "ERROR: The connection was killed...\n";
	$Connection->Disconnect();
	sleep(15);
}

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
		foreach my $u ($Users->users()) {
			if ($u->is_friend($poster) || $u == $poster) {
				SendMessage($u, "$poster->{user} ($src): $content -- http://meneame.net/notame/$poster->{user}/$postid ");
			}
		}
		#BroadCast($poster->{user}.": $content -- http://meneame.net/notame/".$poster->{user}." ");
	}
}

sub StorePost {
	my $poster = shift;
	my $body = shift;
	my ($sql, $sth, $array);

	my $id = $poster->id;

	if (length($body) < 10) {
		SendMessage($poster, "mensaje muy corto");
		return;
	}
	if (length($body) > 300) {
		SendMessage($poster, "mensaje demsiado largo (long max: 300)");
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
		SendMessage($poster, "ya has enviado una nota hace pocos minutos, debes esperar $remaining minutos");
		return;
	}
	$body = MnmDB::clean_text($body);
	$sth = MnmDB::prepare(qq{INSERT INTO posts (post_user_id, post_src, post_ip_int, post_randkey, post_content) VALUES (?, ?, ?, ?, ?) });
	$sth->execute($poster->id, 'im', 0, int(rand(1000000)), $body);
	my $last_id = MnmDB::last_insert_id;
	$sth = MnmDB::prepare(qq{insert into logs (log_date, log_type, log_ref_id, log_user_id, log_ip) VALUES (FROM_UNIXTIME(?), ?, ?, ?, ?) });
	$sth->execute(time, 'post_new', $last_id, $poster->id, 0);
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
	StorePost($user, $body);
	ReadPosts();
}

sub BroadCast {
	my $body = shift;
	foreach my $u ($Users->jids()) {
		print "Broadcast: $u\n";
		SendMessage($u, $body);
	}
}

sub InIQ
{

	return;
}

sub InPresence
{
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
			#rint "Sent: $user->user:$type\n";
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
	#print "Sent ---> unsuscribed $jid\n";
}

sub SendMessage {
	my $to = shift;
	my $message = shift;
	
	my $msg = Net::XMPP::Message->new();
	$msg->SetMessage(to=>$to, "body"  => $message, type=>'chat' );
	$Connection->Send($msg);
}

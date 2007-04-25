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

package MnmJabber;

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
my $Connection;
my $Users;


sub new {
	my $class = shift;
	my $self =  {};
	bless $self, $class;
	$self;
}

sub init {
	my $self = shift;
	my $file = shift;

	if (!MnmDB::read_configuration("$FindBin::Bin/$file", \%AccountConfig)) {
		print "Configuration file $file was not found\n";
		die;
	}
	print "Data dir: $FindBin::Bin\n";
	$SIG{HUP} = 'IGNORE';
	$SIG{KILL} = \&Stop;
	$SIG{TERM} = \&Stop;
	$SIG{INT} = \&Stop;
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
	MnmDB::init(%AccountConfig);
}

sub setCallBacks {
	my $self = shift;
	my %callbacks = @_;

	while($#_ >= 0) {
		my $func = pop(@_);
		my $tag = pop(@_);
		if (defined($func)) {
			$self->{CB}->{$tag} = $func;
		} else {
			delete($self->{CB}->{$tag});
		}
	}
}

sub connect {
	my $self = shift;
	my $server = $AccountConfig{server};
	my $port = $AccountConfig{port};
	my $username = $AccountConfig{username};
	my $componentname = $AccountConfig{componentname};
	my $password = $AccountConfig{password};
	my $resource = $AccountConfig{resource};
	$self->{myJid} = "$username\@$componentname";
	while (1) {
		$Users = new MnmUsers;
		$Connection =  new Net::XMPP::Client(debuglevel=>0);
	
		$Connection->SetCallBacks(message=>sub{$self->InMessage(@_)},
								presence=>sub{$self->InPresence(@_)},
								iq=>sub{$self->InIQ(@_)});
	
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
		$Connection->PresenceSend(show=>"chat");
		print "presence sent...\n";

		while(defined($status = $Connection->Process(5))) { 
			if(defined($status) && $status == 0)  {
				#### Timeout processs
				if (exists($self->{CB}->{timeout})) {
					&{$self->{CB}->{timeout}};
				}
			}

		}

		print "ERROR: The connection was killed...\n";
		$Connection->Disconnect();
		sleep(10);
	}
}


sub Stop {
	print "Exiting...\n";
	$Connection->Disconnect();
	exit(0);
}


sub InMessage {
	my $self = shift;
	my $sid = shift;
	my $message = shift;
    
	my $type = $message->GetType();
	my $from = $message->GetFrom();
    
	my $subject = $message->GetSubject();
	my $body = $message->GetBody();
	my $user;
	if(!($user = $Users->get($from))) {
		print "ERROR: user not found $from -- $user\n";
		return if $type eq 'error'; # Just ignore it
		$user = new MnmUser(jid=>$from);
		if ( $user->id > 0) {
			$Users->add($user);
			print "recovered $user\n";
		} else {
			$self->JidReject($from);
			return;
		}
	}
	if ( $type ne 'chat' ) {
		print "Error type '$type' from $from\n";
		if ( $type eq 'error' ) {
			$Users->delete($user);
		}
		return;
	}
	# If the user send a message, allow her to receive messages
	$user->show('normal');
	if (exists($self->{CB}->{message})) {
		&{$self->{CB}->{message}}($user,$body);
	}
}

sub BroadCast {
	my $self = shift;
	my $body = shift;
	foreach my $u ($Users->jids()) {
		print "Broadcast: $u\n";
		$self->SendMessage($u, $body);
	}
}

sub InIQ {
	my $self = shift;
	return;
}

sub InPresence {
	my $self = shift;
	my $sid = shift;
	my $presence = shift;

	my $from = $presence->GetFrom();
	my $to = $presence->GetTo();
	my $type = $presence->GetType();
	my $status = $presence->GetStatus();

	return if !defined($from) || $from eq $self->{myJid};

	#print "Received presence: $from ($type, $status, " . $presence->GetShow(). ")\n";
	my $user = new MnmUser(jid=>$from);
	if ( $user->id > 0) {
		if ($type eq 'subscribe' || $type eq 'subscribed') {
			print "Subscription: $user\n";
			$Connection->Subscription(to=>$from, type=>"subscribed");
		} elsif ($type eq 'unsubscribe') {
			$Users->delete_all($user);
			$self->JidReject($from);
		} elsif ($type eq 'unavailable') {
			$Users->delete($user);
		} elsif ($type eq '') {
			$user->show($presence->GetShow() || 'normal');
			$Users->add($user);
		} else {
			print "Presence: received $type from $user\n";
		}
	} else {
		$Users->delete($user);
		$self->JidReject($from);
	}
}

sub JidReject {
	my $self = shift;
	(my $jid, my $rs) = split /\//, shift;
	$Connection->Subscription(to=>$jid, type=>"unsubscribed");
	#print "Sent ---> unsuscribed $jid\n";
}

sub SendMessage {
	my $self = shift;
	my $to = shift;
	my $message = shift;
	
	my $msg = Net::XMPP::Message->new();
	$msg->SetMessage(to=>$to, "body"  => $message, type=>'chat' );
	$Connection->Send($msg);
}

sub users {
	my $self = shift;
	return $Users->users();
}

1;


# The source code packaged with this file is Free Software, Copyright (C) 2005 by
# Ricardo Galli <gallir at uib dot es>.
# http://meneame.net/
# It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
# You can get copies of the licenses here:
#      http://www.affero.org/oagpl.html
# AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
#

package MnmUsers;

use MnmDB;
use MnmUser;
use strict;

sub new {
	my $class = shift;
	my $self =  {};
	bless $self, $class;
	$self;
}

sub add {
	my $self = shift;
	my $user = shift;

	if (!defined($self->{'jid'}{$user->{jid}})) {
		$self->{'jid'}{$user->{jid}} = $user;
		#print "Adding: " . $user->{jid} . "\n";
	} else {
		# Only change the presence "show"
		$self->{'jid'}{$user->{jid}}->show($user->show);
		#print "Reusing: " . $user->{jid} . "\n";
	}
}

sub delete {
	my $self = shift;
	my $user = shift;

	delete $self->{'jid'}{$user->{jid}};
	#print "Deleting: " . $user->{jid} .  "\n";
}

sub delete_all {
	my $self = shift;
	my $user = shift;

	my $jid_clean = $user->jid_clean;
	foreach my $jid (keys %{$self->{'jid'}}) {
		if ($jid =~ /^$jid_clean/) {
			delete $self->{'jid'}{$jid};
			#print "Deleting: $jid\n";
		}
	}
}

sub get {
	my $self = shift;
	my $jid = shift;

	if ($self->{'jid'}{$jid}) {
		if( ! $self->{'jid'}{$jid}->check()) {
			delete $self->{'jid'}{$jid};
			return 0;
		}
	}
	return $self->{'jid'}{$jid};
}

sub jids {
	my $self = shift;
	return keys %{$self->{'jid'}};
}

sub users {
	my $self = shift;
	my %users;
	foreach my $user (values %{$self->{'jid'}}) {
		if ($user->show eq 'normal' || $user->show eq 'chat') {
			$users{$user->{jid}} = $user;
		}
	}
	return values %users;
}

sub unique_users {
	my $self = shift;
	my %users;
	my %unique;

	foreach my $user (values %{$self->{'jid'}}) {
		if (!defined($unique{$user->{user}}) && ($user->show eq 'normal' || $user->show eq 'chat')) {
			$users{$user->{jid}} = $user;
			$unique{$user->{user}} = 1;
		}
	}
	return values %users;
}

1;

__END__


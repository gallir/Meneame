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

	$self->{'jid'}{$user->{jid}} = $user;
	$self->{'jid_counter'}{$user->{jid}} ++;
	if ($self->{'jid_counter'}{$user->{jid}} > 1) {
		print "Adding: " . $user->{jid} . ": " . $self->{'jid_counter'}{$user->{jid}} . "\n";
	}
}

sub delete {
	my $self = shift;
	my $user = shift;

	$self->{'jid_counter'}{$user->{jid}} --;
	if ($self->{'jid_counter'}{$user->{jid}} <= 0 ) {
		delete $self->{'jid'}{$user->{jid}};
		delete $self->{'jid_counter'}{$user->{jid}};
	} else {
		print "Deleting: " . $user->{jid} . ": " . $self->{'jid_counter'}{$user->{jid}} . "\n";
	}
}

sub get {
	my $self = shift;
	(my $jid, my $rs) = split /\//, shift;

	if ($self->{'jid'}{$jid}) {
		if( ! $self->{'jid'}{$jid}->check()) {
			delete $self->{'jid'}{$jid};
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
	return values %{$self->{'jid'}};
}

1;

__END__


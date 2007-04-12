# The source code packaged with this file is Free Software, Copyright (C) 2005 by
# Ricardo Galli <gallir at uib dot es>.
# http://meneame.net/
# It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
# You can get copies of the licenses here:
#      http://www.affero.org/oagpl.html
# AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
#

package MnmUser;

use MnmDB;
use strict;

our $Count = 0;
use overload q("") => \&as_string;
use overload '==' => sub { 
	my $first = shift; 
	my $second = shift;
	return $first->{jid} eq $second->{jid};
};

use overload '!=' => sub { 
	my $first = shift; 
	my $second = shift;
	return $first->{jid} ne $second->{jid};
};


sub DESTROY {
	my $self = shift;
	$Count--;
}

sub new {
	my $class = shift;
	my %arg = @_;
	my $self;


	if (defined($arg{jid})) {
		(my $jid, my $rs) = split /\//, $arg{jid};
		$self =  {jid => $jid, timestamp => 0, karma => 0};
		bless $self, $class;
		$self->read('jid');
	} elsif (defined($arg{user})){
		$self =  {user => $arg{user}, timestamp => 0, karma => 0};
		bless $self, $class;
		$self->read('user');
	}

	$Count++;
	$self;
}

sub read {
	my $self = shift;
	my $what = shift;
	my $key;
	my $constraint;

	if ($what eq 'user') {
		$constraint = '';
		$what = 'user_login';
		$key = MnmDB::escape($self->{user});
	} else {
		$what = 'user_public_info';
		$constraint = 'user_public_info is not null AND';
		$key = MnmDB::escape($self->{jid});
	}
	my ($sql, $sth, $hash);
	$sql = qq{SELECT * from users WHERE $constraint $what = $key AND user_level != 'disabled' ORDER BY user_id DESC LIMIT 1};
	$hash = MnmDB::row_hash($sql);
	if ($hash->{user_id} > 0) {
		$self->{id} = $hash->{user_id};
		if (!defined($self->{user})) {
			$self->{user} = $hash->{user_login};
		}
		if (!defined($self->{jid})) {
			$self->{jid} = $hash->{user_public_info};
		}
		$self->{karma} = $hash->{user_karma};
		$self->{timestamp} = time;
	} else {
		$self->{id} = 0;
		$self->{user} = undef;
	}
	return $self->{id};
}

sub check {
	my $self = shift;
	if (time - $self->{timestamp} > 180) { # Read every three minutes
		$self->{id} = 0;
		$self->read;
	}
	if (!$self->{id}>0) {
		return 0;
	}
	return 1;
}

sub id {
	my $self = shift;
	return $self->{id};
}

sub jid {
	my $self = shift;
	return $self->{jid};
}

sub user {
	my $self = shift;
	return $self->{user};
}

sub karma {
	my $self = shift;
	return $self->{karma};
}


sub is_friend {
	my $self = shift;
	my $user = shift;
	my ($sql, $sth);
	my $from = $self->{id};
	my $to = $user->{id};
	$sql = qq{SELECT count(*) FROM friends WHERE friend_type='manual' and friend_from = $from and friend_to = $to};
	my $res = MnmDB::row_array($sql);
	return $res->[0];
}

sub as_string {
	my $self = shift;
	return $self->{jid};
}
1;

__END__

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
	return $first->jid_clean eq $second->jid_clean;
};

use overload '!=' => sub { 
	my $first = shift; 
	my $second = shift;
	return $first->jid_clean ne $second->jid_clean;
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
		$self =  {jid => $arg{jid}, timestamp => 0, karma => 0};
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
		$key = MnmDB::escape($self->jid_clean);
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
	$self->read_prefs;
	return $self->{id};
}

sub read_prefs {
	my $self = shift;
	my ($sql, $sth, $key, $value);
	$sql = qq{SELECT pref_key, pref_value from prefs WHERE pref_user_id = $self->{id} and pref_key like 'jabber%'};
	$sth = MnmDB::prepare($sql);
	$sth->execute ||  die "Could not execute SQL statement: $sql";
	while ( ($key, $value) = $sth->fetchrow_array) {
		$self->{prefs}{$key} = $value;
	}
}

sub store_prefs {
	my $self = shift;
	my $key = shift;
	my $value = shift;
	my ($sql, $sth);
	if ($value =~ /[^0]+/) {
		$sql = qq{replace into prefs (pref_user_id, pref_key, pref_value) values (?, ?, ?)};
		$sth = MnmDB::prepare($sql);
		$sth->execute($self->{id}, $key, $value);
		$self->{prefs}{$key} = $value;
	} else {
		$sql = qq{delete from prefs where pref_user_id = $self->{id} and pref_key = '$key'};
		$sth = MnmDB::prepare($sql);
		$sth->execute;
		delete($self->{prefs}{$key});
	}
}

sub get_pref {
	my $self = shift;
	my $key = shift;

	return $self->{prefs}{$key};
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

sub show {
	my $self = shift;
	my $show = shift;
	if ($show) { 
		$self->{show} = $show;
	}
	return $self->{show};
}

sub jid_clean {
	my $self = shift;
	my $clean_jid;
	($clean_jid) = $self->{jid} =~ /^([^\/]+)/;
	return $clean_jid;
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

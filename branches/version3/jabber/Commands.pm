# The source code packaged with this file is Free Software, Copyright (C) 2005 by
# Ricardo Galli <gallir at uib dot es>.
# http://meneame.net/
# It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
# You can get copies of the licenses here:
#      http://www.affero.org/oagpl.html
# AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
#

use strict;
package Commands;

# libwww-perl
use LWP::Simple;

sub fon_gs {
	my $link = shift;
	my $tag = shift;

	# urlencode
	$link =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;
	$tag =~ s/([^A-Za-z0-9])/sprintf("%%%02X", ord($1))/seg;

	# http://fon.gs/create.php?url=http://www.fon.com&linkname=pruebaapi
	# OK: http://fon.gs/pruebaapi
	# MODIFIED: http://fon.gs/pruebaapi1
	# ERROR: <error msg>      p.ej --->  ERROR: Invalid URL

	my $content = get('http://fon.gs/create.php?url='.$link.'&linkname='.$tag);
	chomp($content);
	return $content;
}
1;
__END__

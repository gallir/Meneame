#! /bin/bash
# execute with posts_safe.sh > /dev/null 2>&1 &

while true
do
	mydir=`dirname $0`
	echo $mydir
	$mydir/sneaker.pl > $mydir/sneaker.log 2>&1 | logger -p daemon.err -t sneaker.pl -i -s & wait
	echo "Process terminated, sleeping 10 seconds" | logger -p daemon.err -t sneaker_safe -i -s
	sleep 10
done


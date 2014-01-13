#! /bin/bash
# execute with check_access_safe.sh > /dev/null 2>&1 &

while true
do
	mydir=`dirname $0`
	echo $mydir
	$mydir/check_access.py -b -p 6 -r 15 -q -s 60 -A 1500 -m meneame-admins@googlegroups.com 2>&1 | logger -p daemon.err -t check_access.py -i -s & wait
	echo "Process terminated, sleeping 10 seconds" | logger -p daemon.err -t check_access_safe -i -s
	sleep 10
done


#! /bin/bash

for f in *.dat
do
	base=`basename $f .dat`
	year=`echo $base | awk -F- '{print $1}'`
	month=`echo $base | awk -F- '{print $2}'`
	plt=/tmp/$base.plt

	echo set key below > $plt
	echo set xdata time >> $plt
	echo set timefmt '"%d-%m-%Y"' >> $plt
	echo set format x '"%d"'  >> $plt
	echo set xrange \[\"01-$month-$year\":\"31-$month-$year\"\]  >> $plt
	echo set terminal png transparent nocrop enhanced  size 400,400  >> $plt
	echo set output \'votes-$base.png\'   >> $plt
	echo set title \'$base\' >> $plt
	echo set grid mxtics >> $plt
	#echo set xlabel \'day\'  >> $plt
	echo plot  \'$f\' using 1:2 title \"comments\" with linespoints, \'$f\' using 1:3 title \"link votes\" with linespoints, \'$f\' using 1:4 title \"comment votes\" with linespoints, \'$f\' using 1:5 title \"total users\" with linespoints >> $plt
	gnuplot $plt
	rm $plt
done


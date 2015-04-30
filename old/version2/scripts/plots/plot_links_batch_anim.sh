#! /bin/bash

# it gets data from one big file and create N files intended to generate and animated gif

max=450
step=2

f=$1

for s in `seq -w 0 $step $max`
do
	step=`./ranges.pl $s`
	echo $step
	base=`echo $step | awk '{print $1}'`
	end=`echo $step | awk '{print $2}'`
	plt=/tmp/$s.plt

	#echo set key below > $plt
	echo set xdata time >> $plt
	echo set timefmt '"%d-%m-%Y"' >> $plt
	echo set format x '"%d"'  >> $plt
	echo set yrange \[0:1000]  >> $plt
	echo set xrange \[\"$base\":\"$end\"\]  >> $plt
	echo set terminal gif size 600,400  >> $plt
	echo set output \'anim-$s.gif\'   >> $plt
	echo set title \'$step\' >> $plt
	#echo set grid mxtics >> $plt
	#echo set xlabel \'day\'  >> $plt
	echo plot  \'$f\' using 1:2 title \"links\" with linespoints, \'$f\' using 1:3 title \"discarded\" with linespoints, \'$f\' using 1:4 title \"published\" with linespoints, \'$f\' using 1:5 title \"average karma\" with linespoints >> $plt
	gnuplot $plt
	rm $plt
done


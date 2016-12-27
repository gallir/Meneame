<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005-2013 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

// Cumulative Distribution Function
// It returns the probability of val < array[i]
function cdf($array, $value) {
	$len = count($array);
	if ($len == 0) return 0;

	$i = 0;

	while ($i < $len) {
		if ($array[$i] > $value) break;
		$i++;
	}

	return $i/$len;
}

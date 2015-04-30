<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>

function do_contained_pages($id, $total, $current, $page_size, $program, $type, $container) {
	global $db;

	$index_limit = 6;

	$total_pages=ceil($total/$page_size);
	$start=max($current-intval($index_limit/2), 1);
	$end=$start+$index_limit-1;
	
	echo '<div class="mini-pages">';
	if($start>1) {
		$i = 1;
		echo '<a href="javascript:get_votes(\''.$program.'\',\''.$type.'\',\''.$container.'\','.$i.','.$id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
		echo '<span>...</span>';
	}
	for ($i=$start;$i<=$end && $i<= $total_pages;$i++) {
		if($i==$current) echo '<span class="current">';
		echo '<a href="javascript:get_votes(\''.$program.'\',\''.$type.'\',\''.$container.'\','.$i.','.$id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
		if($i==$current) echo '</span>';
	}
	if($total_pages>$end) {
		$i = $total_pages;
		echo '<span>...</span>';
		echo '<a href="javascript:get_votes(\''.$program.'\',\''.$type.'\',\''.$container.'\','.$i.','.$id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
	}
	echo "</div>\n";
}

?>

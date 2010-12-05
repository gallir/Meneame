<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es> and 
// Beldar <beldar.cat at gmail dot com>
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
// The code below was made by Beldar <beldar at gmail dot com>
// Modified by Alberto Vidal <a24v7b at gmail dot com>

function do_contained_pages($id, $total, $current, $page_size, $program, $type, $container = false) {
	global $globals;

	$index_limit = 6;

	$total_pages=ceil($total/$page_size);
	$start=max($current-intval($index_limit/2), 1);
	$end=min($start+$index_limit-1, $total_pages);
	$start=max($end-$index_limit+1,1);
	
	echo '<div class="pages">';
	if($start>1) {
		$i = 1;
			do_contained_page_link($id, $i, $program, $type, $container);
		if($start>2) echo '<span>...</span>';
	}
	for ($i=$start;$i<=$end;$i++) {
		if($i==$current) {
				echo '<span class="current">'.$i.'</span>';
		} else {
			do_contained_page_link($id, $i, $program, $type, $container);
		}
	}
	if($total_pages>$end) {
		$i = $total_pages;
		if($total_pages>$end+1) echo '<span>...</span>';
		do_contained_page_link($id, $i, $program, $type, $container);
	}
	echo "</div>\n";
	if (! $container) {
		echo '<script type="text/javascript">';
		echo '$(document).ready(function() {$("a.fancybox").fancybox({transitionIn: "none", transitionOut: "none"})});';
		echo '</script>';
	}

}

function do_contained_page_link($id, $i, $program, $type, $container) {
	if ($container)
		echo '<a href="javascript:get_votes(\''.$program.'\',\''.$type.'\',\''.$container.'\','.$i.','.$id.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
	else
		echo '<a class="fancybox" href="'.backend_call_string($program, $type, $i, $id).'">'.$i.'</a>';
}

?>

<?php

$globals['skip_check_ip_noaccess'] = true;
include('../config.php');
include(mnminclude . 'html1.php');
include('libs/admin.php');

header('Content-Type: text/html; charset=utf-8');

global $db, $globals;

if (!empty($_GET['id'])) {
	$id = intval($_GET['id']);
}

if (! $id > 0 ) die;

array_push($globals['cache-control'], 'no-cache');
http_cache();

if ($_GET['process'] == 'get_reporters') {


	global $db;
	$comment_id = intval($_GET['id']);
	$reason = clean_input_string($_GET['reason']);

	if (!isset($_GET['p']))  {
		$reporters_page = 1;
	} else $reporters_page = intval($_GET['p']);

	$reporters_page_size = 20;
	$reporters_offset=($reporters_page-1)*$reporters_page_size;

	$total = $db->get_var("select count(*) from reports where report_ref_id=$comment_id and report_reason='$reason'");

	$sql = "SELECT report_id, report_date, users.user_id, users.user_login, users.user_level, user_avatar FROM `reports`left join users on users.user_id=report_user_id where report_ref_id=$comment_id and report_reason='$reason' order by report_date ASC LIMIT $reporters_offset,$reporters_page_size";
	$reporters = $db->get_results($sql);

	echo '<div style="width:550px;padding: 5px 5px;text-align:left">';
	echo '<div style="padding-top: 20px;min-width:350px">';
	if ($reporters) {
		echo '<div class="reporters-list">';
		foreach ( $reporters as $reporter ) {
			echo '<div class="item">';
			echo '<a href="'.get_user_uri($reporter->user_login).'" title="'.$reporter->user_login.': '.$reporter->report_date .'" target="_blank">';
			echo '<img class="avatar" src="'.get_avatar_url($reporter->user_id, $reporter->user_avatar, 20).'" width="20" height="20" alt=""/>';
			echo $reporter->user_login.'</a>';
			echo '</div>';
		}
		echo "</div>\n";
	}

	do_contained_pages_reports($comment_id, $total, $reporters_page, $reporters_page_size, 'get_reporters', $reason);
	echo '</div>';
	echo '</div>';

}

function do_contained_pages_reports($id, $total, $current, $page_size, $process, $reason) {
	global $globals;

	$index_limit = 6;

	$total_pages=ceil($total/$page_size);
	$start=max($current-intval($index_limit/2), 1);
	$end=min($start+$index_limit-1, $total_pages);
	$start=max($end-$index_limit+1,1);

	echo '<div class="pages">';
	if($start>1) {
		$i = 1;
		do_contained_page_link_reports($process, $id, $reason, $i);
		if($start>2) echo '<span>...</span>';
	}
	for ($i=$start;$i<=$end;$i++) {
		if($i==$current) {
			echo '<span class="current">'.$i.'</span>';
		} else {
			do_contained_page_link_reports($process, $id, $reason, $i);
		}
	}
	if($total_pages>$end) {
		$i = $total_pages;
		if($total_pages>$end+1) echo '<span>...</span>';
		do_contained_page_link_reports($process, $id, $reason, $i);
	}
	echo "</div>\n";
}


function do_contained_page_link_reports($process, $id, $reason, $i) {
	echo '<a href="javascript:get_reporters(\''.$process.'\','.$id.',\''.$reason.'\','.$i.')" title="'._('ir a página')." $i".'">'.$i.'</a>';
}



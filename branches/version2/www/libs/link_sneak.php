<script type="text/javascript">
//<![CDATA[
var link_id = <? echo $globals['link_id'] ?>;
var link_votes_0 = <? echo $globals['link']->votes; ?>;
var link_negatives_0 = <? echo $globals['link']->negatives; ?>;
var link_karma_0 = <? echo $globals['link']->karma; ?>;
var link_votes = 0;
var link_negatives = 0;
var link_karma = 0;
//]]>
</script>
<script type="text/javascript" src="http://<? echo get_server_name().$globals['base_url']; ?>js/link_sneak02.js.php"></script>
<?
echo '<div class="mini-sneaker-item">';
echo '<div class="sneaker-title">';
echo '<div class="mini-sneaker-ts"><strong>'._('hora').'</strong></div>';
echo '<div class="mini-sneaker-type"><strong>'._('acción').'</strong></div>';
echo '<div class="mini-sneaker-votes"><strong><abbr title="'._('meneos').'">me</abbr>/<abbr title="'._('comentarios').'">co</abbr></strong></div>';
echo '<div class="mini-sneaker-who">&nbsp;<strong>'._('quién/qué').'</strong></div>';
echo '<div class="mini-sneaker-status"><strong>'._('estado').'</strong></div>';
echo "</div>\n";
echo "</div>\n";

for ($i=0; $i<10;$i++) {
	echo '<div id="sneaker-'.$i.'" class="mini-sneaker-item">&nbsp;';
	echo "</div>\n";
}
?>

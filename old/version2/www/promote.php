<?
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
// 		http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include(mnminclude.'html1.php');
include(mnminclude.'annotation.php');

$globals['ads'] = true;
do_header(_('promote') . ' // men&eacute;ame');
do_banner_top();
echo '<div id="container-wide">' . "\n";
promote_style();

$annotation = new Annotation('promote');
$annotation->text = $output;
if($annotation->read()) {
	echo $annotation->text;
}


do_footer();


function promote_style() {
?>
<style type="text/css">
p {
    font-family: Bitstream Vera Sans, Arial, Helvetica, sans-serif;
    font-size: 90%;
}
table {
    font-size: 110%;
    margin: 0px;
    padding: 4px;
}
td {
    margin: 0px;
    padding: 4px;
}
.thead {
    font-size: 115%;
    text-transform: uppercase;
    color: #FFFFFF;
    background-color: #FF6600;
    padding: 6px;
}
.tdata0 {
    background-color: #FFF;
}
.tdata1 {
    background-color: #FFF3E8;
}
.tnumber0 {
    text-align: center;
}
.tnumber1 {
    text-align: center;
    background-color: #FFF3E8;
}
</style>
<?
}

?>

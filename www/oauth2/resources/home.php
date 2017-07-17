<?php

require_once __DIR__.'/../init.php';

OAuth2Server::getInstance()->checkAccess();

$serializer = JMS\Serializer\SerializerBuilder::create()
    ->setCacheDir($globals['jms_cache'])
    ->addMetadataDir(__DIR__.'/metadata')
    ->setDebug(true)
    ->build();

$page_size = $globals['page_size']*2;
$page = get_current_page();
$offset = ($page - 1) * $page_size;
$from = '';

$rows = Link::count('published');
$where = "sub_statuses.id = ".SitesMgr::my_id()." AND status='published' ";
$order_by = "ORDER BY date DESC ";

if (!$rows) {
    $rows = $db->get_var("SELECT SQL_CACHE count(*) FROM sub_statuses $from WHERE $where");
}

// We use a "INNER JOIN" in order to avoid "order by" whith filesorting. It was very bad for high pages
$sql = "SELECT".Link::SQL."INNER JOIN (SELECT link FROM sub_statuses $from WHERE $where $order_by LIMIT $offset,$page_size) as ids ON (ids.link = link_id)";

$globals['site_id'] = SitesMgr::my_id();

// Search for sponsored link
if (!empty($globals['sponsored_link_uri'])) {
    $sponsored_link = Link::from_db($globals['sponsored_link_uri'], 'uri');
}

$links = $db->get_results($sql, "Link");

if ($links) {
    $jsonContent = $serializer->serialize($links, 'json');
    echo $jsonContent;
}
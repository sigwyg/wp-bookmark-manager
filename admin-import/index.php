<?php
require_once(dirname(__FILE__).'/wbm-importer.php');
$wp_bookmark_importer = new WP_Bookmark_Importer();
$wp_bookmark_importer->dispatch();
?>

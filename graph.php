<?php

if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__).'/../../../');
define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC.'inc/init.php');



$graph = plugin_load('helper', 'statdisplay_graph');

//$graph->graph('all');
//$graph->graph('month by day');
$graph->sendgraph($INPUT->str('graph'));
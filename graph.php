<?php
/**
 * statdisplay plugin graph dispatcher
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */


if(!defined('DOKU_INC')) define('DOKU_INC', dirname(__FILE__).'/../../../');
define('DOKU_DISABLE_GZIP_OUTPUT', 1);
require_once(DOKU_INC.'inc/init.php');

$graph = plugin_load('helper', 'statdisplay_graph');
$graph->sendgraph(
    $_REQUEST['graph'],
    $_REQUEST['f'],
    $_REQUEST['to']
);

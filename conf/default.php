<?php
/**
 * Options for logstats plugin
 */

$conf['accesslog']                       = 'data/access.log'; // Acces log file name to use
$conf['visit_time']                      = 30; // time in minutes of a visit
$conf['referer']                         = 'complete_link'; // display choice for the refereer
$conf['top_url_number_of_lines']         = 30;
$conf['auto_compute_stats']              = TRUE;
$conf['top_user_agents_number_of_lines'] = 30;
$conf['top_entries_number_of_lines']     = 10;
$conf['top_kbytes_number_of_lines']      = 5;
$conf['top_referers_number_of_lines']    = 30;
$conf['referer_regular_expr']            = '"Google={\'/google\.[^f][^r]/\'}"';
$conf['user_agent']                      = 'all_line'; // display choice for the user agent
$conf['user_agent_keywords']             = '"firefox=Mozilla Firefox"'."\n".'"msie=Microsoft Internet Explorer"';
$conf['line_number']                     = 10000; // number of line to analyse per request
$conf['memory_cache']                    = 'data/cache'; // Where to cache the generated statistics

/*refereer (total OU nom de domaine OU Expression regulieres
user agent (ligne complete OU moteur affichage OU mots clef)
nombre de lignes a traiter*/

?>

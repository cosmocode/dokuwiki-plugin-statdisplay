<?php
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 * @author     Andreas Gohr <gohr@cosmocode.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_statdisplay extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    function getType() {
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    function getPType() {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort() {
        return 155;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{statdisplay>[^\}]+\}\}', $mode, 'plugin_statdisplay');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, &$handler) {
        $command = trim(substr($match, 14 ,-2));
        list($command, $params) = explode('?', $command);
        $params = explode(' ',$params);

        $params = array_map('trim', $params);
        $params = array_filter($params);

        $pos = array_search('graph', $params);
        if($pos !== FALSE){
            $graph = true;
            unset($params[$pos]);
        } else {
            $graph = false;
        }

        $data = array(
            'command' => $command,
            'params'  => $params,
            'graph'   => $graph
        );

        return $data;
    }

    /**
     * Create output
     */
    function render($format, &$renderer, $data) {
        $command = $data['command'];
        $params  = $data['params'];
        $graph   = $data['graph'];

        // FIXME should be moved to action component
        /** @var $helper helper_plugin_statdisplay_log */
        $log = plugin_load('helper', 'statdisplay_log');
        $log->parseLogData();

        /** @var $table helper_plugin_statdisplay_table */
        $table = plugin_load('helper', 'statdisplay_table');
        $table->table($renderer, $command, $params);


/*
        $statdisplay_daily_histogram  = DOKU_URL.'lib/plugins/statdisplay/daily_histogram.php';
        $statdisplay_resume_histogram = DOKU_URL.'lib/plugins/statdisplay/resume_histogram.php';
        $statdisplay_progress_bar     = DOKU_URL.'lib/plugins/statdisplay/progressbar.php';

        $tmp_users = explode('"', $this->getConf('user_agent_keywords'));
        for($i = 1; $i < count($tmp_users); $i += 2) {
            $tmp2_users                = explode('=', $tmp_users[$i]);
            $tab_agent[$tmp2_users[0]] = $tmp2_users[1];
        }

        $tmp_referers = explode('"', $this->getConf('referer_regular_expr'));
        for($i = 1; $i < count($tmp_referers); $i += 2) {
            $nom           = explode('=', $tmp_referers[$i]);
            $nom           = $nom[0];
            $tmp2_referers = explode('\'', $tmp_referers[$i]);
            for($j = 1; $j < count($tmp2_referers); $j += 2)
                $tab_referers[$nom][$tmp2_referers[$j]] = 1;
        }

        $nb_lignes_traitement = $this->getConf('line_number');
        $log_path             = $this->getConf('accesslog');
        $duree_visite         = 60 * $this->getConf('visit_time');
        $flag_graph           = FALSE;

        if($command == 'compute stats' || $this->getConf('auto_compute_stats')) {
            $_SESSION['need_update'] = TRUE;
            //$renderer->doc.= '<link rel="stylesheet" type="text/css" href="/lib/plugins/statdisplay/style.css" />';
        }
        include_once 'stat.php';


        if($params[0] == 'graph') {
            if($params[1] != '')
                $required = $params[1];
            else
                $required = $_SESSION['last_month'];
        } else if($params[0] != '') {
            $required = $params[0];
        } else {
            $required = $_SESSION['last_month'];
        }

        if($command == 'progress bar') {
            //$renderer->doc .= '<img src="lib/plugins/statdisplay/progressbar.php?max='.$_SESSION['progress']['max'].'&amp;value='.$_SESSION['progress']['value'].'" alt="progress bar"></img>';

            $renderer->doc .= "<img src=\"";
            $renderer->doc .= ml(
                $statdisplay_progress_bar
                    ."?max=".$_SESSION['progress']['max'].
                    "&value=".$_SESSION['progress']['value']
            );
            $renderer->doc .= "\" alt=\"progress bar\"></img>";
        }

        if(strstr($command, 'one month') != NULL) {
            if($flag_graph)
                $renderer->doc .= 'Cannot display graph statistics for one month';
            else {
                include_once 'month_summary.php';
                $renderer->doc .= summary($required);
            }
        }

        if(strstr($command, 'user agents') != NULL) {
            if($flag_graph)
                $renderer->doc .= 'Cannot display graph statistics for user agents';
            else {
                include_once 'user_agents.php';
                $renderer->doc .= user_agents($required, $this->getConf('user_agent'), $tab_agent, $this->getConf('top_user_agents_number_of_lines'));
            }
        }

        if(strstr($command, '{{statdisplay>top referers') != NULL) {
            if($flag_graph)
                $renderer->doc .= 'Cannot display graph statistics for top referers';
            else {
                include_once 'top_referers.php';
                $renderer->doc .= top_referers($required, $this->getConf('referer'), $this->getConf('top_referers_number_of_lines'), $tab_referers, $this->getConf('regular_use'));
            }
        }

        if(strstr($command, '{{statdisplay>month by day') != NULL) {
            if($flag_graph) {
                if(isset($_SESSION['statdisplay'][$required])) {
                    //$renderer->doc .= '<img src="' . $statdisplay_daily_histogram . '?title='.str_replace("_"," ",$required).' Daily Statistics&amp;type=31&amp;month='.$required.'" alt="month by day"></img>';
                    $renderer->doc .= "<img src=\"";
                    $renderer->doc .= ml(
                        $statdisplay_daily_histogram
                            .'?title='.str_replace("_", " ", $required).
                            ' Daily Statistics&type=31&month='.$required
                    );
                    $renderer->doc .= "\" alt=\"month by day\"></img>";
                }
            } else {
                include_once 'daily_statistics.php';
                $renderer->doc .= daily($required);
            }
        }

        if(strstr($command, '{{statdisplay>month by hour') != NULL) {
            if($flag_graph) {
                if(isset($_SESSION['statdisplay'][$required])) {
                    //$renderer->doc .= '<img src="' . $statdisplay_daily_histogram . '?title='.str_replace("_"," ",$required).' Hourly Statistics&amp;type=24&amp;month='.$required.'" alt="month by hour"></img>';
                    $renderer->doc .= "<img src=\"";
                    $renderer->doc .= ml(
                        $statdisplay_daily_histogram
                            .'?title='.str_replace("_", " ", $required).
                            ' Hourly Statistics&type=24&month='.$required
                    );
                    $renderer->doc .= "\" alt=\"month by hour\"></img>";

                }
            } else {
                include_once 'hourly_statistics.php';
                $renderer->doc .= hourly($required);
            }
        }

        if(strstr($command, '{{statdisplay>top bytes') != NULL) {
            if($flag_graph)
                $renderer->doc .= 'Cannot display graph statistics for top bytes';
            else {
                include_once 'top_bytes.php';
                $renderer->doc .= top_bytes($required, $this->getConf('top_kbytes_number_of_lines'));
            }
        }

        if(strstr($command, '{{statdisplay>top urls') != NULL) {
            if($flag_graph)
                $renderer->doc .= 'Cannot display graph statistics for top url';
            else {
                include_once 'top_url.php';
                $renderer->doc .= top_url($required, $this->getConf('top_url_number_of_lines'));
            }
        }

        if(strstr($command, '{{statdisplay>top entries') != NULL) {
            if($flag_graph)
                $renderer->doc .= 'Cannot display graph statistics for user agents';
            else {
                include_once 'top_entries.php';
                $renderer->doc .= top_entries($required, $this->getConf('top_entries_number_of_lines'));
            }
        }

        if(strstr($command, '{{statdisplay>all') != NULL) {
            if(strstr($command, 'graph') != NULL)
                $flag_graph = TRUE;
            $params = explode(' ', $params);

            if($params[0] == 'graph') {
                $begin = $params[1];
                $end   = $params[2];
            } else if($params[1] == 'graph') {
                $begin = $params[0];
                $end   = $params[2];
            } else //param[2]=='graph' ou pas
            {
                $begin = $params[0];
                $end   = $params[1];
            }

            if($flag_graph and isset($_SESSION['statdisplay'])) {
                //$renderer->doc .= '<img src="' . $statdisplay_resume_histogram . '?begin='.$begin.'&amp;end='.$end.'" alt="All months graph"></img>';
                $renderer->doc .= "<img src=\"";
                $renderer->doc .= ml(
                    $statdisplay_resume_histogram
                        ."?begin=".$begin."&end=".$end
                );
                $renderer->doc .= "\" alt=\"All months graph\"></img>";
            } else {
                include_once 'total_summary.php';
                $renderer->doc .= total_summary($begin, $end);
            }
        }
*/
        return true;
    }
}

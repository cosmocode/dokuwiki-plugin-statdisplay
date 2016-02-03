<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();


/**
 * statdisplay plugin syntax component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
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
    function handle($match, $state, $pos, Doku_Handler $handler) {
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

        // remaining params are dates
        list($from, $to) = array_values($params);

        $data = array(
            'command' => $command,
            'graph'   => $graph,
            'from'    => $this->cleanDate($from),
            'to'      => $this->cleanDate($to)
        );

        return $data;
    }

    /**
     * Create output
     */
    function render($format, Doku_Renderer $renderer, $data) {
        if($format != 'xhtml') return true;
        $command = $data['command'];
        $graph   = $data['graph'];
        $from    = $data['from'];
        $to      = $data['to'];

        /** @var $table helper_plugin_statdisplay_table */
        if(!$graph){
            $table = plugin_load('helper', 'statdisplay_table');
            $table->table($renderer, $command, $from, $to);
        }else{
            $img = array(
                'src' => DOKU_BASE.'lib/plugins/statdisplay/graph.php?graph='.rawurlencode($command).'&f='.$from.'&t='.$to,
                'class' => 'media'
            );
            $renderer->doc .= '<img  '.buildAttributes($img).'/>';
        }
        return true;
    }

    /**
     * Make correct year-month format from the input syntax
     *
     * @param $date
     * @return string
     */
    private function cleanDate($date){
        $months = array('','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
        list($month, $year) = explode('_', strtolower($date));
        $year = (int) $year;
        if($year < 2000 || $year > 2050) return '';
        $month = array_search($month, $months);
        if(!$month) return '';
        return sprintf("%d-%02d", $year, $month);
    }

}

<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * statdisplay plugin action component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class action_plugin_statdisplay extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('INDEXER_TASKS_RUN','AFTER', $this, 'handle_run');
    }

    /**
     * Analyze the next chunk of data
     *
     * @param Doku_Event $event
     * @param $param
     */
    function handle_run(&$event, $param) {
        echo "logfile analysis started.\n";

        /** @var $log helper_plugin_statdisplay_log */
        $log = plugin_load('helper', 'statdisplay_log');
        $lines = $log->parseLogData();

        // did we do any work?
        if($lines){
            $event->preventDefault();
            $event->stopPropagation();
        }
        echo "logfile analysis finished analyzing $lines lines.\n";
    }

}

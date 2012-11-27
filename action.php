<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_statdisplay extends DokuWiki_Action_Plugin {

    function register($controller) {
        $controller->register_hook('INDEXER_TASKS_RUN','AFTER', $this, 'handle_run');
    }

    /**
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

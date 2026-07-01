<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;

/**
 * statdisplay plugin action component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class action_plugin_statdisplay extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('INDEXER_TASKS_RUN', 'AFTER', $this, 'handleRun');
    }

    /**
     * Analyze the next chunk of data
     *
     * @param Event $event
     * @param $param
     */
    public function handleRun(&$event, $param)
    {
        echo "logfile analysis started.\n";

        /** @var $log helper_plugin_statdisplay_log */
        $log = plugin_load('helper', 'statdisplay_log');
        if ($log === null) {
            echo "logfile analysis aborted: helper could not be loaded.\n";
            return;
        }
        $lines = $log->parseLogData($this->getConf('lines'));

        // did we do any work?
        if ($lines) {
            $event->preventDefault();
            $event->stopPropagation();
        }
        echo "logfile analysis finished analyzing $lines lines.\n";
    }
}

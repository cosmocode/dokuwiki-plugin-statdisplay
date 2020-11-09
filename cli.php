<?php

use dokuwiki\Extension\CLIPlugin;
use splitbrain\phpcli\Options;

/**
 * statdisplay plugin cli component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class cli_plugin_statdisplay extends CLIPlugin
{

    /**
     * @inheritDoc
     */
    protected function setup(Options $options)
    {
        $options->setHelp('Control the statdisplay plugin');
        $options->registerCommand('parse', 'Parse and analyse the log file');
        $options->registerOption('clear', 'Drop all previously parsed log data and reparse the whole log file', 'c',
            false, 'parse');
        $options->registerOption('lines', 'Number of lines to read per iteration', 'l', 'lines', 'parse');
    }

    /**
     * @inheritDoc
     */
    protected function main(Options $options)
    {
        switch ($options->getCmd()) {
            case 'parse':
                $this->parseData(
                    $this->options->getOpt('clear'),
                    (int)$this->options->getOpt('lines', $this->getConf('lines'))
                );
                break;
            default:
                echo $this->options->help();
        }
    }

    /**
     * Parse the log data
     *
     * @param bool $clear
     */
    protected function parseData($clear, $maxlines)
    {
        /** @var helper_plugin_statdisplay_log $helper */
        $helper = plugin_load('helper', 'statdisplay_log');

        if ($clear) {
            $helper->resetLogCache();
        }

        do {
            $this->info(sprintf('%.2f%%', $helper->progress()));
        } while ($helper->parseLogData($maxlines));
    }
}

<?php

use dokuwiki\Extension\SyntaxPlugin;
use dokuwiki\Parsing\Handler;

/**
 * statdisplay plugin syntax component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class syntax_plugin_statdisplay extends SyntaxPlugin
{
    /** @inheritDoc */
    public function getType()
    {
        return 'substition';
    }

    /** @inheritDoc */
    public function getPType()
    {
        return 'block';
    }

    /** @inheritDoc */
    public function getSort()
    {
        return 155;
    }

    /** @inheritDoc */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern('\{\{statdisplay>[^\}]+\}\}', $mode, 'plugin_statdisplay');
    }

    /** @inheritDoc */
    public function handle($match, $state, $pos, Handler $handler)
    {
        $command = trim(substr($match, 14, -2));
        [$command, $params] = array_pad(explode('?', $command), 2, '');
        $params = explode(' ', $params);

        $params = array_map(trim(...), $params);
        $params = array_filter($params);

        $pos = array_search('graph', $params);
        if ($pos !== false) {
            $graph = true;
            unset($params[$pos]);
        } else {
            $graph = false;
        }

        // remaining params are dates
        [$from, $to] = array_pad(array_values($params), 2, '');

        return [
            'command' => $command,
            'graph' => $graph,
            'from' => $this->cleanDate($from),
            'to' => $this->cleanDate($to),
        ];
    }

    /** @inheritDoc */
    public function render($format, Doku_Renderer $renderer, $data)
    {
        if ($format != 'xhtml') return true;
        $command = $data['command'];
        $graph = $data['graph'];
        $from = $data['from'];
        $to = $data['to'];

        /** @var $table helper_plugin_statdisplay_table */
        if (!$graph) {
            $table = plugin_load('helper', 'statdisplay_table');
            $table->table($renderer, $command, $from, $to);
        } else {
            $img = [
                'src' => DOKU_BASE . 'lib/plugins/statdisplay/graph.php?graph=' .
                    rawurlencode($command) . '&f=' . $from . '&t=' . $to,
                'class' => 'media',
            ];
            $renderer->doc .= '<img  ' . buildAttributes($img) . '/>';
        }
        return true;
    }

    /**
     * Make correct year-month format from the input syntax
     *
     * @param $date
     * @return string
     */
    private function cleanDate($date)
    {
        $months = ['', 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        [$month, $year] = array_pad(explode('_', strtolower($date)), 2, '');
        $year = (int)$year;
        if ($year < 2000 || $year > 2050) return '';
        $month = array_search($month, $months);
        if (!$month) return '';
        return sprintf("%d-%02d", $year, $month);
    }
}

<?php

use CpChart\Data;
use CpChart\Image;
use dokuwiki\Extension\Plugin;

/**
 * statdisplay plugin graph helper component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class helper_plugin_statdisplay_graph extends Plugin
{
    /** Font used for all graphs, shipped with szymach/c-pchart */
    private const FONT = 'verdana.ttf';

    /** @var helper_plugin_statdisplay_log */
    private $log;

    /**
     * Outputs a Graph image
     *
     * @param string $command
     * @param string $from
     * @param string $to
     * @return void
     */
    public function sendgraph($command, $from = '', $to = '')
    {
        $this->log = plugin_load('helper', 'statdisplay_log');

        header('Content-Type: image/png');
        match ($command) {
            'all' => $this->summary(),
            'month by day' => $this->monthby('day', $from),
            'month by hour' => $this->monthby('hour', $from),
            'traffic by day' => $this->trafficby('day', $from),
            'traffic by hour' => $this->trafficby('hour', $from),
            'traffic by user' => $this->userdownloads($from),
            default => $this->nograph('No such graph: ' . $command),
        };
    }

    /**
     * Show all the access data
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    private function summary($from = '', $to = '')
    {
        $times = [];
        $hits = [];
        $pages = [];
        $media = [];
        $visitors = [];

        foreach ($this->log->logdata as $month => $data) {
            if ($month[0] == '_') continue;
            if ($from && $month < $from) continue;
            if ($to && $month > $to) break;

            $times[] = $month;
            $pages[] = $data['page']['all']['count'] ?? 0;
            $media[] = $data['media']['all']['count'] ?? 0;
            $hits[] = $data['hits']['all']['count'] ?? 0;
            $visitors[] = $data['hits']['all']['visitor'] ?? 0;
        }

        $title = $this->getLang('t_summary');

        $this->accessgraph(
            $title,
            $times,
            [
                $this->getLang('hits'),
                $this->getLang('pages'),
                $this->getLang('media'),
                $this->getLang('visitors'),
            ],
            [$hits, $pages, $media, $visitors]
        );
    }

    /**
     * Show month access by day or hour
     *
     * @param string $by either day or hour
     * @param string $date
     */
    private function monthby($by, $date = '')
    {
        if (!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $times = [];
        $hits = [];
        $pages = [];
        $media = [];
        $visitors = [];

        $keys = array_keys((array)$data['hits'][$by]);
        sort($keys);
        foreach ($keys as $idx) {
            $times[] = $idx;
            $pages[] = $data['page'][$by][$idx]['count'];
            $media[] = $data['media'][$by][$idx]['count'];
            $hits[] = $data['hits'][$by][$idx]['count'];
            $visitors[] = $data['hits'][$by][$idx]['visitor'];
        }

        $title = sprintf($this->getLang('t_' . $by), $date);

        $this->accessgraph(
            $title,
            $times,
            [
                $this->getLang('hits'),
                $this->getLang('pages'),
                $this->getLang('media'),
                $this->getLang('visitors'),
            ],
            [$hits, $pages, $media, $visitors]
        );
    }

    /**
     * Show month traffic by day or hour
     *
     * @param string $by either day or hour
     * @param string $date
     */
    private function trafficby($by, $date = '')
    {
        if (!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $times = [];
        $hits = [];
        $pages = [];
        $media = [];

        foreach (array_keys((array)$data['hits'][$by]) as $idx) {
            $times[] = $idx;
            $pages[] = $data['page'][$by][$idx]['bytes'] / 1024;
            $media[] = $data['media'][$by][$idx]['bytes'] / 1024;
            $hits[] = $data['hits'][$by][$idx]['bytes'] / 1024;
        }

        $title = 'Traffic';

        $this->accessgraph(
            $title,
            $times,
            [
                $this->getLang('all'),
                $this->getLang('pages'),
                $this->getLang('media'),
            ],
            [$hits, $pages, $media]
        );
    }

    /**
     * @param string $date month to display
     */
    private function userdownloads($date)
    {
        $usertraffic = $this->log->usertraffic($date);
        if (!$usertraffic) $this->nograph($this->getLang('t_usertraffic') . ': no data');

        $usertraffic = array_map(fn($in) => $in / 1024 / 1024, $usertraffic);

        // get work day average
        if ($usertraffic !== []) {
            $avg = $this->log->avg($usertraffic);
            // $avg = $avg / 7 *5; //work day average
        } else {
            $avg = 0;
        }
        arsort($usertraffic); // highest first

        // limit number of users shown
        $maxusers = 10;
        if (count($usertraffic) > $maxusers + 1) {
            $others = array_slice($usertraffic, $maxusers);
            $usertraffic = array_slice($usertraffic, 0, $maxusers);

            $other = 0;
            foreach ($others as $traffic) {
                $other += $traffic;
            }

            $usertraffic[sprintf($this->getLang('others'), count($others))] = $other;
        }

        // prepare the graph datasets
        $data = new Data();
        $data->addPoints(array_values($usertraffic), 'traffic');

        // setup axis
        $data->addPoints(array_keys($usertraffic), 'names');
        $data->setAbscissa('names');

        // create the bar graph
        $image = new Image(600, 300, $data);

        $image->setFontProperties(['FontName' => self::FONT, 'FontSize' => 8]);
        $image->setGraphArea(50, 40, 580, 200);
        $image->drawScale([
            'Mode' => SCALE_MODE_START0,
            'LabelRotation' => 45,
            'GridR' => 200, 'GridG' => 200, 'GridB' => 200,
        ]);

        $image->drawBarChart();

        $image->drawThreshold($avg, ['R' => 128, 'G' => 0, 'B' => 0, 'Ticks' => 2]);

        $image->setFontProperties(['FontName' => self::FONT, 'FontSize' => 11]);
        $image->drawText(300, 15, $this->getLang('t_usertraffic') . ' (MB)', ['Align' => TEXT_ALIGN_TOPMIDDLE]);

        $image->render(null);
    }

    /**
     * Draws a line or bargraph depending on the number of data points
     *
     * @param string $title the graph's title
     * @param array $axis the axis points
     * @param array $labels the labels for the datasets
     * @param array $datasets any number of data arrays
     */
    private function accessgraph($title, $axis, $labels, $datasets)
    {
        if (!count($axis)) {
            $this->nograph($title . ': no data');
            return;
        }

        // add the data and labels
        $data = new Data();
        foreach ($datasets as $num => $set) {
            $data->addPoints($set, "series$num");
            $data->setSerieDescription("series$num", $labels[$num]);
        }

        // setup axis
        $data->addPoints($axis, 'times');
        $data->setAbscissa('times');

        $image = new Image(600, 300, $data);
        $usebargraph = (count($axis) < 10);

        $image->setFontProperties(['FontName' => self::FONT, 'FontSize' => 8]);
        $image->setGraphArea(50, 40, 500, 200);
        $image->drawScale([
            'Mode' => SCALE_MODE_START0,
            'LabelRotation' => 45,
            'GridR' => 200, 'GridG' => 200, 'GridB' => 200,
        ]);

        if ($usebargraph) {
            $image->drawBarChart();
        } else {
            $image->drawLineChart();
        }
        $image->drawLegend(505, 40, ['Style' => LEGEND_NOBORDER, 'Mode' => LEGEND_VERTICAL]);

        $image->setFontProperties(['FontName' => self::FONT, 'FontSize' => 11]);
        $image->drawText(300, 15, $title, ['Align' => TEXT_ALIGN_TOPMIDDLE]);

        $image->render(null);
    }

    /**
     * Just creates a message
     *
     * @param $title
     */
    private function nograph($title)
    {
        $image = new Image(300, 40);
        $image->setFontProperties(['FontName' => self::FONT, 'FontSize' => 10, 'R' => 128, 'G' => 0, 'B' => 0]);
        $image->drawText(150, 20, $title, ['Align' => TEXT_ALIGN_MIDDLEMIDDLE]);
        $image->render(null);
        exit;
    }
}

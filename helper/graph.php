<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * statdisplay plugin graph helper component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class helper_plugin_statdisplay_graph extends DokuWiki_Plugin {
    /** @var helper_plugin_statdisplay_log */
    private $log = null;

    /**
     * Outputs a Graph image
     *
     * @param  string $command
     * @param  string $from
     * @param  string $to
     * @return void
     */
    public function sendgraph($command, $from = '', $to = '') {
        require dirname(__FILE__).'/../pchart/pData.php';
        require dirname(__FILE__).'/../pchart/pChart.php';
        require dirname(__FILE__).'/../pchart/GDCanvas.php';
        require dirname(__FILE__).'/../pchart/PieChart.php';

        $this->log = plugin_load('helper', 'statdisplay_log');

        header('Content-Type: image/png');
        switch($command) {
            case 'all':
                $this->summary();
                break;
            case 'month by day':
                $this->monthby('day', $from);
                break;
            case 'month by hour':
                $this->monthby('hour', $from);
                break;
            case 'traffic by day':
                $this->trafficby('day', $from);
                break;
            case 'traffic by hour':
                $this->trafficby('hour', $from);
                break;
            case 'traffic by user':
                $this->userdownloads($from);
                break;
            default:
                $this->nograph('No such graph: '.$command);
                break;
        }
    }

    /**
     * Show all the access data
     *
     * @param string $from
     * @param string $to
     * @return void
     */
    private function summary($from = '', $to = '') {
        $times    = array();
        $hits     = array();
        $pages    = array();
        $media    = array();
        $visitors = array();

        foreach($this->log->logdata as $month => $data) {
            if($month{0} == '_') continue;
            if($from && $month < $from) continue;
            if($to && $month > $to) break;

            $times[]    = $month;
            $pages[]    = $data['page']['all']['count'];
            $media[]    = $data['media']['all']['count'];
            $hits[]     = $data['hits']['all']['count'];
            $visitors[] = $data['hits']['all']['visitor'];
        }

        $title = $this->getLang('t_summary');

        $this->accessgraph(
            $title,
            $times,
            array(
                 $this->getLang('hits'),
                 $this->getLang('pages'),
                 $this->getLang('media'),
                 $this->getLang('visitors'),
            ),
            array($hits, $pages, $media, $visitors)
        );
    }

    /**
     * Show month access by day or hour
     *
     * @param string $by  either day or hour
     * @param string $date
     */
    private function monthby($by, $date = '') {
        if(!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $times    = array();
        $hits     = array();
        $pages    = array();
        $media    = array();
        $visitors = array();

        foreach(array_keys((array) $data['hits'][$by]) as $idx) {
            $times[]    = $idx;
            $pages[]    = $data['page'][$by][$idx]['count'];
            $media[]    = $data['media'][$by][$idx]['count'];
            $hits[]     = $data['hits'][$by][$idx]['count'];
            $visitors[] = $data['hits'][$by][$idx]['visitor'];
        }

        $title = sprintf($this->getLang('t_'.$by), $date);

        $this->accessgraph(
            $title,
            $times,
            array(
                 $this->getLang('hits'),
                 $this->getLang('pages'),
                 $this->getLang('media'),
                 $this->getLang('visitors'),
            ),
            array($hits, $pages, $media, $visitors)
        );
    }

    /**
     * Show month traffic by day or hour
     *
     * @param string $by  either day or hour
     * @param string $date
     */
    private function trafficby($by, $date = '') {
        if(!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $times = array();
        $hits  = array();
        $pages = array();
        $media = array();

        foreach(array_keys((array) $data['hits'][$by]) as $idx) {
            $times[] = $idx;
            $pages[] = $data['page'][$by][$idx]['bytes'] / 1024;
            $media[] = $data['media'][$by][$idx]['bytes'] / 1024;
            $hits[]  = $data['hits'][$by][$idx]['bytes'] / 1024;
        }

        $title = 'Traffic';

        $this->accessgraph(
            $title,
            $times,
            array(
                 $this->getLang('all'),
                 $this->getLang('pages'),
                 $this->getLang('media'),
            ),
            array($hits, $pages, $media)
        );
    }

    /**
     * @param string $date month to display
     */
    private function userdownloads($date) {
        $usertraffic = $this->log->usertraffic($date);
        if(!$usertraffic) $this->nograph($this->getLang('t_usertraffic').': no data');

        $tomb = create_function('$in', 'return $in / 1024 /1024;');
        $usertraffic = array_map($tomb, $usertraffic);

        // get work day average
        if(count($usertraffic)){
            $avg = $this->log->avg($usertraffic);
            // $avg = $avg / 7 *5; //work day average
        }else{
            $avg = 0;
        }
        arsort($usertraffic); // highest first

        // limit number of users shown
        $maxusers = 10;
        if(count($usertraffic) > $maxusers+1){
            $others = array_slice($usertraffic, $maxusers);
            $usertraffic = array_slice($usertraffic, 0 ,$maxusers);

            $other = 0;
            foreach($others as $user => $traffic){
                $other += $traffic;
            }

            $usertraffic[sprintf($this->getLang('others'), count($others))] = $other;
        }

        // prepare the graph datasets
        $DataSet = new pData();
        $DataSet->addPoints(array_values($usertraffic), "traffic");

        // setup axis
        $DataSet->AddPoints(array_keys($usertraffic), 'names');
        $DataSet->AddAllSeries();
        $DataSet->SetAbscissaLabelSeries('names');
        $DataSet->removeSeries('names');
        $DataSet->removeSeriesName('names');


        // create the bar graph
        $Canvas = new GDCanvas(600, 300, false);
        $Chart  = new pChart(600, 300, $Canvas);

        $Chart->setFontProperties(dirname(__FILE__).'/../pchart/Fonts/DroidSans.ttf', 8);
        $Chart->setGraphArea(50, 40, 600, 200);
        $Chart->drawScale(
            $DataSet, new ScaleStyle(SCALE_NORMAL, new Color(127)),
            45, 1, true
        );

        $Chart->drawBarGraph($DataSet->GetData(), $DataSet->GetDataDescription());
        //$Chart->drawLegend(500, 40, $DataSet->GetDataDescription(), new Color(250));

        $Chart->drawTreshold($avg, new Color(128,0,0));

        $Chart->setFontProperties(dirname(__FILE__).'/../pchart/Fonts/DroidSans.ttf', 12);
        $Chart->drawTitle(10, 10, $this->getLang('t_usertraffic').' (MB)', new Color(0), 590, 30);

        $Chart->Render(null);

    }

    /**
     * Draws a line or bargraph depending on the number of data points
     *
     * @param string $title the graph's title
     * @param array  $axis the axis points
     * @param array  $labels the labels for the datasets
     * @param array  $datasets any number of data arrays
     */
    private function accessgraph($title, $axis, $labels, $datasets) {
        if(!count($axis)) {
            $this->nograph($title.': no data');
            return;
        }

        // add the data and labels
        $DataSet = new pData();
        foreach($datasets as $num => $set) {
            $DataSet->AddPoints($set, "series$num");
            $DataSet->SetSeriesName($labels[$num], "series$num");
        }

        // setup axis
        $DataSet->AddPoints($axis, 'times');
        $DataSet->AddAllSeries();
        $DataSet->SetAbscissaLabelSeries('times');
        $DataSet->removeSeries('times');
        $DataSet->removeSeriesName('times');

        $Canvas      = new GDCanvas(600, 300, false);
        $Chart       = new pChart(600, 300, $Canvas);
        $usebargraph = (count($axis) < 10);

        $Chart->setFontProperties(dirname(__FILE__).'/../pchart/Fonts/DroidSans.ttf', 8);
        $Chart->setGraphArea(50, 40, 500, 200);
        $Chart->drawScale(
            $DataSet, new ScaleStyle(SCALE_NORMAL, new Color(127)),
            45, 1, $usebargraph
        );

        if($usebargraph) {
            $Chart->drawBarGraph($DataSet->GetData(), $DataSet->GetDataDescription());
        } else {
            $Chart->drawLineGraph($DataSet->GetData(), $DataSet->GetDataDescription());
        }
        $Chart->drawLegend(500, 40, $DataSet->GetDataDescription(), new Color(250));

        $Chart->setFontProperties(dirname(__FILE__).'/../pchart/Fonts/DroidSans.ttf', 12);
        $Chart->drawTitle(10, 10, $title, new Color(0), 590, 30);

        $Chart->Render(null);
    }

    /**
     * Just creates a message
     *
     * @param $title
     */
    private function nograph($title) {
        $Canvas = new GDCanvas(300, 40, false);
        $Chart  = new pChart(300, 40, $Canvas);
        $Chart->setFontProperties(dirname(__FILE__).'/../pchart/Fonts/DroidSans.ttf', 10);
        $Chart->drawTitle(0, 0, $title, new Color(128, 0, 0), 300, 40);
        $Chart->Render(null);
        exit;
    }
}

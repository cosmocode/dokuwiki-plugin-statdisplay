<?php

class helper_plugin_statdisplay_graph extends DokuWiki_Plugin {
    /** @var helper_plugin_statdisplay_log */
    private $log = null;

    /**
     * Outputs a Graph image
     *
     * @param        $command
     * @param string $date
     */
    public function sendgraph($command, $date = '') {
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
                $this->monthby('day');
                break;
            case 'month by hour':
                $this->monthby('hour');
                break;
            case 'traffic by day':
                $this->trafficby('day');
                break;
            case 'traffic by hour':
                $this->trafficby('hour');
                break;
            default:
                echo file_get_contents(dirname(__FILE__).'/../nope.png');
                break;
        }
    }

    private function summary() {
        $times    = array();
        $hits     = array();
        $pages    = array();
        $media    = array();
        $visitors = array();

        foreach($this->log->logdata as $month => $data) {
            if($month{0} == '_') continue;
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

    private function accessgraph($title, $axis, $labels, $datasets) {
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

}

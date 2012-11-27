<?php

class helper_plugin_statdisplay_table extends DokuWiki_Plugin {
    /** @var helper_plugin_statdisplay_log */
    private $log = null;

    /** @var Doku_Renderer */
    private $R = null;

    /**
     * @param Doku_Renderer  $R
     * @param string         $command  type of statistic
     * @param string         $from     restrict to this month
     * @param string         $to       end interval
     * @return void
     */
    public function table($R, $command, $from = '', $to='') {
        $this->R   = $R;
        $this->log = plugin_load('helper', 'statdisplay_log');

        switch($command) {
            case 'all':
                $this->summary($from, $to);
                break;
            case 'one month':
                $this->month($from);
                break;
            case 'month by day':
                $this->monthby('day', $from);
                break;
            case 'month by hour':
                $this->monthby('hour', $from);
                break;
            case 'top referers':
                $this->referer($from);
                break;
            case 'top entries':
                $this->entry($from);
                break;
            case 'top urls':
                $this->url($from);
                break;
            case 'top bytes':
                break;
            case 'user agents':
                $this->ua($from);
                break;
            case 'progress bar':
                $this->progress();
                break;
            default:
                $R->cdata('No such table: '.$command);

        }
    }

    private function progress(){
        $pct = sprintf('%.2f', $this->log->progress());
        $this->R->doc .= '<div class="statdisplay-progress" title="'.$pct.'%"><span style="width: '.$pct.'%"></span></div>';
    }

    /**
     * Print referers for a given month
     *
     * @param string $date
     */
    private function referer($date=''){
        if(!$date) $date = date('Y-m');
        $this->listtable($this->log->logdata[$date]['referer_url'],
                         $this->log->logdata[$date]['referer']['count'],
                         'Top Referrers in '.$date);
    }

    /**
     * Print top entry pages for a given month
     *
     * @param string $date
     */
    private function entry($date=''){
        if(!$date) $date = date('Y-m');
        $this->listtable($this->log->logdata[$date]['entry'],
                         $this->log->logdata[$date]['page']['all']['count'],
                         'Top Entry Pages in '.$date);
    }

    /**
     * Print top user agents for a given month
     *
     * @param string $date
     */
    private function ua($date=''){
        if(!$date) $date = date('Y-m');
        $this->listtable($this->log->logdata[$date]['useragent'],
                         $this->log->logdata[$date]['page']['all']['count'],
                         'Top User Agents in '.$date);
    }

    /**
     * Print top pages for a given month
     *
     * @param string $date
     */
    private function url($date=''){
        if(!$date) $date = date('Y-m');
        $this->listtable($this->log->logdata[$date]['page_url'],
                         $this->log->logdata[$date]['page']['all']['count'],
                         'Top Pages in '.$date);
    }

    /**
     * Print daily or hourly statistics
     *
     * @param string $by either 'day' or 'hour'
     * @param string $date
     */
    private function monthby($by, $date = ''){
        if(!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $title = sprintf($this->getLang('t_'.$by), $date);

        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head($title, 11);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head($this->getLang('day'));
        $this->head($this->getLang('hits'),2);
        $this->head($this->getLang('media'),2);
        $this->head($this->getLang('pages'),2);
        $this->head($this->getLang('visitors'),2);
        $this->head($this->getLang('traffic'),2);
        $this->R->tablerow_close();

        foreach(array_keys((array) $data['hits'][$by]) as $idx){
            $this->R->tablerow_open();
            $this->hcell($idx);

            $this->cell($data['hits'][$by][$idx]['count']);
            $this->cell($this->pct($data['hits'][$by][$idx]['count'], $data['hits']['all']['count']));

            $this->cell($data['media'][$by][$idx]['count']);
            $this->cell($this->pct($data['media'][$by][$idx]['count'], $data['media']['all']['count']));

            $this->cell($data['page'][$by][$idx]['count']);
            $this->cell($this->pct($data['page'][$by][$idx]['count'], $data['page']['all']['count']));

            $this->cell($data['hits'][$by][$idx]['visitor']);
            $this->cell($this->pct($data['hits'][$by][$idx]['visitor'], $data['hits']['all']['visitor']));

            $this->cell(filesize_h($data['hits'][$by][$idx]['bytes']));
            $this->cell($this->pct($data['hits'][$by][$idx]['bytes'], $data['hits']['all']['bytes']));

            $this->R->tablerow_close();
        }


        $this->R->table_close();
    }

    /**
     * print a single month
     *
     * @param $date
     */
    private function month($date = '') {
        if(!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head('Monthly Statistics for '.$date, 3);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Total hits');
        $this->cell($data['page']['all']['count'] + $data['media']['all']['count'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Total files');
        $this->cell($data['media']['all']['count'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Total pages');
        $this->cell($data['page']['all']['count'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Total visitors');
        $this->cell($data['page']['all']['visitor'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Total bytes');
        $this->cell(filesize_h($data['page']['all']['bytes']), 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('');
        $this->head('avg');
        $this->head('max');
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Hits per hour');
        $this->cell($this->log->avg($data['hits']['hour'], 'count'));
        $this->cell($this->log->max($data['hits']['hour'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Hits per day');
        $this->cell($this->log->avg($data['hits']['day'], 'count'));
        $this->cell($this->log->max($data['hits']['day'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Files per day');
        $this->cell($this->log->avg($data['media']['day'], 'count'));
        $this->cell($this->log->max($data['media']['day'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Pages per day');
        $this->cell($this->log->avg($data['page']['day'], 'count'));
        $this->cell($this->log->max($data['page']['day'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell('Bytes per day');
        $this->cell(filesize_h($this->log->avg($data['hits']['day'], 'bytes')));
        $this->cell(filesize_h($this->log->max($data['hits']['day'], 'bytes')));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('Hits by Status Code',3);
        $this->R->tablerow_close();

        foreach((array) $this->log->logdata[$date]['status']['all'] as $code => $count ){
            $this->R->tablerow_open();
            $this->hcell('Status '.$code.' - '.$this->getLang('status_'.$code));
            $this->cell($count, 2);
            $this->R->tablerow_close();
        }

        $this->R->table_close();
    }

    /**
     * print the whole summary table
     */
    private function summary($from='', $to='') {
        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head('Summary by Month', 10);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('Month', 1, 2);
        $this->head('Daily Average', 4);
        $this->head('Monthly Totals', 5);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('Hits');
        $this->head('Files');
        $this->head('Pages');
        $this->head('Visitors');
        $this->head('Bytes');
        $this->head('Visitors');
        $this->head('Pages');
        $this->head('Files');
        $this->head('Hits');
        $this->R->tablerow_close();

        foreach($this->log->logdata as $month => $data) {
            if($month{0} == '_') continue;
            if($from && $month < $from) continue;
            if($to && $month > $to) break;

            $this->R->tablerow_open();

            $this->cell($month, 1, false); // Month
            // ---- averages ----
            $this->cell($this->log->avg($data['hits']['day'], 'count')); // Hits
            $this->cell($this->log->avg($data['media']['day'], 'count')); // Files
            $this->cell($this->log->avg($data['page']['day'], 'count')); // Pages
            $this->cell($this->log->avg($data['hits']['day'], 'visitor')); // Visits
            // ---- totals ----
            $this->cell(filesize_h($data['hits']['all']['bytes'])); // kBytes
            $this->cell($data['hits']['all']['visitor']); // Visitors
            $this->cell($data['page']['all']['count']); // Pages
            $this->cell($data['media']['all']['count']); // Files
            $this->cell($data['hits']['all']['count']); // Hits

            $this->R->tablerow_close();
        }

        $this->R->table_close();
    }

    /**
     * Print a simple listing table
     *
     * @param $data
     * @param $max
     * @param $title
     */
    private function listtable(&$data, $max, $title){
        if(!$data) $data = array();

        arsort($data);
        $row = 1;

        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head($title, 4);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('#');
        $this->head('Name');
        $this->head('Hits', 2);
        $this->R->tablerow_close();


        foreach($data as $key => $count){
            $this->R->tablerow_open();
            $this->cell($row);
            $this->hcell($key);
            $this->cell($count);
            $this->cell($this->pct($count, $max));
            $this->R->tablerow_close();
            $row++;
            if($row > $this->log->top_limit) break;
        }

        $this->R->table_close();
    }

    /**
     * Calculate and format a percent value
     *
     * @param $val
     * @param $max
     * @return string
     */
    private function pct($val, $max){
        if(!$max) return '0.00%';

        return sprintf("%.2f%%", $val*100/$max);
    }

    /**
     * print a table header cell
     *
     * @param string $data
     * @param int    $col
     * @param int    $row
     */
    private function head($data = '', $col = 1, $row = 1) {
        $this->R->tableheader_open($col, 'center', $row);
        $this->R->cdata($data);
        $this->R->tablecell_close();
    }

    /**
     * print a non numeric data cell
     *
     * @param string $data
     * @param int    $span
     */
    private function hcell($data = '', $span = 1) {
        $this->cell($data, $span, false);
    }

    /**
     * print a numeric data cell
     *
     * @param string $data
     * @param int    $span
     * @param bool   $number
     */
    private function cell($data = '', $span = 1, $number = true) {
        if($number) {
            if(!$data) $data = 0;
            $align = 'right';
        } else {
            $align = null;
        }

        $this->R->tablecell_open($span, $align);
        $this->R->cdata($data);
        $this->R->tablecell_close();
    }
}

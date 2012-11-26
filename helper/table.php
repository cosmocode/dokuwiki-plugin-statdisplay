<?php

class helper_plugin_statdisplay_table extends DokuWiki_Plugin {
    /** @var helper_plugin_statdisplay_log */
    private $log = null;

    /** @var Doku_Renderer */
    private $R = null;

    /**
     * @param Doku_Renderer  $R
     * @param string         $command  type of statistic
     * @param string         $date     restrict to this month
     * @return void
     */
    public function table($R, $command, $date = '') {
        $this->R   = $R;
        $this->log = plugin_load('helper', 'statdisplay_log');

        switch($command) {
            case 'all':
                $this->summary();
                break;
            case 'one month':
                $this->month();
                break;
            case 'month by day':
                $this->monthby('day');
                break;
            case 'month by hour':
                $this->monthby('hour');
                break;
            case 'top referers':
                break;
            case 'top entries':
                break;
            case 'top urls':
                break;
            case 'top bytes':
                break;
            case 'user agents':
                break;

            default:
                $R->cdata('Unknown statistic '.$command);

        }
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

        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head($by.'ly Statistics for '.$date, 11); //FIXME
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('Day');
        $this->head('Hits',2);
        $this->head('Files',2);
        $this->head('Pages',2);
        $this->head('Visits',2);
        $this->head('Bytes',2);
        $this->R->tablerow_close();

        foreach(array_keys($data['hits'][$by]) as $idx){
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

        foreach($this->log->logdata[$date]['status']['all'] as $code => $count ){
            $this->R->tablerow_open();
            $this->hcell('Status '.$code);
            $this->cell($count, 2);
            $this->R->tablerow_close();
        }


        dbg($this->log->logdata);
        $this->R->table_close();
    }

    /**
     * print the whole summary table
     */
    private function summary() {
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

        foreach($this->log->logdata as $ym => $data) {
            if($ym{0} == '_') continue;

            $this->R->tablerow_open();

            $this->cell($ym, 1, false); // Month
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

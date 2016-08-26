<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * statdisplay plugin table helper component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
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
    public function table($R, $command, $from = '', $to = '') {
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
            case 'traffic by user':
                $this->userdownloads($from);
                break;
            default:
                $R->cdata('No such table: '.$command);

        }
    }

    private function progress() {
        $pct = sprintf('%.2f', $this->log->progress());
        $this->R->doc .= '<div class="statdisplay-progress" title="'.$pct.'%"><span style="width: '.$pct.'%"></span></div>';
    }

    /**
     * Print referers for a given month
     *
     * @param string $date
     */
    private function referer($date = '') {
        if(!$date) $date = date('Y-m');
        $this->listtable(
            $this->log->logdata[$date]['referer_url'],
            $this->log->logdata[$date]['referer']['count'],
            sprintf($this->getLang('t_topReferrer'), $date)
        );
    }

    /**
     * Print top entry pages for a given month
     *
     * @param string $date
     */
    private function entry($date = '') {
        if(!$date) $date = date('Y-m');
        $this->listtable(
            $this->log->logdata[$date]['entry'],
            $this->log->logdata[$date]['page']['all']['count'],
            sprintf($this->getLang('t_topEntry'), $date)
        );
    }

    /**
     * Print top user agents for a given month
     *
     * @param string $date
     */
    private function ua($date = '') {
        if(!$date) $date = date('Y-m');
        $this->listtable(
            $this->log->logdata[$date]['useragent'],
            $this->log->logdata[$date]['page']['all']['count'],
            sprintf($this->getLang('t_topUserAgents'), $date)
        );
    }

    /**
     * Print top pages for a given month
     *
     * @param string $date
     */
    private function url($date = '') {
        if(!$date) $date = date('Y-m');
        $this->listtable(
            $this->log->logdata[$date]['page_url'],
            $this->log->logdata[$date]['page']['all']['count'],
            sprintf($this->getLang('t_topPages'), $date)
        );
    }

    /**
     * Print daily or hourly statistics
     *
     * @param string $by either 'day' or 'hour'
     * @param string $date
     */
    private function monthby($by, $date = '') {
        if(!$date) $date = date('Y-m');
        $data = $this->log->logdata[$date];

        $title = sprintf($this->getLang('t_'.$by), $date);

        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head($title, 11);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head($this->getLang($by));
        $this->head($this->getLang('hits'), 2);
        $this->head($this->getLang('media'), 2);
        $this->head($this->getLang('pages'), 2);
        $this->head($this->getLang('visitors'), 2);
        $this->head($this->getLang('traffic'), 2);
        $this->R->tablerow_close();

        foreach(array_keys((array) $data['hits'][$by]) as $idx) {
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
        $this->head(sprintf($this->getLang('t_statisticMonth'), $date), 3);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('totalHits'));
        $this->cell($data['page']['all']['count'] + $data['media']['all']['count'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('totalFiles'));
        $this->cell($data['media']['all']['count'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('totalPages'));
        $this->cell($data['page']['all']['count'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('totalVisitors'));
        $this->cell($data['page']['all']['visitor'], 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('totalBytes'));
        $this->cell(filesize_h($data['page']['all']['bytes']), 2);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('');
        $this->head($this->getLang('avg'));
        $this->head($this->getLang('max'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('hitsHour'));
        $this->cell($this->log->avg($data['hits']['hour'], 'count'));
        $this->cell($this->log->max($data['hits']['hour'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('hitsDay'));
        $this->cell($this->log->avg($data['hits']['day'], 'count'));
        $this->cell($this->log->max($data['hits']['day'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('filesDay'));
        $this->cell($this->log->avg($data['media']['day'], 'count'));
        $this->cell($this->log->max($data['media']['day'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('pagesDay'));
        $this->cell($this->log->avg($data['page']['day'], 'count'));
        $this->cell($this->log->max($data['page']['day'], 'count'));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->hcell($this->getLang('bytesDay'));
        $this->cell(filesize_h($this->log->avg($data['hits']['day'], 'bytes')));
        $this->cell(filesize_h($this->log->max($data['hits']['day'], 'bytes')));
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head($this->getLang('hitsStatusCode'), 3);
        $this->R->tablerow_close();

        foreach((array) $this->log->logdata[$date]['status']['all'] as $code => $count) {
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
    private function summary($from = '', $to = '') {
        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head($this->getLang('summaryMonth'), 10);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head($this->getLang('month'), 1, 2);
        $this->head($this->getLang('dailyavg'), 4);
        $this->head($this->getLang('totals'), 5);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head($this->getLang('hits'));
        $this->head($this->getLang('files'));
        $this->head($this->getLang('pages'));
        $this->head($this->getLang('visitors'));
        $this->head($this->getLang('hits'));
        $this->head($this->getLang('files'));
        $this->head($this->getLang('pages'));
        $this->head($this->getLang('visitors'));
        $this->head($this->getLang('bytes'));

        $this->R->tablerow_close();

        foreach((array) $this->log->logdata as $month => $data) {
            if($month{0} == '_') continue;
            if($from && $month < $from) continue;
            if($to && $month > $to) break;

            $this->R->tablerow_open();

            $this->cell($month, 1, false); // Month
            // ---- averages ----
            $this->cell(round($this->log->avg($data['hits']['day'], 'count'))); // Hits
            $this->cell(round($this->log->avg($data['media']['day'], 'count'))); // Files
            $this->cell(round($this->log->avg($data['page']['day'], 'count'))); // Pages
            $this->cell(round($this->log->avg($data['hits']['day'], 'visitor'))); // Visits
            // ---- totals ----
            $this->cell($data['hits']['all']['count']); // Hits
            $this->cell($data['media']['all']['count']); // Files
            $this->cell($data['page']['all']['count']); // Pages
            $this->cell($data['hits']['all']['visitor']); // Visitors
            $this->cell(filesize_h($data['hits']['all']['bytes'])); // kBytes

            $this->R->tablerow_close();
        }

        $this->R->table_close();
    }

    /**
     * @param string $date month to display
     */
    private function userdownloads($date) {
        $usertraffic = $this->log->usertraffic($date);

        $this->listtable($usertraffic, $this->log->sum($usertraffic), $this->getLang('t_usertraffic'), true);
    }

    /**
     * Print a simple listing table
     *
     * @param array  $data
     * @param float  $max
     * @param string $title
     * @param bool   $istraffic
     * @return void
     */
    private function listtable(&$data, $max, $title, $istraffic=false) {
        if(!$data) $data = array();

        arsort($data);
        $row = 1;

        $this->R->table_open();

        $this->R->tablerow_open();
        $this->head($title, 4);
        $this->R->tablerow_close();

        $this->R->tablerow_open();
        $this->head('#');
        $this->head($this->getLang('name'));
        if($istraffic){
            $this->head($this->getLang('traffic'), 2);
        }else{
            $this->head($this->getLang('hits'), 2);
        }
        $this->R->tablerow_close();

        foreach($data as $key => $count) {
            if($istraffic){
                $val = filesize_h($count);
            }else{
                $val = $count;
            }

            $this->R->tablerow_open();
            $this->cell($row);
            $this->hcell($key);
            $this->cell($val);
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
    private function pct($val, $max) {
        if(!$max) return '0.00%';

        return sprintf("%.2f%%", $val * 100 / $max);
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
        $this->R->tableheader_close();
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

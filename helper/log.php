<?php

class helper_plugin_statdisplay_log extends DokuWiki_Plugin {
    public $logdata = array();
    private $logcache = '';
    private $logfile = '';

    /**
     * Constructor
     *
     * Loads the cache
     */
    public function __construct() {
        $this->logfile =  fullpath(DOKU_INC . $this->getConf('accesslog'));
        // file not found? assume absolute path
        if(!file_exists($this->logfile)) $this->logfile = $this->getConf('accesslog');

        // load the cache file
        $this->logcache = getCacheName($this->logfile, '.statdisplay');
        if(file_exists($this->logcache)) {
            $this->logdata = unserialize(io_readFile($this->logcache, false));
        }
    }

    /**
     * Parses the next chunk of logfile into our memory structure
     */
    public function parseLogData() {
        // continue from last position
        $size = filesize($this->logfile);
        if(!$size) return 0;

        $pos = 0;
        if(isset($this->logdata['_logpos'])) $pos = $this->logdata['_logpos'];
        if($pos > $size) $pos = 0;
        if($pos && $size-$pos < 30000) return 0; // we want at least 30k of log data

        // open handle
        $fh = fopen($this->logfile, 'r');
        if(!$fh) return 0;
        fseek($fh, $pos, SEEK_SET);

        $lines             = 0;
        $max_lines_per_run = 500;

        // read lines
        while(feof($fh) == 0 && $lines < $max_lines_per_run) {
            $line = fgets($fh);
            $lines++;
            $pos += strlen($line);

            if($line == '') continue;

            $parts = explode(' ', $line);
            $date  = strtotime(trim($parts[3].' '.$parts[4], '[]'));

            $month = date('Y-m', $date);
            $day   = date('d', $date);
            $hour  = date('G', $date);
            list($url) = explode('?', $parts[6]); // strip GET vars
            $status = $parts[8];
            $size   = $parts[9];

            if($status == 200) {
                $thistype = (substr($url, 0, 8) == '/_media/') ? 'media' : 'page';
                if($thistype == 'page'){
                    // for analyzing webserver logs we consider all known extensions as media files
                    list($ext) = mimetype($url);
                    if($ext !== false) $thistype = 'media';
                }

                // remember IPs
                $newvisitor = !isset($this->logdata[$month]['ip'][$parts[0]]);
                $this->logdata[$month]['ip'][$parts[0]]++;

                // log type dependent and summarized
                foreach(array($thistype, 'hits') as $type) {
                    // we need these in perfect order
                    if(!isset($this->logdata[$month][$type]['hour']))
                        $this->logdata[$month][$type]['hour'] = array_fill(0, 23, array());

                    $this->logdata[$month][$type]['all']['count']++;
                    $this->logdata[$month][$type]['day'][$day]['count']++;
                    $this->logdata[$month][$type]['hour'][$hour]['count']++;

                    $this->logdata[$month][$type]['all']['bytes'] += $size;
                    $this->logdata[$month][$type]['day'][$day]['bytes'] += $size;
                    $this->logdata[$month][$type]['hour'][$hour]['bytes'] += $size;

                    if($newvisitor) {
                        $this->logdata[$month][$type]['all']['visitor']++;
                        $this->logdata[$month][$type]['day'][$day]['visitor']++;
                        $this->logdata[$month][$type]['hour'][$hour]['visitor']++;
                    }
                }

                if($thistype == 'page') {
                    // referer
                    $referer = trim($parts[10], '"');
                    if(substr($referer, 0, 4) == 'http') {
                        list($referer) = explode('?', $referer);
                        $this->logdata[$month]['referer']['count']++;
                        $this->logdata[$month]['referer_url'][$referer]++;
                    }

                    // entry page
                    if($newvisitor) {
                        $this->logdata[$month]['entry'][$url]++;
                    }

                    // user agent FIXME use browser agent library
                    $ua = trim( join(' ', array_slice($parts,11)), '" ');
                    $this->logdata[$month]['useragent'][$ua]++;
                }
            }else{
                // count non-200 as a hit too
                $this->logdata[$month]['hits']['all']['count']++;
                $this->logdata[$month]['hits']['day'][$day]['count']++;
                $this->logdata[$month]['hits']['hour'][$hour]['count']++;
            }

            $this->logdata[$month]['status']['all'][$status]++;
            $this->logdata[$month]['status']['day'][$day][$status]++;
            $this->logdata[$month]['status']['hour'][$hour][$status]++;
        }
        $this->logdata['_logpos'] = $pos;

        // clean up the last month, freeing memory
        if($this->logdata['_lastmonth'] != $month){
            // FIXME shorten IPs, referers, entry pages, user agents

            $this->logdata['_lastmonth'] = $month;
        }

        // save the data
        io_saveFile($this->logcache, serialize($this->logdata));
        return $lines;
    }

    /**
     * Avarages a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return int
     */
    public function avg($input, $key) {
        $cnt = 0;
        $all = 0;
        foreach((array) $input as $item) {
            $all += $item[$key];
            $cnt++;
        }

        if(!$cnt) return 0;
        return round($all / $cnt);
    }

    /**
     * Gives maximum of a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return int
     */
    public function max($input, $key) {
        $max = 0;
        foreach((array) $input as $item) {
            if($item[$key] > $max) $max = $item[$key];
        }

        return $max;
    }

}

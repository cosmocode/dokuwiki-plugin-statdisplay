<?php
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * statdisplay plugin log helper component
 *
 * @author Andreas Gohr <gohr@cosmocode.de>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
class helper_plugin_statdisplay_log extends DokuWiki_Plugin {
    public $logdata = array();
    private $logcache = '';
    private $logfile = '';

    public $top_limit = 30;

    /**
     * Constructor
     *
     * Loads the cache
     */
    public function __construct() {
        global $conf;
        $this->logfile = fullpath($conf['metadir'].'/'.$this->getConf('accesslog'));
        // file not found? assume absolute path
        if(!file_exists($this->logfile)) $this->logfile = $this->getConf('accesslog');

        // load the cache file
        $this->logcache = getCacheName($this->getConf('accesslog'), '.statdisplay');
        if(file_exists($this->logcache)) {
            $this->logdata = unserialize(io_readFile($this->logcache, false));
        }
    }

    /**
     * Return the progress of the log analysis
     *
     * @return float
     */
    public function progress() {
        $pos = (int) $this->logdata['_logpos'];
        $max = @filesize($this->logfile);
        if(!$max) return 100.0;

        return $pos * 100 / $max;
    }

    /**
     * Parses the next chunk of logfile into our memory structure
     */
    public function parseLogData() {
        $size = filesize($this->logfile);
        if(!$size) return 0;

        // continue from last position
        $pos = 0;
        if(isset($this->logdata['_logpos'])) $pos = $this->logdata['_logpos'];
        if($pos > $size) $pos = 0;
        if($pos && ( ($size - $pos) < ($this->getConf('lines') * 150) )) return 0; // we want to have some minimal log data

        if(!$this->lock()) return 0;

        require_once(dirname(__FILE__).'/../Browser.php');

        // open handle
        $fh = fopen($this->logfile, 'r');
        if(!$fh) return 0;
        fseek($fh, $pos, SEEK_SET);

        // read lines
        $lines = 0;
        while(feof($fh) == 0 && $lines < $this->getConf('lines')) {
            $line = fgets($fh);
            $lines++;
            $pos += strlen($line);

            if($line == '') continue;

            $parts = explode(' ', $line);
            $date  = strtotime(trim($parts[3].' '.$parts[4], '[]'));
            if(!$date) continue;

            $month = date('Y-m', $date);
            $day   = date('d', $date);
            $hour  = date('G', $date);
            list($url) = explode('?', $parts[6]); // strip GET vars
            $status = $parts[8];
            $size   = $parts[9];
            $user   = trim($parts[2], '"-');

            if(!empty($user)){
                $user = $GLOBALS['auth']->cleanUser($user);
            }

            if($status == 200) {
                $thistype = (substr($url, 0, 8) == '/_media/') ? 'media' : 'page';
                if($thistype == 'page') {
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

                    if($user) {
                        $this->logdata[$month]['usertraffic'][$day][$user] += $size;
                    }

                    if($newvisitor) {
                        $this->logdata[$month][$type]['all']['visitor']++;
                        $this->logdata[$month][$type]['day'][$day]['visitor']++;
                        $this->logdata[$month][$type]['hour'][$hour]['visitor']++;
                    }
                }

                // log additional detailed data
                if($thistype == 'page') {
                    // url
                    $this->logdata[$month]['page_url'][$url]++;

                    // referer
                    $referer = trim($parts[10], '"');
                    // skip non valid and local referers
                    if(substr($referer, 0, 4) == 'http' && (strpos($referer, DOKU_URL) !== 0)) {
                        list($referer) = explode('?', $referer);
                        $this->logdata[$month]['referer']['count']++;
                        $this->logdata[$month]['referer_url'][$referer]++;
                    }

                    // entry page
                    if($newvisitor) {
                        $this->logdata[$month]['entry'][$url]++;
                    }

                    // user agent
                    $ua = trim(join(' ', array_slice($parts, 11)), '" ');
                    if($ua) {
                        $ua = $this->ua($ua);
                        $this->logdata[$month]['useragent'][$ua]++;
                    }
                }
            } else {
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
        if(isset($month) && $this->logdata['_lastmonth'] != $month) {
            $this->clean_month($this->logdata['_lastmonth']);
            $this->logdata['_lastmonth'] = $month;
        }

        // save the data
        io_saveFile($this->logcache, serialize($this->logdata));
        $this->unlock();
        return $lines;
    }

    /**
     * Clean up the backlog
     *
     * Shortens IPs, referers, entry pages, user agents etc. to preserve space and memory
     *
     * @param string $month where to clean up
     */
    private function clean_month($month) {
        if(!$month) return;

        foreach(array('ip', 'page_url', 'referer_url', 'entry', 'useragent') as $type) {
            if(is_array($this->logdata[$month][$type])) {
                arsort($this->logdata[$month][$type]);
                $this->logdata[$month][$type] = array_slice($this->logdata[$month][$type], 0, $this->top_limit);
            }
        }
    }

    /**
     * Returns the common user agent name and version as a string
     *
     * @param $useragent
     * @return string
     */
    private function ua($useragent) {
        $ua = new Browser($useragent);
        list($version) = explode('.', $ua->getVersion());
        if(!$version) $version = ''; // no zero version
        if($version == 'unknown') $version = '';
        return trim($ua->getBrowser().' '.$version);
    }

    /**
     * Lock the the analysis process
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    private function lock() {
        global $conf;
        $run  = 0;
        $lock = $conf['lockdir'].'/_statdisplay.lock';
        while(!@mkdir($lock, $conf['dmode'])) {
            usleep(50);
            if(is_dir($lock) && time() - @filemtime($lock) > 60 * 5) {
                // looks like a stale lock - remove it
                @rmdir($lock);
                return false;
            } elseif($run++ == 1000) {
                // we waited 5 seconds for that lock
                return false;
            }
        }
        if($conf['dperm'])
            chmod($lock, $conf['dperm']);
        return true;
    }

    /**
     * Unlock the the analysis process
     *
     * @author Tom N Harris <tnharris@whoopdedo.org>
     */
    private function unlock() {
        global $conf;
        @rmdir($conf['lockdir'].'/_statdisplay.lock');
        return true;
    }

    /**
     * Return the last 7 day's user traffic
     *
     * @param $date
     * @return array
     */
    public function usertraffic($date) {
        if(!$date) $date = date('Y-m');

        $data = $this->logdata[$date]['usertraffic'];
        $data = array_slice((array) $data, -7, 7, true); // limit to seven days

        // add from previous month if needed
        $num = count($data);
        if($num < 7) {
            $data += array_slice((array) $this->logdata[$this->prevmonth($date)]['usertraffic'], -1 * (7 - $num), 7 - $num, true);
        }

        // count up the traffic
        $alltraffic  = 0;
        $usertraffic = array();
        foreach($data as $day => $info) {
            foreach((array) $info as $user => $traffic) {
                $usertraffic[$user] += $traffic;
                $alltraffic += $traffic;
            }
        }
        return $usertraffic;
    }

    /**
     * Gives the sum of a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return int
     */
    public function sum($input, $key=null) {
        $sum = 0;
        foreach((array) $input as $item) {
            if(is_null($key)){
                $val = $item;
            }else{
                $val = $item[$key];
            }
            $sum += $val;
        }

        return $sum;
    }

    /**
     * Avarages a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return float
     */
    public function avg($input, $key=null) {
        $cnt = 0;
        $all = 0;
        foreach((array) $input as $item) {
            if(is_null($key)){
                $all += $item;
            }else{
                $all += $item[$key];
            }
            $cnt++;
        }

        if(!$cnt) return 0;
        return $all / $cnt;
    }

    /**
     * Gives maximum of a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return int
     */
    public function max($input, $key=null) {
        $max = 0;
        foreach((array) $input as $item) {
            if(is_null($key)){
                $val = $item;
            }else{
                $val = $item[$key];
            }

            if($val > $max) $max = $val;
        }

        return $max;
    }

    /**
     * return the month before the given month
     *
     * @param $date
     * @return string
     */
    private function prevmonth($date) {
        list($year, $month) = explode('-', $date);
        $month = $month - 1;
        if($month < 1) {
            $year  = $year - 1;
            $month = 12;
        }
        return sprintf("%d-%02d", $year, $month);
    }

}

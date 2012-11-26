<?php

class helper_plugin_statdisplay_log extends DokuWiki_Plugin {
    public  $logdata = array();
    private $logcache = '';

    /**
     * Constructor
     *
     * Loads the cache
     */
    public function __construct() {
        $this->logcache = getCacheName($this->getConf('accesslog'), '.statdisplay');

        // load the cache file
        if(file_exists($this->logcache)) {
            $this->logdata = unserialize(io_readFile($this->logcache, false));
        }
    }

    public function parseLogData() {
        // open handle
        $fh = fopen($this->getConf('accesslog'), 'r');
        if(!$fh) return;

        // continue from last position
        $pos = 0;
        if(isset($this->logdata['_logpos'])) $pos = $this->logdata['_logpos'];
        if($pos > filesize($this->getConf('accesslog'))) $pos = 0;
        fseek($fh, $pos, SEEK_SET);

        $lines             = 0;
        $max_lines_per_run = 200;

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
            $hour  = date('H', $date);
            list($url) = explode('?', $parts[6]); // strip GET vars
            $status = $parts[8];
            $size   = $parts[9];

            if($status == 200) {
                $thistype = (substr($url, 0, 8) == '/_media/') ? 'media' : 'page';

                // log type dependent and summarized
                foreach(array($thistype, 'hits') as $type){
                    $this->logdata[$month][$type]['all']['count']++;
                    $this->logdata[$month][$type]['day'][$day]['count']++;
                    $this->logdata[$month][$type]['hour'][$hour]['count']++;

                    $this->logdata[$month][$type]['all']['bytes'] += $size;
                    $this->logdata[$month][$type]['day'][$day]['bytes'] += $size;
                    $this->logdata[$month][$type]['hour'][$hour]['bytes'] += $size;
                }
            }

            $this->logdata[$month]['status']['all'][$status] ++;
            $this->logdata[$month]['status']['day'][$day][$status] ++;
            $this->logdata[$month]['status']['hour'][$hour][$status] ++;

        }

        $this->logdata['_logpos'] = $pos;

        // save the data
        io_saveFile($this->logcache, serialize($this->logdata));

        /*

            //Bloc gestion des visites & pages d'entree
            $tmp = date_to_timestamp($date_string);
            if(!isset($ip[$parts[0]]) || ($ip[$parts[0]] + $duree_visite) < $tmp) {
                $this->logcache[$month]['jour']['visits'][$date[0]]++;
                $this->logcache[$month]['resume']['visits']++;
                $this->logcache[$month]['heure']['visits'][intval($hour[1])]++;
                if($this->logcache[$month]['url']['is_page'][$parts[6]]) {
                    $this->logcache[$month]['entree'][$parts[6]]++;
                    $this->logcache[$month]['resume']['entree']++;
                }
                $ip[$parts[0]] = $tmp;
            }
            $_SESSION['last_visit'] = $tmp;

            if(strstr($parts[10], '"http') != NULL) //Referrers
            {
                $parts[10] = trim($parts[10], '"');
                $tmp       = explode('?', $parts[10]);
                $referer   = $tmp[0];
                $this->logcache[$month]['referers_url'][$referer]++;
                $tmp      = explode('/', $referer);
                $referer1 = $tmp[2];
                $this->logcache[$month]['referers_domain'][$referer1]++;
                $this->logcache[$month]['referers_total']++;
            }
        }
        //fin  du if ==200

        $this->logcache[$month]['jour']['hits'][$date[0]]++; //Dans tt les cas on augmente hits
        $this->logcache[$month]['resume']['hits']++;
        $this->logcache[$month]['heure']['hits'][intval($hour[1])]++;
        if($this->logcache[$month]['url']['is_page'][$parts[6]])
            $this->logcache[$month]['url']['hits'][$parts[6]]++;

        if(!is_numeric($parts[7]))
            $this->logcache[$month]['resume'][$parts[8]]++;
        else if(!is_numeric($parts[6]))
            $this->logcache[$month]['resume'][$parts[7]]++;

        if(strlen($parts[11]) > 4) {
            $parts[11] = trim($parts[11], "\";\n");
            $this->logcache[$month]['agents_moteur'][$parts[11]]++;
        }

        $parts = explode('"', $line);
        if(strlen($parts[5]) > 4)
            $this->logcache[$month]['agents_complet'][$parts[5]]++;
        */

    }

    /**
     * Avarages a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return int
     */
    public function avg($input, $key){
        $cnt = 0;
        $all = 0;
        foreach((array) $input as $item){
            $all += $item[$key];
            $cnt++;
        }

        if(!$cnt) return 0;
        return round($all/$cnt);
    }

    /**
     * Gives maximum of a certain column from the input array
     *
     * @param $input
     * @param $key
     * @return int
     */
    public function max($input, $key){
        $max = 0;
        foreach((array) $input as $item){
            if($item[$key] > $max) $max = $item[$key];
        }

        return $max;
    }

}

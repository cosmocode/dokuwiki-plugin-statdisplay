<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

$statdisplay_memory_cache1 = DOKU_INC . $this->getConf('memory_cache') . '/statdisplay_memory_cache1.php';
$statdisplay_memory_cache2 = DOKU_INC . $this->getConf('memory_cache') . '/statdisplay_memory_cache2.php';

if (!function_exists("date_to_timestamp"))
{function date_to_timestamp($time_string)
{
    $date_string=trim($time_string,"[");
    $date=explode('/',$date_string);
    $hour=explode(':',$date[2]);
    $date[0]=trim($date[0],"0");
    return mktime($hour[1],$hour[2],$hour[3],$month[$date[1]],$date[0],$hour[0]);
}
}

if (file_exists($log_path))
{
    if (is_readable($log_path))
	$fichier_log=fopen($log_path,'r');
    else
	exit ('Permission denied on log file '.$log_path);
}
else
{
    exit('Cannot open Logstats plugin log file : '.$log_path);
}

if (!file_exists($statdisplay_memory_cache1))
{
    $fichier_sauv=fopen($statdisplay_memory_cache1,'wr+');
    fclose($fichier_sauv);
}

if (!file_exists($statdisplay_memory_cache2))
{
    $fichier_sauv=fopen($statdisplay_memory_cache2,'wr+');
    fclose($fichier_sauv);
}

if (filemtime($statdisplay_memory_cache1) >= filemtime($statdisplay_memory_cache2))
{
    $file=fopen($statdisplay_memory_cache1,'r');
    fseek($file,-2,SEEK_END);
    if (fgets($file)=="?>") //fichier 1 correctement ecrit
    {
	fclose($file);
	require $statdisplay_memory_cache1;
	if ($_SESSION['need_update'] and ( $log_offset<filesize($log_path) or !isset($log_offset)) )
	{
	    $need_update=TRUE;
	    $fichier_sauv=fopen($statdisplay_memory_cache2,'wr+');
	    if (!flock($fichier_sauv,LOCK_EX))
		exit('failed to lock memory file ' . $statdisplay_memory_cache2);
	}
    }
    else
    {
	fclose($file);
	$file=fopen($statdisplay_memory_cache2,'r');
	fseek($file,-2,SEEK_END);
	if (fgets($file)=="?>") //fichier 2 correctement ecrit
	    require $statdisplay_memory_cache2;

	fclose($file);
	if ($_SESSION['need_update'] and ( $log_offset<filesize($log_path) or !isset($log_offset)) )
	{
	    $need_update=TRUE;
	    $fichier_sauv=fopen($statdisplay_memory_cache1,'wr+');
	    if (!flock($fichier_sauv,LOCK_EX))
		exit('failed to lock memory file ' . $statdisplay_memory_cache1);
	}
    }
}
else if (filemtime($statdisplay_memory_cache2) >= filemtime($statdisplay_memory_cache1))
{
    $file=fopen($statdisplay_memory_cache2,'r');
    fseek($file,-2,SEEK_END);
    if (fgets($file)=="?>") //fichier 2 correctement ecrit
    {
	fclose($file);
	require $statdisplay_memory_cache2;

	if ($_SESSION['need_update'] and ( $log_offset<filesize($log_path) or !isset($log_offset)) )
	{
	    $need_update=TRUE;
	    $fichier_sauv=fopen($statdisplay_memory_cache1,'wr+');
	    if (!flock($fichier_sauv,LOCK_EX))
		exit('failed to lock memory file '. $statdisplay_memory_cache1);
	}
    }

    else
    {
	fclose($file);
	$file=fopen($statdisplay_memory_cache1,'r');
	fseek($file,-2,SEEK_END);
	if (fgets($file)=="?>") //fichier 1 correctement ecrit
	    require $statdisplay_memory_cache1;

	fclose($file);
	if ($_SESSION['need_update'] and ( $log_offset<filesize($log_path) or !isset($log_offset)) )
	{
	    $need_update=TRUE;
	    $fichier_sauv=fopen($statdisplay_memory_cache2,'wr+');
	    if (!flock($fichier_sauv,LOCK_EX))
		exit('failed to lock memory file '. $statdisplay_memory_cache2);
	}
    }
}
else
{
    $fichier_sauv=fopen($statdisplay_memory_cache1,'wr+');
    if (!flock($fichier_sauv,LOCK_EX))
	exit('failed to lock memory file ' . $statdisplay_memory_cache1);
}

// Writing php file for generation of images
// into cache folder
/*
$statdisplay_daily_histogram = DOKU_INC . $this->getConf('memory_cache') . '/statdisplay_daily_histogram.php';
if (!file_exists($statdisplay_daily_histogram))
{
    if (!copy(DOKU_PLUGIN . 'statdisplay/daily_histogram.php', $statdisplay_daily_histogram))
	exit('Failed to copy daily_histogram.php into ' . DOKU_INC . $this->getConf('memory_cache') . " folder.");
}
$statdisplay_resume_histogram = DOKU_INC . $this->getConf('memory_cache') . '/statdisplay_resume_histogram.php';
if (!file_exists($statdisplay_resume_histogram))
{
    if (!copy(DOKU_PLUGIN . 'statdisplay/resume_histogram.php', $statdisplay_resume_histogram))
	exit('Failed to copy resume_histogram.php into ' . DOKU_INC . $this->getConf('memory_cache') . " folder.");
}
 */


if (isset($log_offset))
    fseek($fichier_log,$log_offset,SEEK_SET);


?>

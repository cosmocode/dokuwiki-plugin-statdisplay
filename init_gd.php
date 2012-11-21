<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

// Enables the detection of the dokuwiki's configuration
// Allow to guess where are the statdisplay_memory_cacheX.php files
require "./conf/default.php";
require "../../../conf/local.php";

$statdisplay_memory_cache1 = "../../../" . $conf['memory_cache'] . "/statdisplay_memory_cache1.php";
$statdisplay_memory_cache2 = "../../../" . $conf['memory_cache'] . "/statdisplay_memory_cache2.php";


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
     }
   else
     {
      fclose($file);
      $file=fopen($statdisplay_memory_cache2,'r');
      fseek($file,-2,SEEK_END);
      if (fgets($file)=="?>") //fichier 2 correctement ecrit
         require $statdisplay_memory_cache2;
        
      fclose($file);
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
     }
   else
     {
      fclose($file);
      $file=fopen($statdisplay_memory_cache1,'r');
      fseek($file,-2,SEEK_END);
      if (fgets($file)=="?>") //fichier 1 correctement ecrit
         require $statdisplay_memory_cache1;
        
      fclose($file);
     }
  }
?>

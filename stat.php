<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

include 'init.php';
if ($need_update)
{
while(feof($fichier_log)==0 && $log_pos<$nb_lignes_traitement)
 {
  $ligne=fgets($fichier_log);
  $log_pos++;
  if ($ligne=="")
    break;
  $champs=explode(' ',$ligne);
  $date_string=trim($champs[3],"[");
  $date=explode('/',$date_string);
  $hour=explode(':',$date[2]);
  $date[0]=ltrim($date[0],"0");
  $month_year=$date[1].'_'.$hour[0];
  $tmp=explode('?',$champs[6]);//enleve les variables GET
  $champs[6]=$tmp[0];
  if ($champs[8]==200)
   {
    if (!isset($tableau[$month_year]['url']['is_page'][$champs[6]]))
      {
       if (stristr($champs[6],".php")!=NULL || stristr($champs[6],".htm")!=NULL || stristr($champs[6],".xhtml")!=NULL ||stristr($champs[6],".asp")!=NULL || strstr($champs[6],".")==NULL) 
         {
          $tableau[$month_year]['url']['is_page'][$champs[6]]=TRUE;
          $tableau[$month_year]['resume']['nb_pages']++;
         }
       else
          $tableau[$month_year]['url']['is_page'][$champs[6]]=FALSE;
      }

    if ($tableau[$month_year]['url']['is_page'][$champs[6]])
     {
      $tableau[$month_year]['jour']['pages'][$date[0]]++;
      $tableau[$month_year]['resume']['pages']++;
      $tableau[$month_year]['heure']['pages'][intval($hour[1])]++;
      $tableau[$month_year]['url']['bytes'][$champs[6]]+=($champs[9]/1024);
     }
   $tableau[$month_year]['jour']['files'][$date[0]]++;
   $tableau[$month_year]['resume']['files']++;
   $tableau[$month_year]['heure']['files'][intval($hour[1])]++;
   $tableau[$month_year]['resume']['bytes']+=($champs[9]/1024);
   $tableau[$month_year]['heure']['bytes'][intval($hour[1])]+=($champs[9]/1024);
   $tableau[$month_year]['jour']['bytes'][$date[0]]+=($champs[9]/1024);
   
//Bloc gestion des visites & pages d'entree
  $tmp=date_to_timestamp($date_string);
  if (!isset($ip[$champs[0]]) || ($ip[$champs[0]]+$duree_visite) <  $tmp)
    {
     $tableau[$month_year]['jour']['visits'][$date[0]]++;
     $tableau[$month_year]['resume']['visits']++;
     $tableau[$month_year]['heure']['visits'][intval($hour[1])]++;
     if ($tableau[$month_year]['url']['is_page'][$champs[6]])
       	{
				 $tableau[$month_year]['entree'][$champs[6]]++;
				 $tableau[$month_year]['resume']['entree']++;
				}
     $ip[$champs[0]]=$tmp;
 		}
  $_SESSION['last_visit']=$tmp;

  if (strstr($champs[10],'"http')!=NULL) //Referrers
    {
     $champs[10]=trim($champs[10],'"');
     $tmp=explode('?',$champs[10]);
     $referer=$tmp[0];
     $tableau[$month_year]['referers_url'][$referer]++;
     $tmp=explode('/',$referer);
     $referer1=$tmp[2];
     $tableau[$month_year]['referers_domain'][$referer1]++;
     $tableau[$month_year]['referers_total']++;
		}
}//fin  du if ==200

  $tableau[$month_year]['jour']['hits'][$date[0]]++; //Dans tt les cas on augmente hits
  $tableau[$month_year]['resume']['hits']++;
  $tableau[$month_year]['heure']['hits'][intval($hour[1])]++;
  if ($tableau[$month_year]['url']['is_page'][$champs[6]])
    $tableau[$month_year]['url']['hits'][$champs[6]]++;
  
 if (!is_numeric($champs[7]))
    $tableau[$month_year]['resume'][$champs[8]]++;
  else if (!is_numeric($champs[6]))
    $tableau[$month_year]['resume'][$champs[7]]++;

 if (strlen($champs[11])>4)
 {
  $champs[11]=trim($champs[11],"\";\n");
  $tableau[$month_year]['agents_moteur'][$champs[11]]++;
 }

 $champs=explode('"',$ligne);
 if (strlen($champs[5])>4)
    $tableau[$month_year]['agents_complet'][$champs[5]]++;

 }
 //Fin de la boucle While

  fwrite($fichier_sauv,"<?PHP\n");
 foreach($tableau as $index => $valeur)
 {
  for($a=1;$a<=31;$a++)
   {
    if (isset($valeur['jour']['hits'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'jour\'][\'hits\'][\''.$a.'\'] = '.$valeur['jour']['hits'][$a].";\n");
    if (isset($valeur['jour']['files'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'jour\'][\'files\'][\''.$a.'\'] = '.$valeur['jour']['files'][$a].";\n");
    if (isset($valeur['jour']['pages'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'jour\'][\'pages\'][\''.$a.'\'] = '.$valeur['jour']['pages'][$a].";\n");
    if (isset($valeur['jour']['visits'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'jour\'][\'visits\'][\''.$a.'\'] = '.$valeur['jour']['visits'][$a].";\n");
    if (isset($valeur['jour']['bytes'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'jour\'][\'bytes\'][\''.$a.'\'] = '.$valeur['jour']['bytes'][$a].";\n");
   }
    
  for($a=0;$a<=23;$a++)
   {
    if (isset($valeur['heure']['hits'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'heure\'][\'hits\'][\''.$a.'\'] = '.$valeur['heure']['hits'][$a].";\n");
    if (isset($valeur['heure']['files'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'heure\'][\'files\'][\''.$a.'\'] = '.$valeur['heure']['files'][$a].";\n");
    if (isset($valeur['heure']['pages'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'heure\'][\'pages\'][\''.$a.'\'] = '.$valeur['heure']['pages'][$a].";\n");
    if (isset($valeur['heure']['visits'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'heure\'][\'visits\'][\''.$a.'\'] = '.$valeur['heure']['visits'][$a].";\n");
    if (isset($valeur['heure']['bytes'][$a]))
       fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'heure\'][\'bytes\'][\''.$a.'\'] = '.$valeur['heure']['bytes'][$a].";\n");
   }
  foreach($valeur['url']['hits'] as $nom => $val)
    fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'url\'][\'hits\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['url']['bytes'] as $nom => $val)
    fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'url\'][\'bytes\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['resume'] as $nom => $val)
     fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'resume\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['referers_url'] as $nom => $val)
      fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'referers_url\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['referers_domain'] as $nom => $val)
      fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'referers_domain\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['agents_moteur'] as $nom => $val)
      fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'agents_moteur\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['agents_complet'] as $nom => $val)
      fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'agents_complet\'][\''.$nom.'\'] = '.$val.";\n");
  foreach($valeur['entree'] as $nom => $val)
      fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'entree\'][\''.$nom.'\'] = '.$val.";\n");
 	fwrite($fichier_sauv,'$tableau[\''.$index.'\'][\'referers_total\'] =
	\''.$valeur['referers_total']."';\n");
 }

 if (isset($_SESSION['last_visit']))
 		fwrite($fichier_sauv,'$_SESSION[\'last_visit\'] = \''.$_SESSION['last_visit']."';\n");

  foreach($ip as $nom => $val)
  		{
				if (($val+$duree_visite)>$_SESSION['last_visit'])
					fwrite($fichier_sauv,'$ip[\''.$nom.'\'] = '.$val.";\n");
			}


 if (isset($month_year))
	 $_SESSION['last_month'] = $month_year;
 fwrite($fichier_sauv,'$_SESSION[\'last_month\'] = \''.$_SESSION['last_month']."';\n");

  fwrite($fichier_sauv,'$log_offset = '.ftell($fichier_log).";\n?>");

fclose($fichier_sauv);
}
$lod=fstat($fichier_log);
$progress['max']=$lod['size'];
$progress['value']=ftell($fichier_log);
fclose($fichier_log);
$_SESSION['statdisplay']=$tableau;
$_SESSION['progress']=$progress;

?>

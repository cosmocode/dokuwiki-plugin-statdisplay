<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function hourly($month)
{
if (isset($_SESSION['statdisplay'][$month]))
{
$tableau=$_SESSION['statdisplay'];
$msg = '<table cellspacing="0"> <tr><td colspan="11" class="titre" bgcolor="#BABABA">Hourly statistics for '.str_replace("_"," ",$month).'</td></tr>
  <tr>
   <td class="titre" bgcolor="#BABABA">Hour</td>
   <td class="titre" colspan="2" bgcolor="green">hits</td>
   <td class="titre" colspan="2"  bgcolor="blue">files</td>
   <td class="titre" colspan="2" bgcolor="cyan">pages</td>
   <td class="titre" colspan="2" bgcolor="yellow">visits</td>
   <td class="titre" colspan="2" bgcolor="red">Kbytes</td>
  </tr>';
for ($i=0;$i<=23;$i++)
  {
   if ($tableau[$month]['heure']['hits'][$i]>0)
    {
     $msg .= '<tr>
     <td align="center">'.$i.'</td>
     <td class="valeur">'.$tableau[$month]['heure']['hits'][$i].'</td>
     <td class="pourcent">'.number_format($tableau[$month]['heure']['hits'][$i]*100/$tableau[$month]['resume']['hits'],2).'%</td>
     <td class="valeur">'.$tableau[$month]['heure']['files'][$i].'</td>
     <td class="pourcent">'.number_format($tableau[$month]['heure']['files'][$i]*100/$tableau[$month]['resume']['files'],2).'%</td>
     <td class="valeur">'.$tableau[$month]['heure']['pages'][$i].'</td>
     <td class="pourcent">'.number_format($tableau[$month]['heure']['pages'][$i]*100/$tableau[$month]['resume']['pages'],2).'%</td>
     <td class="valeur">'.$tableau[$month]['heure']['visits'][$i].'</td>
     <td class="pourcent">'.number_format($tableau[$month]['heure']['visits'][$i]*100/$tableau[$month]['resume']['visits'],2).'%</td>
     <td class="valeur">'.number_format($tableau[$month]['heure']['bytes'][$i],2).'</td>
     <td class="pourcent">'.number_format($tableau[$month]['heure']['bytes'][$i]*100/$tableau[$month]['resume']['bytes'],2).'%</td>
     </tr>';
    }
  }
$msg .= '</table>';
return $msg;
}
}
?>

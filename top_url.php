<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */


function top_url($month,$nb_lines)
{
if (isset($_SESSION['statdisplay'][$month]))
{
arsort($_SESSION['statdisplay'][$month]['url']['hits']);
$i=1;
   foreach($_SESSION['statdisplay'][$month]['url']['hits'] as $index =>$inter2)
   {
	 	if ($i>$nb_lines)
   		break;
    $msg1 .= '<tr>
     <td align="center">'.$i.'</td>
     <td align="left">'.$index.'</td>
     <td class="valeur">'.$inter2.'</td>
     <td class="pourcent">'.number_format($inter2*100/$_SESSION['statdisplay'][$month]['resume']['hits'],2).'%</td>
     <td class="valeur">'.number_format($_SESSION['statdisplay'][$month]['url']['bytes'][$index],2).'</td>
     <td class="pourcent">'.number_format($_SESSION['statdisplay'][$month]['url']['bytes'][$index]*100/$_SESSION['statdisplay'][$month]['resume']['bytes'],2).'%</td>
     </tr>';
     $i++;
   }
$msg = '<table cellspacing="0"> <tr><td colspan="6" class="titre" bgcolor="#BABABA">Top '.($i-1).' of '.count($_SESSION['statdisplay'][$month]['url']['hits']).' PAGES for '.str_replace("_"," ",$month).' by Hits</td></tr>';
$msg .= '<tr><td class="titre" bgcolor="#BABABA">#</td><td class="titre" bgcolor="yellow">url</td>
<td class="titre" colspan="2" bgcolor="green">hits</td><td class="titre" colspan="2" bgcolor="red">Kbytes</td></tr>';
$msg.=$msg1.'</table><br />';
return $msg;
}
}
?>

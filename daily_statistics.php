<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function daily($month) {
    if(isset($_SESSION['statdisplay'][$month])) {
        $tableau = $_SESSION['statdisplay'];
        $msg     = '<table cellspacing="0"> <tr><td colspan="11" class="titre" bgcolor="#BABABA">Daily statistics for '.str_replace("_", " ", $month).'</td></tr>
 <tr>
   <td class="titre" bgcolor="#BABABA">Day</td>
   <td class="titre" colspan="2" bgcolor="green">hits</td>
   <td class="titre" colspan="2" bgcolor="blue">files</td>
   <td class="titre" colspan="2" bgcolor="cyan">pages</td>
   <td class="titre" colspan="2"  bgcolor="yellow">visits</td>
   <td class="titre" colspan="2"  bgcolor="red">Kbytes</td>
 </tr>';

        for($i = 1; $i <= 31; $i++) {
            if($tableau[$month]['jour']['hits'][$i] > 0) {
                $msg .= '<tr>
     <td align="center">'.$i.'</td>
     <td class="valeur" >'.$tableau[$month]['jour']['hits'][$i].'</td>
     <td class="pourcent" >'.number_format($tableau[$month]['jour']['hits'][$i] * 100 / $tableau[$month]['resume']['hits'], 2).'%</td>
     <td class="valeur" >'.$tableau[$month]['jour']['files'][$i].'</td>
     <td class="pourcent" >'.number_format($tableau[$month]['jour']['files'][$i] * 100 / $tableau[$month]['resume']['files'], 2).'%</td>
     <td class="valeur" >'.$tableau[$month]['jour']['pages'][$i].'</td>
     <td class="pourcent" >'.number_format($tableau[$month]['jour']['pages'][$i] * 100 / $tableau[$month]['resume']['pages'], 2).'%</td>
     <td class="valeur" >'.$tableau[$month]['jour']['visits'][$i].'</td>
     <td class="pourcent" >'.number_format($tableau[$month]['jour']['visits'][$i] * 100 / $tableau[$month]['resume']['visits'], 2).'%</td>
     <td class="valeur" >'.number_format($tableau[$month]['jour']['bytes'][$i], 2).'</td>
     <td class="pourcent" >'.number_format($tableau[$month]['jour']['bytes'][$i] * 100 / $tableau[$month]['resume']['bytes'], 2).'%</td>
     </tr>';
            }
        }
        $msg .= '</table>';
        return $msg;
    }
}

?>

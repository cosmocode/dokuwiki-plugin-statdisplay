<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function top_entries($month, $nb_lines) {
    if(isset($_SESSION['statdisplay'][$month])) {
        arsort($_SESSION['statdisplay'][$month]['entree']);

        $i = 1;
        foreach($_SESSION['statdisplay'][$month]['entree'] as $index => $inter2) {
            if($i <= $nb_lines) {
                $msg1 .= '<tr>
      <td align="center">'.$i.'</td>
      <td align="left">'.$index.'</td>
      <td class="valeur">'.$inter2.'</td>
      <td class="pourcent">'.number_format($inter2 * 100 / $_SESSION['statdisplay'][$month]['resume']['entree'], 2).'%</td>
      </tr>';
                $i++;
            } else
                break;
        }
        $msg = '<table cellspacing="0"> <tr><td colspan="6" class="titre" bgcolor="#BABABA">Top '.($i - 1).' of '.count($_SESSION['statdisplay'][$month]['entree']).' total Entry pages for '.str_replace('_', ' ', $month).'</td></tr>
<tr>
<td class="titre" bgcolor="#BABABA">#</td>
<td class="titre" bgcolor="yellow">url</td>
<td class="titre" colspan="2" bgcolor="green">Entries</td>
</tr>'.$msg1.'</table><br />';
        return $msg;
    }
}

?>

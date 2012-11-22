<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function total_summary($begin, $end) {
    if(isset($_SESSION['statdisplay'])) {
        $tableau = $_SESSION['statdisplay'];
        if($begin == "")
            $new_tab = $tableau;
//Cut the table to the interesting part
        if($begin != "") {
            $flag     = 0;
            $end_flag = 0;
            foreach($tableau as $key => $sub_tab) {
                if($flag == 0 AND $key == $begin)
                    $flag = 1;
                if($flag == 1 AND $end != "" AND $key == $end)
                    $end_flag = 1;
                if($end_flag == 1 AND $key != $end)
                    $flag = 0;
                if($flag == 1)
                    $new_tab[$key] = $sub_tab;
            }
        }

        $msg = '<table cellspacing="0"><tr><td colspan="10" class="titre" bgcolor="#BABABA">Summary by month</td></tr>
<tr><td rowspan="2" class="titre" bgcolor="#BABABA">Month</td><td colspan="4" class="titre" bgcolor="#BABABA">Daily Average</td>
<td colspan="6" class="titre" bgcolor="#BABABA">Monthly Totals</td></tr>
<tr><td class="titre" bgcolor="green">Hits</td><td class="titre" bgcolor="blue">Files</td>
<td class="titre" bgcolor="cyan">Pages</td><td class="titre" bgcolor="yellow">Visits</td>
<td class="titre" bgcolor="red">Kbytes</td><td class="titre" bgcolor="yellow">Visits</td>
<td class="titre" bgcolor="cyan">Pages</td><td class="titre" bgcolor="blue">Files</td>
<td class="titre" bgcolor="green">Hits</td></tr>';

        foreach($new_tab as $month => $value)
            $msg .= '<tr><td align="right" >'.str_replace('_', ' ', $month).'</td>
		<td align="right">'.intval($value['resume']['hits'] / count($value['jour']['hits'])).'</td>
		<td align="right">'.intval($value['resume']['files'] / count($value['jour']['hits'])).'</td>
		<td align="right">'.intval($value['resume']['pages'] / count($value['jour']['hits'])).'</td>
		<td align="right">'.intval($value['resume']['visits'] / count($value['jour']['hits'])).'</td>
		<td align="right">'.intval($value['resume']['bytes']).'</td>
		<td align="right">'.$value['resume']['visits'].'</td>
		<td align="right">'.$value['resume']['pages'].'</td>
		<td align="right">'.$value['resume']['files'].'</td>
		<td align="right">'.$value['resume']['hits'].'</td></tr>';

        $msg .= '</table>';
        return $msg;
    }
}

?>

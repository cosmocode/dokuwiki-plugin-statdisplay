<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function summary($month)
{
if (isset($_SESSION['statdisplay'][$month]))
{
$tableau=$_SESSION['statdisplay'];
$nb_jours=count($tableau[$month]['jour']['hits']);
$msg= '<table cellspacing="0"> <tr><td colspan="3" class="titre" bgcolor="#BABABA">Monthly statistics for '.str_replace("_"," ",$month).'</td></tr>';
$msg .=  '<tr><td align="left">Total hits</td><td colspan="2" align="right">'.$tableau[$month]['resume']['hits'].'</td></tr>
<tr><td align="left">Total files</td><td colspan="2" align="right">'.$tableau[$month]['resume']['files'].'</td></tr>
<tr><td align="left">Total pages</td><td colspan="2" align="right">'.$tableau[$month]['resume']['pages'].'</td></tr>
<tr><td align="left">Total visits</td><td colspan="2" align="right">'.$tableau[$month]['resume']['visits'].'</td></tr>
<tr><td align="left">Total Kbytes</td><td colspan="2" align="right">'.intval($tableau[$month]['resume']['bytes']).'</td></tr>
<tr><td align="center" bgcolor="#BABABA"></td><td class="titre" bgcolor="#BABABA">avg</td><td class="titre" bgcolor="#BABABA">max</td></tr>
<tr><td align="left">Hits per hours</td><td align="right">'.intval($tableau[$month]['resume']['hits']/24).'</td><td align="right">'.max($tableau[$month]['heure']['hits']).'</td></tr>
<tr><td align="left">Hits per day</td><td align="right">'.intval($tableau[$month]['resume']['hits']/$nb_jours).'</td><td align="right">'.max($tableau[$month]['jour']['hits']).'</td></tr>
<tr><td align="left">Files per day</td><td align="right">'.intval($tableau[$month]['resume']['files']/$nb_jours).'</td><td align="right">'.max($tableau[$month]['jour']['files']).'</td></tr>
<tr><td align="left">Pages per day</td><td align="right">'.intval($tableau[$month]['resume']['pages']/$nb_jours).'</td><td align="right">'.max($tableau[$month]['jour']['pages']).'</td></tr>
<tr><td align="left">Visits per day</td><td align="right">'.intval($tableau[$month]['resume']['visits']/$nb_jours).'</td><td align="right">'.max($tableau[$month]['jour']['visits']).'</td></tr>
<tr><td align="left">Kbytes per day</td><td align="right">'.intval($tableau[$month]['resume']['bytes']/$nb_jours).'</td><td align="right">'.intval(max($tableau[$month]['jour']['bytes'])).'</td></tr>
<tr><td colspan="3" class="titre" bgcolor="#BABABA">Hits by response code</td></tr>
<tr><td align="left">Code 200 OK</td><td colspan="2" align="right">'.$tableau[$month]['resume']['200'].'</td></tr>
<tr><td align="left">Code 206 Partial content</td><td colspan="2" align="right">'.$tableau[$month]['resume']['206'].'</td></tr>
<tr><td align="left">Code 301 Moved permanently</td><td colspan="2" align="right">'.$tableau[$month]['resume']['301'].'</td></tr>
<tr><td align="left">Code 304 Not modified</td><td colspan="2" align="right">'.$tableau[$month]['resume']['304'].'</td></tr>
<tr><td align="left">Code 400 Bad request</td><td colspan="2" align="right">'.$tableau[$month]['resume']['400'].'</td></tr>
<tr><td align="left">Code 403 Forbidden</td><td colspan="2" align="right">'.$tableau[$month]['resume']['403'].'</td></tr>
<tr><td align="left">Code 404 Not found</td><td colspan="2" align="right">'.$tableau[$month]['resume']['404'].'</td></tr>
<tr><td align="left">Code 408 Request timeout</td><td colspan="2" align="right">'.$tableau[$month]['resume']['408'].'</td></tr>
</table>';
return $msg;
}
}
?>

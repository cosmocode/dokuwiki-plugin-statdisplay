<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function top_referers($month,$choice,$nb_lines,$tab_referers,$association)
{
if (isset($_SESSION['statdisplay'][$month]))
{
switch ($choice)
{
	case 'complete_link':
		$tab=$_SESSION['statdisplay'][$month]['referers_url'];
		break;
	case 'domain':
		$tab=$_SESSION['statdisplay'][$month]['referers_domain'];
	default:
			break;
}

if ($association)
	{
		foreach ($tab as $referer => $value)
			{
				foreach($tab_referers as $nom => $inter)
					{
						foreach ($inter as $nom1 => $inter1)
							{
								if (preg_match($nom1,$referer) >0)
     							{
										$tab[$nom]+=$value;
										unset($tab[$referer]);
										break 2;
									}
							}
					}
			}
	}

arsort($tab);
$i=1;
   foreach($tab as $index =>$inter2)
   {
    if ( $i<=$nb_lines)
     {
      $msg1 .= '<tr>
      <td align="center">'.$i.'</td>
      <td align="left">'.$index.'</td>
      <td class="valeur">'.$inter2.'</td>
      <td
      class="pourcent">'.number_format($inter2*100/$_SESSION['statdisplay'][$month]['referers_total'],2).'%</td>
      </tr>';
      $i++;
     }
    else
     break;
   }
 $msg = '<table cellspacing="0"> <tr><td colspan="4" class="titre" bgcolor="#BABABA">Top '.($i-1).' of '.count($tab).' total Referers for '.str_replace('_',' ',$month).'</td></tr><tr>
<td class="titre" bgcolor="#BABABA">#</td>
<td class="titre" bgcolor="yellow">url</td>
<td class="titre" colspan="2" bgcolor="green">hits</td>
</tr>'.$msg1.'</table><br />';

return $msg;
}
}
?>

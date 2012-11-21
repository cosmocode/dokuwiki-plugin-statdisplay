<?PHP
/**
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     J.-F. Lalande <jean-francois.lalande@ensi-bourges.fr>
 * @author     Maxime Fonda <maxime.fonda@ensi-bourges.fr>
 * @author     Thibault Coullet <thibault.coullet@ensi-bourges.fr>
 */

function user_agents($month,$choice,$tabagent,$nb_line)
{
if (isset($_SESSION['statdisplay'][$month]))
{
$msg .= '<table cellspacing="0"> 
<tr>
	<td colspan="4" class="titre" bgcolor="#BABABA"> User agents for '.str_replace('_',' ',$month).'
	</td>
</tr>
<tr>
	<td class="titre" bgcolor="#BABABA">#
	</td>
	<td class="titre" bgcolor="yellow">Name
	</td>
	<td class="titre" bgcolor="green" colspan="2">Hits
	</td>
</tr>';

switch ($choice) 
{	
	case 'keyword':
		$user=$_SESSION['statdisplay'][$month]['agents_complet'];
		foreach ($user as $name => $value)
			{
				foreach($tabagent as $nom => $inter)
					{
						if (stristr($name,$nom) != NULL)
     					{
								$users[$inter]+=$value;
								break ;
							}
					}
			}
		break;
	case 'display':
		$users=$_SESSION['statdisplay'][$month]['agents_moteur'];
		break;
	case 'all_line':
		$users=$_SESSION['statdisplay'][$month]['agents_complet'];
		break;
	default :
		break;
}
 
arsort($users);
$i=1;

foreach($users as $nom => $valeur)
	{
		if ($i > $nb_line)
				break;

		$msg .= '<tr><td align="center">'.$i."</td>\n<td align=\"left\">";
			$msg .= $nom;
		$msg .=	"</td>\n<td class=\"valeur\">".$valeur."</td>\n<td class=\"pourcent\">".
						number_format($valeur*100/$_SESSION['statdisplay'][$month]['resume']['hits'],2)."%</td></tr>\n";
		$i++;
	}
$msg .= '</table>';
return $msg;
}
}
?>

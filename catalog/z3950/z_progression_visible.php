<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// | creator : Eric ROBERT                                                    |
// | modified : ...                                                           |
// +-------------------------------------------------+
// $Id: z_progression_visible.php,v 1.12 2015-11-22 18:37:00 Alexandre Exp $

// définition du minimum nécéssaire
$base_path="../..";
$base_auth = "CATALOGAGE_AUTH";
$base_title = "";
//permet d'appliquer le style de l'onglet ou apparait la frame
$current_alert = "catalog";
require_once ("$base_path/includes/init.inc.php");

// les requis par z_progression_visible.php ou ses sous modules
require_once("$include_path/isbn.inc.php");
require_once("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once("$class_path/iso2709.class.php");
require_once("z3950_func.inc.php");
//print "<div id='contenu-frame'>";

print "
<div id='contenu-frame'>
<!--
<br /><p align='center'>$msg[z3950_progr_rech_txt]</p>
-->
<table class='nobrd' width='100%' align='center'>";

print "
	<div id='zframe1' align='center'> ".$msg['patientez']."
		<div id='joke' style='visibility:\"visible\";'><img src='../../images/patience.gif'></div>
	</div>";

//
// On détermine les Bibliothèques sélectionnées
//

$recherche=pmb_mysql_query("SELECT * FROM z_bib $selection_bib ORDER BY bib_nom");
$parity = 1;
while ($resultat=pmb_mysql_fetch_array($recherche)) {
	$bib_id=$resultat["bib_id"];
	$nom_bib=$resultat["bib_nom"];
	if ($parity % 2) {
		$pair_impair = "even";
		} else {
			$pair_impair = "odd";
			}
	$parity += 1;

	print "
		<tr class='$pair_impair'>
			<td width='30%'>$nom_bib</td>
			<td><div id='z$bib_id'>$msg[z3950_essai_cnx]</div></td>
		</tr>";
	}

print "
</table></div>";
?>

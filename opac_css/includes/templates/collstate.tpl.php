<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collstate.tpl.php,v 1.7 2015-12-23 14:20:23 jpermanne Exp $

// templates pour gestion des autorités collections

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$collstate_list_header = "
<table class='exemplaires' cellpadding='2' width='100%'>
	<tbody>
";

$collstate_list_footer ="
	</tbody>
</table>";

$tpl_collstate_liste[0]="
<table class='exemplaires' cellpadding='2' width='100%'>
	<tbody>
		<tr>
			<!-- surloc -->
			<th class='collstate_header_emplacement_libelle'>".$msg["collstate_form_emplacement"]."</th>		
			<th class='collstate_header_cote'>".$msg["collstate_form_cote"]."</th>
			<th class='collstate_header_type_libelle'>".$msg["collstate_form_support"]."</th>
			<th class='collstate_header_statut_opac_libelle'>".$msg["collstate_form_statut"]."</th>			
			<th class='collstate_header_origine'>".$msg["collstate_form_origine"]."</th>		
			<th class='collstate_header_state_collections'>".$msg["collstate_form_collections"]."</th>
			<th class='collstate_header_archive'>".$msg["collstate_form_archive"]."</th>
			<th class='collstate_header_lacune'>".$msg["collstate_form_lacune"]."</th>		
		</tr>
		!!collstate_liste!!	
	</tbody>	
</table>
";

$tpl_collstate_liste_line[0]="
<tr class='!!pair_impair!!' !!tr_surbrillance!! >
	<!-- surloc -->
	<td class='emplacement_libelle' !!tr_javascript!! >!!emplacement_libelle!!</td>
	<td class='cote' !!tr_javascript!! >!!cote!!</td>
	<td class='type_libelle' !!tr_javascript!! >!!type_libelle!!</td>
	<td class='statut_opac_libelle' !!tr_javascript!! >!!statut_libelle!!</td>	
	<td class='origine' !!tr_javascript!! >!!origine!!</td>
	<td class='state_collections' !!tr_javascript!! >!!state_collections!!</td>
	<td class='archive' !!tr_javascript!! >!!archive!!</td>
	<td class='lacune' !!tr_javascript!! >!!lacune!!</td>
</tr>";

$tpl_collstate_liste[1]="
<table class='exemplaires' cellpadding='2' width='100%'>
	<tbody>
		<tr>
			<!-- surloc -->
			<th class='collstate_header_location_libelle'>".$msg["collstate_form_localisation"]."</th>		
			<th class='collstate_header_emplacement_libelle'>".$msg["collstate_form_emplacement"]."</th>		
			<th class='collstate_header_cote'>".$msg["collstate_form_cote"]."</th>
			<th class='collstate_header_type_libelle'>".$msg["collstate_form_support"]."</th>
			<th class='collstate_header_statut_opac_libelle'>".$msg["collstate_form_statut"]."</th>		
			<th class='collstate_header_origine'>".$msg["collstate_form_origine"]."</th>		
			<th class='collstate_header_state_collections'>".$msg["collstate_form_collections"]."</th>
			<th class='collstate_header_archive'>".$msg["collstate_form_archive"]."</th>
			<th class='collstate_header_lacune'>".$msg["collstate_form_lacune"]."</th>		
		</tr>
		!!collstate_liste!!
	</tbody>	
</table>
";

$tpl_collstate_surloc_liste = "<th class='collstate_header_surloc_libelle'>".$msg["collstate_form_surloc"]."</th>";

$tpl_collstate_liste_line[1]="
<tr class='!!pair_impair!!' !!tr_surbrillance!! >
	<!-- surloc -->
	<td class='localisation' !!tr_javascript!! >!!localisation!!</td>
	<td class='emplacement_libelle' !!tr_javascript!! >!!emplacement_libelle!!</td>
	<td class='cote' !!tr_javascript!! >!!cote!!</td>
	<td class='type_libelle' !!tr_javascript!! >!!type_libelle!!</td>	
	<td class='statut_opac_libelle' !!tr_javascript!! >!!statut_libelle!!</td>
	<td class='origine' !!tr_javascript!! >!!origine!!</td>
	<td class='state_collections' !!tr_javascript!! >!!state_collections!!</td>
	<td class='archive' !!tr_javascript!! >!!archive!!</td>
	<td class='lacune' !!tr_javascript!! >!!lacune!!</td>
</tr>";

$tpl_collstate_surloc_liste_line = "<td class='surloc_libelle' !!tr_javascript!! >!!surloc!!</td>";

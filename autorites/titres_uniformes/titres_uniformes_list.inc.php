<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: titres_uniformes_list.inc.php,v 1.15 2015-12-04 14:58:41 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// nombre de références par pages
if ($nb_per_page_titre_uniforme != "") 
	$nb_per_page = $nb_per_page_titre_uniforme ;
	else $nb_per_page = 10;

// traitement de la saisie utilisateur
include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once($class_path."/analyse_query.class.php");
require_once($class_path.'/searcher/searcher_authorities_titres_uniformes.class.php');

if($user_input) {
	//a priori pas utile. Armelle
	$clef = reg_diacrit($user_input);
} else {
	$user_input = '*';
}

// $titres_uniformes_list_tmpl : template pour la liste auteurs
$titres_uniformes_list_tmpl = "
<br />
<br />
<div class='row'>
	<h3><! --!!nb_autorite_found!!-- >".$msg["aut_titre_uniforme_result"]." !!cle!! </h3>
	</div>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		!!list!!
	</table>
<div class='row'>
	!!nav_bar!!
	</div>
";

$tu_searcher = new searcher_authorities_titres_uniformes(stripslashes($user_input));
$nbr_lignes = $tu_searcher->get_nb_results();

$type_oeuvre_clause = "";

if($oeuvre_nature_selector){
	$type_oeuvre_clause.= "tu_oeuvre_nature = '".$oeuvre_nature_selector."'";
}
if($oeuvre_type_selector){
	if($oeuvre_nature_selector){
		$type_oeuvre_clause.= " and ";
	}
	$type_oeuvre_clause.= "tu_oeuvre_type = '".$oeuvre_type_selector."'";
}

if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;

if($nbr_lignes) {
	$titres_uniformes_list_tmpl=str_replace( "<! --!!nb_autorite_found!!-- >",$nbr_lignes.' ',$titres_uniformes_list_tmpl);
	$url_base = "./autorites.php?categ=titres_uniformes&sub=reach&user_input=".rawurlencode(stripslashes($user_input)) ;
	
	$titre_uniforme_list = "<tr>
			<th></th>
			<th>".$msg[103]."</th>
			<!--!!col_num_autorite!!-->
			<th>".$msg["count_notices_assoc"]."</th>
		</tr>";
	
	$num_auth_present=false;
	$req="SELECT id_authority_source FROM authorities_sources WHERE authority_type='uniform_title' AND TRIM(authority_number) !='' LIMIT 1";
	$res_aut=pmb_mysql_query($req,$dbh);
	if($res_aut && pmb_mysql_num_rows($res_aut)){
		$titre_uniforme_list=str_replace("<!--!!col_num_autorite!!-->","<th>".$msg["authorities_number"]."</th>",$titre_uniforme_list);
		$num_auth_present=true;
	}
	
	$parity=1;
	$sorted_tu = $tu_searcher->get_sorted_result('default', $debut, $nb_per_page);
	
	foreach ($sorted_tu as $authority_id) {
		// On va chercher les infos spécifique à l'autorité
		$authority = new authority($authority_id);
		$tu_id = $authority->get_num_object();
		$tu = $authority->get_object_instance();

		$tu->do_isbd();
		$titre_uniforme_entry = $tu->tu_isbd;
		//$titre_uniforme_entry = $tu->display;
		$link_titre_uniforme = "./autorites.php?categ=titres_uniformes&sub=titre_uniforme_form&id=".$tu_id."&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page";
		//$link_titre_uniforme = './autorites.php?categ=see&sub=titre_uniforme&id='.$tu_id;
		if ($parity % 2) {
			$pair_impair = "even";
		} else {
			$pair_impair = "odd";
		}
		$parity += 1;
		
		$notice_count_sql = "SELECT count(*) FROM notices_titres_uniformes WHERE ntu_num_tu = ".$tu_id;
		$notice_count = pmb_mysql_result(pmb_mysql_query($notice_count_sql), 0, 0);
		
	    $tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\"  ";
        $titre_uniforme_list .= "
        <tr class='$pair_impair' $tr_javascript style='cursor: pointer'>
			<td style='text-align:center; width:25px;'>
        		<a title='".$msg['authority_list_see_label']."' href='./autorites.php?categ=see&sub=titre_uniforme&id=".$tu_id."'>
        			<i class='fa fa-eye'></i>
        		</a>
        	</td>
         	<td valign='top' onmousedown=\"document.location='$link_titre_uniforme';\">
				$titre_uniforme_entry
			</td>";
		
		//Numéros d'autorite
		if($num_auth_present){
			$requete="SELECT authority_number,origin_authorities_name, origin_authorities_country FROM authorities_sources JOIN origin_authorities ON num_origin_authority=id_origin_authorities WHERE authority_type='uniform_title' AND num_authority='".$tu_id."' AND TRIM(authority_number) !='' GROUP BY authority_number,origin_authorities_name,origin_authorities_country ORDER BY authority_favorite DESC, origin_authorities_name";
			$res_aut=pmb_mysql_query($requete,$dbh);
			if($res_aut && pmb_mysql_num_rows($res_aut)){
				$titre_uniforme_list .= "<td>";
				$first=true;
				while ($aut = pmb_mysql_fetch_object($res_aut)) {
					if(!$first)$titre_uniforme_list .=", ";
					$titre_uniforme_list .=htmlentities($aut->authority_number,ENT_QUOTES,$charset);
					if($tmp=trim($aut->origin_authorities_name)){
						$titre_uniforme_list .=htmlentities(" (".$aut->origin_authorities_name.")",ENT_QUOTES,$charset);
					}
					$first=false;
				}
				$titre_uniforme_list .= "</td>";
			}else{
				$titre_uniforme_list .= "<td>&nbsp;</td>";
			}
		}
		
		if($notice_count && $notice_count!=0)
			$titre_uniforme_list .=  "<td onmousedown=\"document.location='./catalog.php?categ=search&mode=9&etat=aut_search&aut_type=titre_uniforme&aut_id=".$tu_id."'\">".$notice_count."</td>";
		else $titre_uniforme_list .= "<td>&nbsp;</td>";	
		$titre_uniforme_list .=  "</tr>";
			
	} // fin while

	$url_base = $url_base.'&authority_statut='.$authority_statut;
	if (!$last_param) $nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
        else $nav_bar = "";
		
	// affichage du résultat
	list_titres_uniformes($user_input, $titre_uniforme_list, $nav_bar);

} else {
	// la requête n'a produit aucun résultat
	titre_uniforme::search_form();
	error_message($msg[211], str_replace('!!author_cle!!', stripslashes($user_input), $msg["aut_titre_uniforme_no_result"]), 0, './autorites.php?categ=titres_uniformes&sub=&id=');
}

function list_titres_uniformes($cle, $titre_uniforme_list, $nav_bar) {
	global $titres_uniformes_list_tmpl;
	global $charset ;
	
	$titres_uniformes_list_tmpl = str_replace("!!cle!!", htmlentities(stripslashes($cle),ENT_QUOTES, $charset), $titres_uniformes_list_tmpl);
	$titres_uniformes_list_tmpl = str_replace("!!list!!", $titre_uniforme_list, $titres_uniformes_list_tmpl);
	$titres_uniformes_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $titres_uniformes_list_tmpl);
	titre_uniforme::search_form();
	print pmb_bidi($titres_uniformes_list_tmpl);
}


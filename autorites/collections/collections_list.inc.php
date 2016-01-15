<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: collections_list.inc.php,v 1.38 2015-12-04 14:58:41 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//collections_list.inc : liste les éditeurs correspondants à la regex user_input
// affichage de la liste collections pour sélection

// nombre de références par pages
if ($nb_per_page_collection != "") 
	$nb_per_page = $nb_per_page_collection ;
	else $nb_per_page = 10;

// initialisation variables
$nav_bar = '';
$collection_list = '';

// traitement de la saisie utilisateur

include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once($class_path."/analyse_query.class.php");
require_once($class_path.'/searcher/searcher_authorities_collections.class.php');

if($user_input) {
	//a priori pas utile. Armelle
	$clef = reg_diacrit($user_input);
} else {
	$user_input = '*';
}

// $collection_list_tmpl : template pour la liste editeurs
$collection_list_tmpl = "
<br />
<br />
<div class='row'>
	<h3><! --!!nb_autorite_found!!-- >$msg[173] !!cle!! </h3>
	</div>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		!!list!!
	</table>
<div class='row'>
	!!nav_bar!!
	</div>
";

$collections_searcher = new searcher_authorities_collections(stripslashes($user_input));
$nbr_lignes = $collections_searcher->get_nb_results();

function list_collection($coll, $collection_list, $nav_bar) {
	global $collection_list_tmpl;
	global $charset;
	$collection_list_tmpl = str_replace("!!cle!!", $coll, $collection_list_tmpl);
	$collection_list_tmpl = str_replace("!!list!!", $collection_list, $collection_list_tmpl);
	$collection_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $collection_list_tmpl);
	collection::search_form();
	print pmb_bidi($collection_list_tmpl);
}

if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;

if($nbr_lignes) {
	$collection_list_tmpl=str_replace( "<! --!!nb_autorite_found!!-- >",$nbr_lignes.' ',$collection_list_tmpl);	
	
	$collection_list .= "<tr>
		<th></th>
		<th>".$msg[103]."</th>
		<th>".$msg[165]."</th>
		<!--!!col_num_autorite!!-->
		<th>".$msg["count_notices_assoc"]."</th>
		</tr>";
	
	$num_auth_present=false;
	$req="SELECT id_authority_source FROM authorities_sources WHERE authority_type='collection' AND TRIM(authority_number) !='' LIMIT 1";
	$res_aut=pmb_mysql_query($req,$dbh);
	if($res_aut && pmb_mysql_num_rows($res_aut)){
		$collection_list=str_replace("<!--!!col_num_autorite!!-->","<th>".$msg["authorities_number"]."</th>",$collection_list);
		$num_auth_present=true;
	}
	
	$parity=1;
	$url_base = "./autorites.php?categ=collections&sub=reach&user_input=".rawurlencode(stripslashes($user_input)) ;
	$sorted_collections = $collections_searcher->get_sorted_result('default', $debut, $nb_per_page);
	
	foreach ($sorted_collections as $authority_id) {
		// On va chercher les infos spécifique à l'autorité
		$authority = new authority($authority_id);
		$collection_id = $authority->get_num_object();
		$coll = $authority->get_object_instance();
		
		if ($parity % 2) {
			$pair_impair = "even";
		} else {
			$pair_impair = "odd";
		}
		$parity += 1;

		$notice_count_sql = "SELECT count(*) FROM notices WHERE coll_id = ".$collection_id;
		$notice_count = pmb_mysql_result(pmb_mysql_query($notice_count_sql), 0, 0);
		
        $tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\"  ";
                $collection_list.= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'>";
                $collection_list.= "<td style='text-align:center; width:25px;'>
        						<a title='".$msg['authority_list_see_label']."' href='./autorites.php?categ=see&sub=collection&id=".$collection_id."'>
        							<i class='fa fa-eye'></i>
        						</a>
        					</td>";
                $collection_list.= "<td valign='top' onmousedown=\"document.location='./autorites.php?categ=collections&sub=collection_form&id=$collection_id&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page';\">";
                //$collection_list.= "<td valign='top' onmousedown=\"document.location='./autorites.php?categ=see&sub=collection&id=$collection_id';\">";                
                $collection_list.= htmlentities($coll->display,ENT_QUOTES, $charset);
		$collection_list .= "</td>
							<td>".htmlentities($coll->issn,ENT_QUOTES, $charset)."</td>";
		
		//Numéros d'autorite
		if($num_auth_present){
			$requete="SELECT authority_number,origin_authorities_name, origin_authorities_country FROM authorities_sources JOIN origin_authorities ON num_origin_authority=id_origin_authorities WHERE authority_type='collection' AND num_authority='".$collection_id."' AND TRIM(authority_number) !='' GROUP BY authority_number,origin_authorities_name,origin_authorities_country ORDER BY authority_favorite DESC, origin_authorities_name";
			$res_aut=pmb_mysql_query($requete,$dbh);
			if($res_aut && pmb_mysql_num_rows($res_aut)){
				$collection_list .= "<td>";
				$first=true;
				while ($aut = pmb_mysql_fetch_object($res_aut)) {
					if(!$first)$collection_list .=", ";
					$collection_list .=htmlentities($aut->authority_number,ENT_QUOTES,$charset);
					if($tmp=trim($aut->origin_authorities_name)){
						$collection_list .=htmlentities(" (".$aut->origin_authorities_name.")",ENT_QUOTES,$charset);
					}
					$first=false;
				}
				$collection_list .= "</td>";
			}else{
				$collection_list .= "<td>&nbsp;</td>";
			}
		}
		
		if($notice_count && $notice_count!=0)
			$collection_list .=  "<td onmousedown=\"document.location='./catalog.php?categ=search&mode=2&etat=aut_search&aut_type=collection&aut_id=$collection_id'\">".$notice_count."</td>";
		else $collection_list .= "<td>&nbsp;</td>";
		$collection_list .=  "</tr>";
	} // fin while
	$url_base = $url_base.'&authority_statut='.$authority_statut;
	if (!$last_param) $nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
		else $nav_bar="";   

	// affichage du résultat
	list_collection($user_input, $collection_list, $nav_bar);

} else {
	// la requête n'a produit aucun résultat
	collection::search_form();
	error_message($msg[175], str_replace('!!cle!!', stripslashes($user_input), $msg[174]), 0, './autorites.php?categ=collections&sub=&id=');
}



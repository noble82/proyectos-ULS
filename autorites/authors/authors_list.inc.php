<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authors_list.inc.php,v 1.46 2015-12-04 14:58:41 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// nombre de références par pages
if ($nb_per_page_author != "") 
	$nb_per_page = $nb_per_page_author ;
	else $nb_per_page = 10;

// traitement de la saisie utilisateur
include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once($class_path."/analyse_query.class.php");
require_once($class_path.'/searcher/searcher_authorities_authors.class.php');

if($user_input) {
	//a priori pas utile. Armelle
	$clef = reg_diacrit($user_input);
} else {
	$user_input = '*';
}

$authors_searcher = new searcher_authorities_authors(stripslashes($user_input));
$nbr_lignes = $authors_searcher->get_nb_results();

//On teste à quelle type d'autorités on a affaire pour les traitements suivants
switch($type_autorite){
	
	case 70 :
		//personne physique
		$libelleResult = $msg[209];
		break;
		
	case 71 :
		//collectivité
		$libelleResult = $msg["aut_resul_collectivite"];
		break;
		
	case 72 :
		//congrès
		$libelleResult = $msg["aut_resul_congres"];
		break;
	
	default:
		$libelleResult = $msg[209];
		break;
}	
	
// $authors_list_tmpl : template pour la liste auteurs
$authors_list_tmpl = "
<br />
<br />
<div class='row'>
	<h3><! --!!nb_autorite_found!!-- >$libelleResult !!cle!! </h3>
	</div>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		!!list!!
	</table>
<div class='row'>
	!!nav_bar!!
	</div>
";

if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;

if($nbr_lignes) {
	$authors_list_tmpl=str_replace( "<! --!!nb_autorite_found!!-- >",$nbr_lignes.' ',$authors_list_tmpl);
	$url_base = "./autorites.php?categ=auteurs&sub=reach&user_input=".rawurlencode(stripslashes($user_input));
	
	$author_list = "<tr>
			<th></th>
			<th>".$msg['103']."</th>
			<!--!!col_num_autorite!!-->
			<th>".$msg["count_notices_assoc"]."</th>
		</tr>";
	
	$num_auth_present=false;
	$req="SELECT id_authority_source FROM authorities_sources WHERE authority_type='author' AND TRIM(authority_number) !='' LIMIT 1";
	$res_aut=pmb_mysql_query($req,$dbh);
	if($res_aut && pmb_mysql_num_rows($res_aut)){
		$author_list=str_replace("<!--!!col_num_autorite!!-->","<th>".$msg["authorities_number"]."</th>",$author_list);
		$num_auth_present=true;
	}
	
	$sorted_authors = $authors_searcher->get_sorted_result('default', $debut, $nb_per_page);
	$parity=1;
	foreach ($sorted_authors as $authority_id) {
		// On va chercher les infos spécifique à l'autorité
		$authority = new authority($authority_id);
		$author_id = $authority->get_num_object();
		$aut = $authority->get_object_instance(array('recursif' => 1));
		
		$author_entry=$aut->isbd_entry;
		$link_auteur = "./autorites.php?categ=auteurs&sub=author_form&id=".$author_id."&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=".$nbr_lignes."&page=".$page;
		//$link_auteur = "./autorites.php?categ=see&sub=author&id=$author_id";
		if($aut->see) {
			// auteur avec renvoi
			// récupération des données de l'auteur cible
			$see = authorities_collection::get_authority(AUT_TABLE_AUTHORS, $aut->see, array('recursif' => 1));
			$author_voir=$see->isbd_entry;

			//$author_voir = "<a href='./autorites.php?categ=auteurs&sub=author_form&id=$aut->see&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page'>$author_voir</a>";
			$author_voir = "<a href='./autorites.php?categ=see&sub=author&id=$aut->see'>$author_voir</a>";
			$author_entry .= ".&nbsp;-&nbsp;<u>$msg[210]</u>&nbsp;:&nbsp;".$author_voir;
		}
		
		$notice_count_sql = "SELECT count(distinct responsability_notice) FROM responsability WHERE responsability_author = ".$author_id;
		$notice_count = pmb_mysql_result(pmb_mysql_query($notice_count_sql), 0, 0);
			
		if ($parity % 2) {
			$pair_impair = "even";
		} else {
			$pair_impair = "odd";
		}
		$parity += 1;
	    $tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" ";
        $author_list .= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer' > 
        				<td style='text-align:center; width:25px;'>
	        				<a title='".$msg['authority_list_see_label']."' href='./autorites.php?categ=see&sub=author&id=".$author_id."'>
	        					<i class='fa fa-eye'></i>
	        				</a>
        				</td>
              			<td valign='top' onmousedown=\"document.location='$link_auteur';\" title='".$aut->info_bulle."'>
						$author_entry
						</td>";
		
		//Numéros d'autorite
		if($num_auth_present){
			$requete="SELECT authority_number,origin_authorities_name, origin_authorities_country FROM authorities_sources JOIN origin_authorities ON num_origin_authority=id_origin_authorities WHERE authority_type='author' AND num_authority='".$author_id."' AND TRIM(authority_number) !='' GROUP BY authority_number,origin_authorities_name,origin_authorities_country ORDER BY authority_favorite DESC, origin_authorities_name";
			$res_aut=pmb_mysql_query($requete,$dbh);
			if($res_aut && pmb_mysql_num_rows($res_aut)){
				$author_list .= "<td>";
				$first=true;
				while ($aut = pmb_mysql_fetch_object($res_aut)) {
					if(!$first)$author_list .=", ";
					$author_list .=htmlentities($aut->authority_number,ENT_QUOTES,$charset);
					if($tmp=trim($aut->origin_authorities_name)){
						$author_list .=htmlentities(" (".$aut->origin_authorities_name.")",ENT_QUOTES,$charset);
					}
					$first=false;
				}
				$author_list .= "</td>";
			}else{
				$author_list .= "<td>&nbsp;</td>";
			}
		}	
						
		if($notice_count && $notice_count!=0){
			$author_list .= "<td onmousedown=\"document.location='./catalog.php?categ=search&mode=0&etat=aut_search&aut_id=$author_id';\">".($notice_count)."</td>";
		}else{
			$author_list .= "<td>&nbsp;</td>";
		}					
		$author_list .= "</tr>";
			
	}

	$url_base = $url_base."&type_autorite=".$type_autorite.'&authority_statut='.$authority_statut;
	if (!$last_param) $nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
        else $nav_bar = "";
		
	// affichage du résultat
	list_authors($user_input, $author_list, $nav_bar,$type_autorite);

} else {
	// la requête n'a produit aucun résultat
	auteur::search_form($type_autorite);
	error_message($msg[211], str_replace('!!author_cle!!', stripslashes($user_input), $msg[212]), 0, './autorites.php?categ=auteurs&sub=&id=');
}

function list_authors($cle, $author_list, $nav_bar,$type_autorite) {
	global $authors_list_tmpl;
	global $charset ;	
	$authors_list_tmpl = str_replace("!!cle!!", htmlentities(stripslashes($cle),ENT_QUOTES, $charset), $authors_list_tmpl);
	$authors_list_tmpl = str_replace("!!list!!", $author_list, $authors_list_tmpl);
	$authors_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $authors_list_tmpl);
	auteur::search_form($type_autorite);
	print pmb_bidi($authors_list_tmpl);
}


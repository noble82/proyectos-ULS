<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search.inc.php,v 1.50 2015-12-04 14:58:40 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

$url_base = "./autorites.php?categ=categories&sub=&id=0&parent=";

// inclusions diverses
include("$include_path/templates/category.tpl.php");
require_once("$class_path/category.class.php");
require_once("$class_path/analyse_query.class.php");
require_once("$class_path/thesaurus.class.php");
require_once($class_path.'/searcher/searcher_authorities_categories.class.php');

// search.inc : recherche des catégories en gestion d'autorités

//Récuperation de la liste des langues définies pour l'interface
$langages = new XMLlist("$include_path/messages/languages.xml", 1);
$langages->analyser();
$lg = $langages->table;

if (!$user_input) $user_input = '*';


//affichage du selectionneur de thesaurus et du lien vers les thésaurus
$liste_thesaurus = thesaurus::getThesaurusList();
$sel_thesaurus = '';
$lien_thesaurus = '';

if ($thesaurus_mode_pmb != 0) {	 //la liste des thesaurus n'est pas affichée en mode monothesaurus
	$sel_thesaurus = "<select class='saisie-30em' id='id_thes' name='id_thes' ";
	$sel_thesaurus.= "onchange = \"document.location = '".$url_base."&id_thes='+document.getElementById('id_thes').value; \">" ;
	foreach($liste_thesaurus as $id_thesaurus=>$libelle_thesaurus) {
		$sel_thesaurus.= "<option value='".$id_thesaurus."' "; ;
		if ($id_thesaurus == $id_thes) $sel_thesaurus.= " selected";
		$sel_thesaurus.= ">".htmlentities($libelle_thesaurus,ENT_QUOTES, $charset)."</option>";
	}
	$sel_thesaurus.= "<option value=-1 ";
	if ($id_thes == -1) $sel_thesaurus.= "selected ";
	$sel_thesaurus.= ">".htmlentities($msg['thes_all'],ENT_QUOTES, $charset)."</option>";
	$sel_thesaurus.= "</select>&nbsp;";

	$lien_thesaurus = "<a href='./autorites.php?categ=categories&sub=thes'>".$msg[thes_lien]."</a>";

}	
$user_query=str_replace("<!-- sel_thesaurus -->",$sel_thesaurus,$user_query);
$user_query=str_replace("<!-- lien_thesaurus -->",$lien_thesaurus,$user_query);
$user_query = str_replace("<!-- sel_authority_statuts -->", authorities_statuts::get_form_for(AUT_TABLE_CATEG, $authority_statut, true), $user_query);

//affichage du choix de langue pour la recherche
$sel_langue = '';
$sel_langue = "<div class='row'>";
$sel_langue.= "<input type='checkbox' name='lg_search' id='lg_search' value='1' ";
if($lg_search == 1){
	$sel_langue .= " checked='checked' ";
}
$sel_langue.= "/>&nbsp;".htmlentities($msg['thes_sel_langue'],ENT_QUOTES, $charset);
$sel_langue.= "</div><br />";
$user_query=str_replace("<!-- sel_langue -->",$sel_langue,$user_query);
$user_query=str_replace("!!user_input!!",htmlentities(stripslashes($user_input),ENT_QUOTES, $charset),$user_query);

//recuperation du thesaurus session 
if(!$id_thes) {
	$id_thes = thesaurus::getSessionThesaurusId();
} else {
	thesaurus::setSessionThesaurusId($id_thes);
}

// nombre de références par pages
if ($nb_per_page_author != "") 
	$nb_per_page = $nb_per_page_author ;
	else $nb_per_page = 10;

// traitement de la saisie utilisateur
include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");


// $authors_list_tmpl : template pour la liste auteurs
$categ_list_tmpl = "
<br />
<br />
<div class='row'>
	<h3><! --!!nb_autorite_found!!-- >$msg[1320] !!cle!! </h3>
	</div>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		!!list!!
	</table>
<div class='row'>
	!!nav_bar!!
	</div>
";

$categ_searcher = new searcher_authorities_categories(stripslashes($user_input));
$nbr_lignes = $categ_searcher->get_nb_results();

function list_categ($cle, $categ_list, $nav_bar) {
	global $categ_list_tmpl;
	$categ_list_tmpl = str_replace("!!cle!!", $cle, $categ_list_tmpl);
	$categ_list_tmpl = str_replace("!!list!!", $categ_list, $categ_list_tmpl);
	$categ_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $categ_list_tmpl);
	categ_browser::search_form();
	print pmb_bidi($categ_list_tmpl);
}	

if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;

if($nbr_lignes) {
	$categ_list_tmpl=str_replace( "<! --!!nb_autorite_found!!-- >",$nbr_lignes.' ',$categ_list_tmpl);	

	$parity=1;
	
	$categ_list .= "<tr>
		<th></th>
		<th>".$msg[103]."</th>
		<th>".$msg["categ_commentaire"]."</th>
		<!--!!col_num_autorite!!-->
		<th>".$msg["count_notices_assoc"]."</th>
		</tr>";
	
	$num_auth_present=false;
	$req="SELECT id_authority_source FROM authorities_sources WHERE authority_type='category' AND TRIM(authority_number) !='' LIMIT 1";
	$res_aut=pmb_mysql_query($req,$dbh);
	if($res_aut && pmb_mysql_num_rows($res_aut)){
		$categ_list=str_replace("<!--!!col_num_autorite!!-->","<th>".$msg["authorities_number"]."</th>",$categ_list);
		$num_auth_present=true;
	}

	$sorted_categ = $categ_searcher->get_sorted_result('default', $debut, $nb_per_page);
	foreach ($sorted_categ as $authority_id) {
		// On va chercher les infos spécifique à l'autorité
		$authority = new authority($authority_id);
		$categ_id = $authority->get_num_object();
		$categ = $authority->get_object_instance();
		
		if ($id_thes == -1) {
			$display = '['.htmlentities($categ->thes->libelle_thesaurus,ENT_QUOTES, $charset).']';
		} else {
			$display = '';
		}
		if ($lg_search) $display.= '['.$lg[$categ->langue].'] ';
		if($categ->voir_id) {
			$temp = authorities_collection::get_authority(AUT_TABLE_CATEG, $categ->voir_id);
			$display .= $categ->libelle." -&gt; <i>";
			$display .= $temp->catalog_form;
			$display.= "@</i>";
		} else {
			$display .= $categ->catalog_form;
		}	

		$notice_count = $categ->notice_count(false);
		
		$categ_entry = $display ;
		$categ_comment = $categ->commentaire;
 		$link_categ = "./autorites.php?categ=categories&sub=categ_form&parent=0&id=".$categ_id."&id_thes=".$categ->thes->id_thesaurus."&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=".$nbr_lignes."&page=".$page."&nb_per_page=".$nb_per_page;
//		$link_categ = "./autorites.php?categ=see&sub=category&id=".$categ_id;
		
		if ($parity % 2) {
			$pair_impair = "even";
		} else {
			$pair_impair = "odd";
		}
		
		$parity += 1;
		$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\"  ";
        $categ_list .= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'>
        				<td style='text-align:center; width:25px;'>
        					<a title='".$msg['authority_list_see_label']."' href='./autorites.php?categ=see&sub=category&id=".$categ_id."'>
        						<i class='fa fa-eye'></i>
        					</a>
        		    	</td>
        				<td valign='top' onmousedown=\"document.location='$link_categ';\">
						$categ_entry
						</td>
						<td valign='top' onmousedown=\"document.location='$link_categ';\">
						$categ_comment
						</td>";
		
		//Numéros d'autorite
		if($num_auth_present){
			$requete="SELECT authority_number,origin_authorities_name, origin_authorities_country FROM authorities_sources JOIN origin_authorities ON num_origin_authority=id_origin_authorities WHERE authority_type='category' AND num_authority='".$categ_id."' AND TRIM(authority_number) !='' GROUP BY authority_number,origin_authorities_name,origin_authorities_country ORDER BY authority_favorite DESC, origin_authorities_name";
			$res_aut=pmb_mysql_query($requete,$dbh);
			if($res_aut && pmb_mysql_num_rows($res_aut)){
				$categ_list .= "<td>";
				$first=true;
				while ($aut = pmb_mysql_fetch_object($res_aut)) {
					if(!$first)$categ_list .=", ";
					$categ_list .=htmlentities($aut->authority_number,ENT_QUOTES,$charset);
					if($tmp=trim($aut->origin_authorities_name)){
						$categ_list .=htmlentities(" (".$aut->origin_authorities_name.")",ENT_QUOTES,$charset);
					}
					$first=false;
				}
				$categ_list .= "</td>";
			}else{
				$categ_list .= "<td>&nbsp;</td>";
			}
		}
		
		if($notice_count && $notice_count!=0)	
			$categ_list .= "<td onmousedown=\"document.location='./catalog.php?categ=search&mode=1&etat=aut_search&aut_type=categ&aut_id=".$categ_id."'\">".$notice_count."</a></td>";
		else $categ_list .= "<td>&nbsp;</td>";
		$categ_list .= "</tr>";
			
	} // fin while

	//Création barre de navigation
	$url_base='./autorites.php?categ=categories&sub=search&id_thes='.$id_thes.'&user_input='.rawurlencode(stripslashes($user_input)).'&lg_search='.$lg_search;
	if (!$last_param) $nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
        else $nav_bar = "";
	
	// affichage du résultat
	list_categ(stripslashes($user_input), $categ_list, $nav_bar);

} else {
	// la requête n'a produit aucun résultat
	categ_browser::search_form($parent);
	error_message($msg[211], str_replace('!!categ_cle!!', stripslashes($user_input), $msg["categ_no_categ_found_with"]), 0, './autorites.php?categ=categories&sub=search');
}
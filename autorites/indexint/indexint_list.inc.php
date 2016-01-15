<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexint_list.inc.php,v 1.40 2015-12-04 14:58:41 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

function list_indexint($cle, $indexint_list, $nav_bar) {
	global $indexint_list_tmpl;
	global $charset,$id_pclass;
	$indexint_list_tmpl = str_replace("!!cle!!", $cle, $indexint_list_tmpl);
	$indexint_list_tmpl = str_replace("!!list!!", $indexint_list, $indexint_list_tmpl);
	$indexint_list_tmpl = str_replace("!!nav_bar!!", $nav_bar, $indexint_list_tmpl);

	indexint::search_form($id_pclass);
	print pmb_bidi($indexint_list_tmpl);
}

// nombre de r�f�rences par pages
if ($nb_per_page_serie != "") 
	$nb_per_page = $nb_per_page_serie ;
	else $nb_per_page = 10;

// traitement de la saisie utilisateur
include("$include_path/marc_tables/$pmb_indexation_lang/empty_words");
require_once($class_path."/analyse_query.class.php");
require_once($class_path.'/searcher/searcher_authorities_indexint.class.php');

if ($user_input) {
	//a priori pas utile. Armelle
	$clef = reg_diacrit($user_input);
} else {
	$user_input = '*';
}


// $indexint_list_tmpl : template pour la liste
$indexint_list_tmpl = "
<br />
<br />
<div class='row'>
	<h3><! --!!nb_autorite_found!!-- >$msg[indexint_found] !!cle!! </h3>
	</div>
	<script type='text/javascript' src='./javascript/sorttable.js'></script>
	<table class='sortable'>
		!!list!!
	</table>
<div class='row'>
	!!nav_bar!!
	</div>";

$indexint_searcher = new searcher_authorities_indexint(stripslashes($user_input));
$fields_restrict = array();
if (!$exact) {
	$fields_restrict[]= array(
			'field' => "code_champ",
			'values' => array(8002),
			'op' => "and",
			'not' => false
	);
} else {
	$fields_restrict[]= array(
			'field' => "code_champ",
			'values' => array(8001),
			'op' => "and",
			'not' => false
	);
}
$indexint_searcher->add_fields_restrict($fields_restrict);

$nbr_lignes = $indexint_searcher->get_nb_results();

if ($thesaurus_classement_mode_pmb != 0) {
	if($id_pclass==0) {
		$pclass_url="";	
	} else {
		$pclass_url="&id_pclass=$id_pclass";
	}	
} else {
	$pclass_url="&id_pclass=$thesaurus_classement_defaut";	
}

if(!$page) $page=1;
$debut =($page-1)*$nb_per_page;



if($nbr_lignes) {
	$indexint_list_tmpl=str_replace( "<! --!!nb_autorite_found!!-- >",$nbr_lignes.' ',$indexint_list_tmpl);
	
	$indexint_list = "<tr>
		<th></th>
		<th>".$msg[103]."</th>
		<th>".$msg[707]."</th>
		<th>".$msg["count_notices_assoc"]."</th>
		</tr>";
	
	$sorted_indexint = $indexint_searcher->get_sorted_result('default', $debut, $nb_per_page);
	
	$parity=1;
	$url_base = "./autorites.php?categ=indexint&sub=reach$pclass_url&user_input=".rawurlencode(stripslashes($user_input))."&exact=$exact" ;
	
	foreach ($sorted_indexint as $authority_id) {
		// On va chercher les infos sp�cifique � l'autorit�
		$authority = new authority($authority_id);
		$indexint_id = $authority->get_num_object();
		$indexint = $authority->get_object_instance();
		
		if ($parity % 2) $pair_impair = "even"; else $pair_impair = "odd";
		$parity += 1;
		
		$notice_count_sql = "SELECT count(*) FROM notices WHERE indexint = ".$indexint_id;
		$notice_count = pmb_mysql_result(pmb_mysql_query($notice_count_sql), 0, 0);
		
        $tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\"  ";
        
    	if($thesaurus_classement_mode_pmb!=0){
			$pclass_name="[".$indexint->name_pclass."] ";
    	}	
    
    	$indexint_list.= "<tr class='$pair_impair' $tr_javascript style='cursor: pointer'>";
    	$indexint_list.= "<td style='text-align:center; width:25px;'>
        					<a title='".$msg['authority_list_see_label']."' href='./autorites.php?categ=see&sub=indexint&id=".$indexint_id."'>
        						<i class='fa fa-eye'></i>
        					</a>
        		    	</td>";
		$indexint_list.= "<td valign='top' onmousedown=\"document.location='./autorites.php?categ=indexint&sub=indexint_form&id=$indexint_id&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page&exact=$exact$pclass_url';\">";
    	//$indexint_list.= "<td valign='top' onmousedown=\"document.location='./autorites.php?categ=see&sub=indexint&id=$indexint_id';\">";
		$indexint_list.= $pclass_name;
		$indexint_list.= htmlentities($indexint->name,ENT_QUOTES, $charset)."</td><td valign='top' onmousedown=\"document.location='./autorites.php?categ=indexint&sub=indexint_form&id=$indexint_id&user_input=".rawurlencode(stripslashes($user_input))."&nbr_lignes=$nbr_lignes&page=$page&exact=$exact$pclass_url';\">".htmlentities($indexint->comment,ENT_QUOTES, $charset)."</td>";
		//$indexint_list.= htmlentities($indexint->name,ENT_QUOTES, $charset)."</td><td valign='top' onmousedown=\"document.location='./autorites.php?categ=see&sub=indexint&id=$indexint_id';\">".htmlentities($indexint->comment,ENT_QUOTES, $charset)."</td>";
		if($notice_count && $notice_count!=0)
			 $indexint_list .= "<td onmousedown=\"document.location='./catalog.php?categ=search&mode=1&etat=aut_search&aut_type=indexint&aut_id=$indexint_id'\">".$notice_count."</td>";
		else $indexint_list .= "<td>&nbsp;</td>";
		$indexint_list .= "</tr>";	
	}
	$url_base.='&authority_statut='.$authority_statut;
	if (!$last_param) $nav_bar = aff_pagination ($url_base, $nbr_lignes, $nb_per_page, $page, 10, false, true) ;
        else $nav_bar = "";
        
	// affichage du r�sultat
	if ($user_input) {
		if ($exact)
			$c_user_input= $msg["rech_exacte"];
		else
			$c_user_input=$msg["rech_commentaire"];
	}
	list_indexint($user_input.$c_user_input, $indexint_list, $nav_bar);

} else {
	// la requ�te n'a produit aucun r�sultat
	indexint::search_form($id_pclass);
	error_message($msg["indexint_search"], str_replace('!!titre_cle!!', stripslashes($user_input), $msg["indexint_noresult"]), 0, './autorites.php?categ=indexint&sub=&id=');
}

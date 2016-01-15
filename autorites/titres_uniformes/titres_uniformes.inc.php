<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: titres_uniformes.inc.php,v 1.15 2015-12-21 15:41:49 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// fonctions communes aux pages de gestion des autorités
require_once('./autorites/auth_common.inc.php');

// classe de gestion des titres uniformes
require_once("$class_path/titre_uniforme.class.php");
require_once("$include_path/templates/titres_uniformes.tpl.php");

// gestion des titres uniformes
print "<h1>".$msg[140]."&nbsp;: ". $msg["aut_menu_titre_uniforme"]."</h1>";

switch($sub) {
	case 'reach':
		include('./autorites/titres_uniformes/titres_uniformes_list.inc.php');
		break;
	case 'delete':
		$titre_uniforme = new titre_uniforme($id);
		$sup_result = $titre_uniforme->delete();
		if(!$sup_result)
			include('./autorites/titres_uniformes/titres_uniformes_list.inc.php');
		else {
			error_message($msg[132], $sup_result, 1, "./autorites.php?categ=titres_uniformes&sub=titre_uniforme_form&id=$id");
		}
		break;
	case 'replace':
		if(!$by) {
			$titre_uniforme = new titre_uniforme($id);
			$titre_uniforme->replace_form();
		} else {
			// routine de remplacement
			$titre_uniforme = new titre_uniforme($id);
			$rep_result = $titre_uniforme->replace($by,$aut_link_save);
			if(!$rep_result)
				include('./autorites/titres_uniformes/titres_uniformes_list.inc.php');
			else {
				error_message($msg[132], $rep_result, 1, "./autorites.php?categ=titres_uniformes&sub=titre_uniforme&id=$id");
			}
		}
		break;
	case 'duplicate' :
		$titre_uniforme = new titre_uniforme ($id);
		$id = 0;
		$titre_uniforme->show_form(true);	
		break;
	case 'update':
		// mettre à jour responsabilité id
		// mise à jour d'un auteur
		$titre_uniforme_val = array(				
				'name' 			=> $name,
				'tonalite' 		=> $tonalite,
				'tonalite_selector' => $form_tonalite_selector,
				'comment'		=> $comment,
				'import_denied'	=> $tu_import_denied,
				'num_author' 	=> $form_author_id,
				'form' 			=> $form,
				'form_selector' => $form_form_selector,
				'date' 			=> $date,
				'subject' 		=> $subject,
				'place' 		=> $place,
				'history' 		=> $history,
				'characteristic'		=> $characteristic,
				'intended_termination' 	=> $intended_termination,
				'intended_audience' 	=> $intended_audience,
				'context' 		=> $context,
				'coordinates' 	=> $coordinates,
				'equinox' 		=> $equinox,
				'oeuvre_nature' => $oeuvre_nature,
				'oeuvre_type' 	=> $oeuvre_type,
				'statut'=> $authority_statut);
		
		$titre_uniforme_val['oeuvre_expression']=array();
		for ($i=0; $i< $max_oeuvre_expression ; $i++) {
			$var_oeuvre_expressioncode = "f_oeuvre_expression_code$i" ;
			$var_oeuvre_expressiontype = "f_oeuvre_expression_type$i" ;
			if ($$var_oeuvre_expressioncode) {
				$titre_uniforme_val['oeuvre_expression'][] = array(
					'code' => $$var_oeuvre_expressioncode,
					'type' => $$var_oeuvre_expressiontype
				);
			}
		}
		
		$titre_uniforme_val['oeuvre_event']=array();
		for ($i=0; $i< $max_oeuvre_event; $i++) {
			$var_oeuvre_eventcode = "f_oeuvre_event_code$i" ;
			$var_oeuvre_eventtype = "f_oeuvre_event_type$i" ;
			if ($$var_oeuvre_eventcode) {
				$titre_uniforme_val['oeuvre_event'][] = array(
					'code' => $$var_oeuvre_eventcode
				);
			}
		}
		
		$titre_uniforme_val['other_link']=array();
		$count=0;
		for ($i=0; $i< $max_other_link ; $i++) {
			$var_other_linkcode = "f_other_link_code$i";
			$var_oeuvre_others_link = "f_oeuvre_other_link$i";
			
			if ($$var_other_linkcode){
				$titre_uniforme_val['other_link'][$count]['type'] = $$var_oeuvre_others_link;
				$titre_uniforme_val['other_link'][$count]['code'] = $$var_other_linkcode;
				$count++;
			}
		}
		
		// Distribution instrumentale et vocale (pour la musique)		
		for($i=0;$i<=$max_distrib;$i++) {
			eval("\$val=\$f_distrib".$i.";");
			if($val) $titre_uniforme_val['distrib'][]= $val;
		}
		// Référence numérique (pour la musique)
		for($i=0;$i<=$max_ref;$i++) {
			eval("\$val=\$f_ref".$i.";");
			if($val) $titre_uniforme_val['ref'][]= $val;
		}		
		// Subdivision de forme
		for($i=0;$i<=$max_subdiv;$i++) {
			eval("\$val=\$f_subdiv".$i.";");
			if($val) $titre_uniforme_val['subdiv'][]= $val;
		}		
		$titre_uniforme = new titre_uniforme($id);
		$titre_uniforme->update($titre_uniforme_val);
		include('./autorites/titres_uniformes/titres_uniformes_list.inc.php');
		break;
	case 'titre_uniforme_form':
		// création/modification d'un titre_uniforme
		if(!$id) $titre_uniforme = new titre_uniforme(); // affichage du form pour création
			else $titre_uniforme = new titre_uniforme ($id); // affichage du form pour modification
		$titre_uniforme->show_form();
		break;
	case 'titre_uniforme_last':
		$last_param=1;
		$tri_param = "order by tu_id desc ";
		$limit_param = "limit 0, $pmb_nb_lastautorities ";
		$clef = "";
		$nbr_lignes = 0 ;
		include('./autorites/titres_uniformes/titres_uniformes_list.inc.php');
		break;
	default:
		// affichage du début de la liste
		include('./autorites/titres_uniformes/titres_uniformes_list.inc.php');
		break;
}

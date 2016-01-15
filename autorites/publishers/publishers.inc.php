<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: publishers.inc.php,v 1.12 2015-12-21 15:41:49 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

// on a besoin des templates �diteurs
include("$include_path/templates/editeurs.tpl.php");

// la classe de gestion des �diteurs
require_once("$class_path/editor.class.php");

// gestion des �diteurs

print "<h1>".$msg[140]."&nbsp;: ". $msg[135]."</h1>";
switch($sub) {
	case 'reach':
		include('./autorites/publishers/publishers_list.inc.php');
		break;
	case 'delete':
		$ed = new editeur($id);
		$sup_result = $ed->delete();
		if(!$sup_result) {
			include('./autorites/publishers/publishers_list.inc.php');
		} else {
			error_message($msg[132], $sup_result, 1, "./autorites.php?categ=editeurs&sub=editeur_form&id=$id");
		}
		break;

	case 'replace':
		if(!$ed_id) {
			$editeur = new editeur($id);
			$editeur->replace_form();
		} else {
			// routine de remplacement
			$editeur = new editeur($id);
			$rep_result = $editeur->replace($ed_id,$aut_link_save);
			if(!$rep_result) {
				include('./autorites/publishers/publishers_list.inc.php');
			} else { 
				error_message($msg[132], $rep_result, 1, "./autorites.php?categ=editeurs&sub=editeur_form&id=$id");
			}
		}
		break;
	case 'duplicate':
		$ed = new editeur($id);
		$id = 0;
		$ed->show_form(true);
		break;
	case 'update':
		// mettre � jour �diteur id
		// mise � jour d'un �diteur
		$ed = array(
				'name' => $ed_nom,
				'adr1' => $ed_adr1,
				'adr2' => $ed_adr2,
				'cp' => $ed_cp,
				'ville' => $ed_ville,
				'pays' => $ed_pays,
				'ed_comment'	=> $ed_comment,
				'statut'	=> $authority_statut,
				'web' => $ed_web);
		$editeur = new editeur($id);
		$editeur->update($ed);
		include('./autorites/publishers/publishers_list.inc.php');
		break;
		
	case 'editeur_form':
		// cr�ation d'un �diteur
		if(!$id) {
			// affichage du form pour cr�ation
			$editeur = new editeur(0);
			$editeur->show_form();
		} else {
			// affichage du form pour modification
			$editeur = new editeur($id);
			$editeur->show_form();
		}
		break;
		
	case 'editeur_last':
		$last_param=1;
		$tri_param = "order by ed_id desc ";
		$limit_param = "limit 0, $pmb_nb_lastautorities ";
		$clef = "";
		$nbr_lignes = 0 ;
		include('./autorites/publishers/publishers_list.inc.php');
		break;
		
	default:
		// affichage du d�but de la liste (par d�faut)
		include('./autorites/publishers/publishers_list.inc.php');
		break;
}

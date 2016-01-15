<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: select.php,v 1.32 2015-12-22 11:32:12 ngantier Exp $

// d�finition du minimum n�c�ssaire 
$base_path=".";                            
$base_auth = "";  
//Cas sp�cial pour les cat�gories
if ($_GET["what"]=="categorie") {
	$base_nobody=1;
} else {
	$base_title = "Selection";
}


require_once ("$base_path/includes/init.inc.php");  
require_once("$class_path/marc_table.class.php");
require_once("$class_path/analyse_query.class.php");

// modules propres � select.php ou � ses sous-modules
include_once ("$javascript_path/misc.inc.php");
require_once ("$base_path/includes/shortcuts/shortcuts.php");

print "<script type='text/javascript'>
	self.focus();
	</script>";
print reverse_html_entities();

switch($what) {
	case 'editeur':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/editeur.inc.php');
		break;
	case 'collection':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/collection.inc.php');
		break;
	case 'subcollection':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/subcollection.inc.php');
		break;
	case 'auteur':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/author.inc.php');
		break;
	case 'country':
		include('./selectors/country.inc.php');
		break;
	case 'lang':
		include('./selectors/lang.inc.php');
		break;
	case 'function':
		include('./selectors/func.inc.php');
		break;
	case 'categorie':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form" || !(SESSrights & THESAURUS_AUTH)){
			$bt_ajouter ="no";
		}
		include('./selectors/category_frame.inc.php');
		break;
	case 'serie':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/serie.inc.php');
		break;
	case 'indexint':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/indexint.inc.php');
		break;
	case 'calendrier':
		include ('./selectors/calendrier.inc.php');
		break;
	case 'emprunteur':
		include ('./selectors/empr.inc.php');
		break;
	case 'notice':
		include ('./selectors/notice.inc.php');
		break;
	case 'perio':
		include ('./selectors/perio.inc.php');
		break;
	case 'bulletin':
		include ('./selectors/bulletin.inc.php');
		break;		
	case 'codepostal':
		include ('./selectors/codepostal.inc.php');
		break;
	case 'perso':
		include('./selectors/perso.inc.php');
		break;
	case 'fournisseur':
		include('./selectors/fournisseur.inc.php');
		break;
	case 'coord' :
		include('./selectors/coordonnees.inc.php');
		break;
	case 'acquisition_notice':
		include('./selectors/acquisition_notice.inc.php');
		break;
	case 'types_produits':
		include('./selectors/types_produits.inc.php');
		break;
	case 'rubriques':
		include('./selectors/rubriques.inc.php');
		break;
	case 'origine':
		include('./selectors/origine.inc.php');
		break;		
	case 'synonyms':
		include('./selectors/sel_word.inc.php');
		break;	
	case 'titre_uniforme':
		if ((!(SESSrights & AUTORITES_AUTH)) || $caller == "search_form"){
			$bt_ajouter ="no";
		}
		include('./selectors/titre_uniforme.inc.php');
		break;
	case 'notes':
		include('./selectors/notes.inc.php');
		break;
	case 'ontology' :
		include('./selectors/ontology.inc.php');
		break;
	case 'ontologies' :
		include('./selectors/ontologies.inc.php');
		break;
	case 'authperso' :
		include('./selectors/authperso.inc.php');
		break;
	case 'abts' :
		include('./selectors/abts.inc.php');
		break;
	case 'groupexpl' :
		include('./selectors/groupexpl.inc.php');
		break;
	case 'oeuvre_event' :
		include('./selectors/oeuvre_event.inc.php');
		break;
	case 'music_key' :
		include('./selectors/music_key.inc.php');
		break;
	case 'music_form' :
		include('./selectors/music_form.inc.php');
		break;
	default:
		print "<script type='text/javascript'>
			window.close();
		</script>";
		break;
}

pmb_mysql_close($dbh);

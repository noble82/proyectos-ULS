<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.6 2015-12-02 09:16:33 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//En fonction de $categ, il inclut les fichiers correspondants

switch($categ):
	case 'commande':
		
	break;
	case 'type_empty_word':
		include('./autorites/semantique/ajax/type_empty_word.inc.php');
	break;
	case 'dashboard' :
		include("./dashboard/ajax_main.inc.php");
		break;
	case 'grid' :
		include("./autorites/grid/ajax_main.inc.php");
		break;
	case 'fill_form':
		include('./autorites/fill_form/ajax_main.inc.php');
		break;
	case 'get_tu_form_vedette':
		include('./autorites/titres_uniformes/tu_form_vedette.inc.php');
		break;
	default:
	//tbd
	break;		
endswitch;	

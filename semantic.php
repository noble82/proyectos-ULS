<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: semantic.php,v 1.1 2015-08-10 23:16:25 arenou Exp $

// définition du minimum nécéssaire 
$base_path=".";                            
$base_auth = "";  
$base_title = "\$msg[semantic]";
$base_use_dojo=1;

require_once ("$base_path/includes/init.inc.php");  
// ini_set('errors_display',1);
// error_reporting(E_ALL);
require_once($class_path."/autoloader.class.php");
$autoloader = new autoloader();
$autoloader->add_register("onto_class",true);
require_once($class_path."/ontologies.class.php");
require_once($include_path."/templates/semantic.tpl.php");
print "<div id='att' style='z-Index:1000'></div>";
print $menu_bar;
print $extra;
print $extra2;
print $extra_info;
if($use_shortcuts) {
	include("$include_path/shortcuts/circ.sht");
}

$ontologies = new ontologies();
$layout = str_replace("!!ontologies_menu!!",$ontologies->get_semantic_menu(),$semantic_layout);
print $layout;
if($ontology_id){
	$ontology = new ontology($ontology_id);
	$ontology->exec_data_framework();
}else{
	//TODO
	print "<div class='row'>PAGE D'AIDE</div>";
}
print $semantic_layout_end;
print $footer;
mysql_close($dbh);
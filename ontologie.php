<?php
// +--------------------------------------------------------------------------+
// | PMB est sous licence GPL, la réutilisation du code est cadrée            |
// +--------------------------------------------------------------------------+
// $Id: ontologie.php,v 1.1 2015-08-10 23:16:25 arenou Exp $

//Impression

$base_path = ".";
$base_auth = "ADMIN_AUTH";
$base_title = "";
$base_nobody=1;
$base_noheader=1;

require($base_path."/includes/init.inc.php");
require_once($class_path."/ontology.class.php");

$onto = new ontology($ontologie_id);
if($sparql){
	$onto->get_onto_endpoint();
}else if ($draw){
	
	$onto->draw_onto();
	
}else{
	if($get_data){
		$onto->print_datas_rdf();
	}else{
		$onto->print_onto_rdf();
	}
}
?>
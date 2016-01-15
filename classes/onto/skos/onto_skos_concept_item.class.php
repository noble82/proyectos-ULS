<?php
// +-------------------------------------------------+
// © 2002-2014 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_skos_concept_item.class.php,v 1.3 2015-11-30 14:06:17 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path.'/templates/onto/skos/onto_skos_concept_item.tpl.php');
require_once($class_path.'/authorities_statuts.class.php');

class onto_skos_concept_item extends onto_common_item {

	public function get_form($prefix_url="",$flag="",$action="save") {
		$form = parent::get_form($prefix_url,$flag,$action);
		if($flag != "concept_selector_form"){
			$aut_link= new aut_link(AUT_TABLE_CONCEPT,onto_common_uri::get_id($this->get_uri()));
			$form = str_replace('<!-- aut_link -->', $aut_link->get_form(onto_common_uri::get_name_from_uri($this->get_uri(), $this->onto_class->pmb_name)) , $form);
		    $form = str_replace('!!auth_statut_selector!!', authorities_statuts::get_form_for(AUT_TABLE_CONCEPT, $this->get_statut_id()), $form);
		}else {
			$form = str_replace('<!-- aut_link -->', "" , $form);
		}
		return $form;
	}
	
	public function get_statut_id(){
	    global $dbh;
	    $query_statut = 'select num_statut from authorities where num_object = "'.onto_common_uri::get_id($this->get_uri()).'" and type_object='.AUT_TABLE_CONCEPT;
	    $result = pmb_mysql_query($query_statut, $dbh);
	    $statut = 1;
	    if($result){
	        $data = pmb_mysql_fetch_object($result);
	        $statut = $data->num_statut;
	    }
	    return $statut;
	}
}
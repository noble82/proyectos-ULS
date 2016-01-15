<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: searcher_autorities_skos_concepts.class.php,v 1.12 2015-11-30 14:06:17 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class searcher_autorities_skos_concepts extends searcher_autorities {

	public function __construct($user_query){
		parent::__construct($user_query);
		$this->object_key = "id_item";
		$this->object_index_key= "id_item";
		$this->object_words_table = "skos_words_global_index";
		$this->object_fields_table = "skos_fields_global_index";
		$this->field_restrict[]= array(
				'field' => "code_champ",
				'values' => array(1,2,3),
				'op' => "and",
				'not' => false
		);
	}
	
	public function _get_search_type(){
		return parent::_get_search_type()."_concepts";
	}
	
 	// � r��crire au besoin...
 	protected function _sort($start,$number){
 		global $dbh;
 		if($this->table_tempo != ""){
 			$query = "select * from ".$this->table_tempo." join ".$this->object_fields_table." on ".$this->table_tempo.".".$this->object_key." = ".$this->object_fields_table.".".$this->object_index_key." where code_champ= 1 order by pert desc,".$this->object_fields_table.".".$this->object_fields_value." asc limit ".$start.",".$number;
 			$result = pmb_mysql_query($query,$dbh);
 			if(pmb_mysql_num_rows($result)){
 				$this->result=array();
 				while($row = pmb_mysql_fetch_object($result)){
 					$this->result[] = $row->{$this->object_key};
 				}
 			}
 		}
 	}
 	
 	protected function _filter_results(){
 		global $dbh, $concept_scheme, $authority_statut;
 		$query = "";

 		if (($concept_scheme !== null) && ($concept_scheme*1 === 0)) {
 			// On cherche dans les concepts sans sch�ma
 			$query = "select ".$this->object_key." from ".$this->object_fields_table." where ".$this->object_key." not in (select ".$this->object_key." from ".$this->object_fields_table." where code_champ = 4) and code_champ = 1 and ".$this->object_key." in (".$this->objects_ids.")";
 		} else if ($concept_scheme && ($concept_scheme != -1)) {
 			// On cherche dans un schema en particulier
 			$query = "select ".$this->object_key." from ".$this->object_fields_table." where code_champ = 4 and authority_num = ".($concept_scheme*1)." and ".$this->object_key." in (".$this->objects_ids.")";
 		}
 		if($authority_statut){
 			$authority_statut+=0;
 			$statut_query = 'select num_object as '.$this->object_key.' from authorities where type_object='.AUT_TABLE_CONCEPT.' and num_statut = '.$authority_statut;
 			if($query){
 				$query.= ' and '.$this->object_key.' in ('.$statut_query.')';
 			}else{
 				$query = $statut_query;
 			}
 		}
 		// Pas de filtre si on cherche dans tous les sch�mas
 		if ($query) {
 			$result = pmb_mysql_query($query,$dbh);
 			$this->objects_ids ="";
 			if($result && pmb_mysql_num_rows($result)){
 				while($row = pmb_mysql_fetch_object($result)){
 					if($this->objects_ids) $this->objects_ids.= ",";
 					$this->objects_ids.= $row->{$this->object_key};
 				}
 			}
 		}
	}

	protected function get_full_results_query(){
		global $concept_scheme;
		$query = "select ".$this->object_key." from ".$this->object_fields_table." where code_champ = 1";
		if ($concept_scheme*1 === 0) {
			// On cherche dans les concepts sans sch�ma
			$query.= " and ".$this->object_key." not in (select ".$this->object_key." from ".$this->object_fields_table." where code_champ = 4)";
		} else if ($concept_scheme && ($concept_scheme != -1)) {
			// On cherche dans un schema en particulier
			$query = " and ".$this->object_key." in (select ".$this->object_key." from ".$this->object_fields_table." where code_champ = 4 and authority_num = ".($concept_scheme*1);
		}
		return $query;	
	}
}
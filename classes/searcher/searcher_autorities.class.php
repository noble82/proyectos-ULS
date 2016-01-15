<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: searcher_autorities.class.php,v 1.10 2015-12-30 14:00:45 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/searcher/searcher_generic.class.php');


//un jour ca sera utile
class searcher_autorities extends searcher_generic {
	
	protected $authority_type = 0;
	
	protected $object_table = '';
	
	public function __construct($user_query) {
		parent::__construct($user_query);
		$this->object_index_key = "id_authority";
		$this->object_words_table = "authorities_words_global_index";
		$this->object_fields_table = "authorities_fields_global_index";
		$this->object_key = 'id_authority';
		if ($this->authority_type) {
			$this->field_restrict[]= array(
					'field' => "type",
					'values' => array($this->authority_type),
					'op' => "and",
					'not' => false
			);
		}
	}
	
	public function _get_search_type(){
		return "authorites";
	}
	
	protected function get_full_results_query(){
		if ($this->object_table) {
			return 'select id_authority from authorities join '.$this->object_table.' on authorities.num_object = '.$this->object_table_key;
		}
		return 'select id_authority from authorities';
	}
	
	protected function _get_authorities_filters(){
		global $authority_statut;
		
		$filters = array();
		if ($this->authority_type) {
			$filters[] = 'authorities.type_object = '.$this->authority_type;
		}
		if ($authority_statut) {
			$filters[] = 'authorities.num_statut = "'.$authority_statut.'"';
		}
		return $filters;
	}
	
	protected function _get_search_query(){
		$query = parent::_get_search_query();
		$filters = $this->_get_authorities_filters();

		if(($this->user_query != "*") && $this->authority_type && $this->object_table) {
			$filters[] = 'id_authority in ('.$query.')';
			$query = 'select id_authority from authorities join '.$this->object_table.' on authorities.num_object = '.$this->object_table_key;
		}
		
		if (count($filters)) {
			$query .= ' where '.implode(' and ', $filters);
		}
		
		if(($this->user_query != "*") && (get_class($this) == get_class())) {
			// Si cette classe est appel�e directement, on cherche dans toutes les autorit�s donc on va chercher les concepts
			$searcher_authorities_concepts = new searcher_authorities_concepts($this->user_query);
			$query = 'select id_authority from (('.$query.') union ('.$searcher_authorities_concepts->get_raw_query().')) as search_query_concepts';
		}
		return $query;
	}

	protected function _get_sign_elements($sorted=false) {
		global $authority_statut;
		$str_to_hash = parent::_get_sign_elements($sorted);
		$str_to_hash .= "&authority_statut=".$authority_statut;
		return $str_to_hash;
	}

	// � r��crire au besoin...
	protected function _sort($start,$number){
		global $dbh;
		global $last_param, $tri_param, $limit_param;
		if($this->table_tempo != ""){
			$query = "select * from ".$this->table_tempo." order by pert desc limit ".$start.",".$number;
			$res = pmb_mysql_query($query,$dbh);
			if(pmb_mysql_num_rows($res)){
				$this->result=array();
				while($row = pmb_mysql_fetch_object($res)){
					$this->result[] = $row->{$this->object_key};
				}
			}
		} else {
			if ($last_param) {
				$query = $this->_get_search_query().' '.$tri_param.' '.$limit_param;
			} else {
				$query = $this->_get_search_query().' '.$this->get_authority_tri().' limit '.$start.', '.$number;
			}
			$res = pmb_mysql_query($query,$dbh);
			if(pmb_mysql_num_rows($res)){
				$this->result=array();
				while($row = pmb_mysql_fetch_object($res)){
					$this->result[] = $row->id_authority;
				}
			}
		}
	}
	
	public function get_authority_tri() {
		// � surcharger si besoin
		return '';
	}

	protected function _sort_result($start,$number){
		if ($this->user_query != '*') {
			$this->_get_pert();
		}
		$this->_sort($start,$number);
	}
	
	public function get_raw_query()
	{
		$this->_analyse();
		return $this->_get_search_query();
	}
	
	public function get_pert_result($query = false) {
		$pert = '';
		if ($this->get_result() && ($this->user_query != '*')) {
			$pert = $this->_get_pert($query);
		}
		if ($query) {
			return $pert;
		}
		return $this->table_tempo;
	}
	
	protected function _get_pert($return_query = false) {
		$query = parent::_get_pert(true);
		
		if (get_class($this) == get_class()) {
			// Si cette classe est appel�e directement, on cherche dans toutes les autorit�s donc on va chercher les concepts
			$searcher_authorities_concepts = new searcher_authorities_concepts($this->user_query);
			$concepts_pert_result = $searcher_authorities_concepts->get_pert_result(true);
			if ($concepts_pert_result) {
				$query = 'select '.$this->object_key.', sum(pert) as pert from (('.$query.') union all ('.$concepts_pert_result.')) as search_query_concepts group by '.$this->object_key;
			}
			
		}
		
		if ($return_query) {
			return $query;
		}
		$this->table_tempo = 'search_result'.md5(microtime(true));
		$rqt = 'create temporary table '.$this->table_tempo.' '.$query;
		$res = pmb_mysql_query($rqt,$dbh);
		pmb_mysql_query('alter table '.$table.' add index i_id('.$this->object_key.')',$dbh);
	}
}
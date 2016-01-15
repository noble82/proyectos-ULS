<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_authority.class.php,v 1.5 2015-11-17 17:06:56 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/indexation.class.php");
require_once($class_path."/authority.class.php");

//classe de calcul d'indexation des autorités...
class indexation_authority extends indexation {

	protected $type = 0;
	
	public function __construct($xml_filepath, $table_prefix, $type) {
		parent::__construct($xml_filepath, $table_prefix);
		$this->type = $type;
	}
	
	public function get_type(){
		return $this->type;
	}
	
	public function set_type($type){
		$this->type = $type;
	}
	
	// compile les tableaux et lance les requetes
	protected function save_elements($tab_insert, $tab_field_insert){
		global $dbh;
		
		if(!$this->type) return false;
		
		$req_insert="insert into ".$this->table_prefix."_words_global_index(id_authority,type,code_champ,code_ss_champ,num_word,pond,position,field_position) values ".implode(',',$tab_insert);
		pmb_mysql_query($req_insert,$dbh);
		//la table pour les recherche exacte
		$req_insert="insert into ".$this->table_prefix."_fields_global_index(id_authority,type,code_champ,code_ss_champ,ordre,value,lang,pond,authority_num) values ".implode(',',$tab_field_insert);
		pmb_mysql_query($req_insert,$dbh);		
	}
	
	protected function delete_index($object_id,$datatype="all"){
		global $dbh;
		
		if(!$this->type) return false;
				
		$authority = new authority(0, $object_id, $this->type);
		$id_authority = $authority->get_id();
		
		//qu'est-ce qu'on efface?
		if($datatype=='all') {
			$req_del="delete from ".$this->table_prefix."_words_global_index where id_authority = '".$id_authority."' ";
			pmb_mysql_query($req_del,$dbh);
			//la table pour les recherche exacte
			$req_del="delete from ".$this->table_prefix."_fields_global_index where id_authority = '".$id_authority."' ";
			pmb_mysql_query($req_del,$dbh);
		}else{
			foreach($this->datatypes as $xml_datatype=> $codes){
				if($xml_datatype == $datatype){
					foreach($codes as $code_champ){
						$req_del = "delete from ".$this->table_prefix."_words_global_index where id_authority = '".$id_authority."' and code_champ = '".$code_champ."'";
						pmb_mysql_query($req_del,$dbh);
						//la table pour les recherche exacte
						$req_del = "delete from ".$this->table_prefix."_fields_global_index where id_authority = '".$id_authority."' and code_champ = '".$code_champ."'";
						pmb_mysql_query($req_del,$dbh);
					}
				}
			}
		}
	}
	
	protected function get_tab_field_insert($object_id, $infos, $order_fields, $isbd, $lang = '') {
		$authority = new authority(0, $object_id, $this->type);
		return "(".$authority->get_id().", ".$this->type.", ".$infos["champ"].", ".$infos["ss_champ"].", ".$order_fields.", '".addslashes(trim($isbd))."', '".addslashes(trim($lang))."', ".$infos["pond"].", 0)";
	}
	
	protected function get_tab_insert($object_id, $infos, $num_word, $order_fields, $pos) {
		$authority = new authority(0, $object_id, $this->type);
		return "(".$authority->get_id().", ".$this->type.", ".$infos["champ"].", ".$infos["ss_champ"].", ".$num_word.", ".$infos["pond"].", ".$order_fields.", ".$pos.")";
	}
	
	public static function delete_all_index($object_id, $table_prefix, $reference_key, $type = ""){
		global $dbh;
		
		if(!$type) return false;
		
		$authority = new authority(0, $object_id, $type);
		$id_authority = $authority->get_id();
		$req_del="delete from ".$table_prefix."_words_global_index where ".$reference_key."='".$id_authority."'";
		pmb_mysql_query($req_del,$dbh);
		//la table pour les recherche exacte
		$req_del="delete from ".$table_prefix."_fields_global_index where ".$reference_key."='".$id_authority."'";
		pmb_mysql_query($req_del,$dbh);
	}
}
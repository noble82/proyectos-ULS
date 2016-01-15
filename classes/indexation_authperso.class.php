<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: indexation_authperso.class.php,v 1.1 2015-11-12 12:44:51 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/indexation_authority.class.php");

//classe de calcul d'indexation des autorités perso...
class indexation_authperso extends indexation_authority {

	protected $id_authperso;
	
	public function __construct($xml_filepath, $table_prefix, $type, $id_authperso) {
		parent::__construct($xml_filepath, $table_prefix, $type);
		$this->id_authperso = $id_authperso+0;
		$this->transform_xml_indexation();
	}
	
	private function transform_xml_indexation(){

		if(is_array($this->xml_indexation) && count($this->xml_indexation)){
			foreach ($this->xml_indexation['FIELD'] as $i=>$field){
				$this->xml_indexation['FIELD'][$i]['ID'] = str_replace('!!id_authperso!!', $this->id_authperso, $field['ID']);
				if(is_array($field['TABLE'])){
					foreach ($field['TABLE'] as $j=>$table){
						if(is_array($table['LINK'])){
							foreach ($table['LINK'] as $k=>$link){
								$this->xml_indexation['FIELD'][$i]['TABLE'][$j]['LINK'][$k]['REFERENCEFIELD'][0]['value'] = str_replace('!!id_authperso!!', $this->id_authperso, $link['REFERENCEFIELD'][0]['value']);
								$this->xml_indexation['FIELD'][$i]['TABLE'][$j]['LINK'][$k]['EXTERNALFIELD'][0]['value'] = str_replace('!!id_authperso!!', $this->id_authperso, $link['EXTERNALFIELD'][0]['value']);
							}
						}
					}	
				}
			}
		}
	}
}
<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_authpersos.class.php,v 1.2 2015-12-08 10:32:01 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/vedette/vedette_element.class.php");
require_once($class_path."/authperso.class.php");

class vedette_authpersos extends vedette_element{
	public $params = array();
	
	public function __construct($params,$type, $id, $isbd = ""){
		$auth = new authperso(0,$id);
		$params['id_authority']=$auth->id;
		$params['label']=$auth->info['name'];
		$this->params = $params;
		parent::__construct($type, $id, $isbd);
	}

	public function set_vedette_element_from_database(){
		$auth = new authperso(0,$this->id);
 		$this->isbd = $auth->get_isbd($this->id);
	}
}

<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_indexint.class.php,v 1.2 2015-11-19 15:59:08 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/vedette/vedette_element.class.php");
require_once($class_path."/indexint.class.php");

class vedette_indexint extends vedette_element{
	
	public function set_vedette_element_from_database(){
		$indexint = new indexint($this->id);
		$this->isbd = "";
		if ($indexint->name_pclass) {
			$this->isbd .= "[".$indexint->name_pclass."] ";
		}
		
		$this->isbd .= $indexint->name;
		
		if ($indexint->comment) {
			$this->isbd .= " - ".$indexint->comment;
		}
	}
	public function get_link_see(){
		return str_replace("!!type!!", "indexint",$this->get_generic_link());
	}
}

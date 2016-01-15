<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: vedette_authors.class.php,v 1.4 2015-11-19 15:59:08 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/vedette/vedette_element.class.php");
require_once($class_path."/author.class.php");

class vedette_authors extends vedette_element{
	
	public function set_vedette_element_from_database(){
		$author = new auteur($this->id);
		$this->isbd = $author->isbd_entry;
	}
	
	public function get_link_see(){
		return str_replace("!!type!!", "author",$this->get_generic_link());
	}
}

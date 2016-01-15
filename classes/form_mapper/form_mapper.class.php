<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: form_mapper.class.php,v 1.2 2015-12-24 13:36:11 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


class form_mapper {
  
	public function __construct(){

	}	
	
	/**
	 * Fonction retournant l'instance de form_mapper associé au type passé en paramètre
	 * @param String $source
	 * @return form_mapper|boolean
	 */
	public static function getMapper($source){
		global $pmb_authority_mapping_folder, $class_path;
		if($pmb_authority_mapping_folder){
			if(is_dir($class_path.'/form_mapper/'.$pmb_authority_mapping_folder)){
				if(file_exists($class_path.'/form_mapper/'.$pmb_authority_mapping_folder.'/'.$source.'_form_mapper.class.php')){
					require_once($class_path.'/form_mapper/'.$pmb_authority_mapping_folder.'/'.$source.'_form_mapper.class.php');
					$class = $source.'_form_mapper';
					return new $class();
				}
			}
		}
		return false;
	}
	
	/**
	 * Fonction redérivée dans les classes enfants
	 */
	public function getMapping($dest){
		//fonction dérivée dans les classes enfants
	}
	
	/**
	 * Fonction redérivée dans les classes enfants
	 */
	public function getDestinations(){
		//fonction dérivée dans les classes enfants
	}
	
	public static function isMapped($dest){
		global $pmb_authority_mapping_folder, $class_path;
		if($pmb_authority_mapping_folder){
			$directory = $class_path.'/form_mapper/'.$pmb_authority_mapping_folder; 
			if(is_dir($directory)){
				$destinations = array();
				$handle = opendir($directory);
				while(false !== ($filename = readdir($handle))){
					$fullPath = $directory.'/'.$filename;
					if(is_file($fullPath)){
						require_once($fullPath);
						$class = str_replace('.class.php', '', $filename);
						$mapper = new $class();
						$destinations = array_merge($destinations,$mapper->getDestinations());
					}
				}
				if(in_array($dest, $destinations)){
					return true;
				}
			}
		}
		return false;
	}
}



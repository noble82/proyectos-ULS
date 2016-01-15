<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authority.class.php,v 1.15 2016-01-05 17:26:53 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/h2o/pmb_h2o.inc.php");
require_once($class_path.'/skos/skos_concepts_list.class.php');
require_once($class_path.'/skos/skos_view_concepts.class.php');
require_once($class_path.'/aut_link.class.php');
require_once($class_path.'/elements_list/elements_records_list_ui.class.php');
require_once($class_path.'/elements_list/elements_authorities_list_ui.class.php');
require_once($class_path.'/form_mapper/form_mapper.class.php');

class authority {
	
    /**
     * Identifiant
     * @var int
     */
    private $id;
	
	/**
	 * Type de l'autorité
	 * @var int
	 */
	private $type_object;
		
	private $autlink_class;
	
	/**
	 * Identifiant de l'autorité
	 * @var int
	 */
	private $num_object;
	
	/**
	 * 
	 * @var string
	 */
	private $string_type_object;
	
	/**
	 * Array d'onglet d'autorité
	 * @var authority_tabs
	 */
	private $authority_tabs;
	
	/**
	 * Libellé du type d'autorité
	 * @var string
	 */
	private $type_label;

	/**
	 * @var identifiant du statut
	 */
	private $num_statut = 1;
	
	/**
	 * @var class html du statut
	 */
	private $statut_class_html = 'statutnot1';
	
	/**
	 * 
	 * @var label du statut
	 */
	private $statut_label = '';
	
	/**
	 * Classe d'affichage de la liste d'éléments
	 * @var elements_list_ui
	 */
	private $authority_list_ui;
	
	/**
	 * Tableau des paramètres perso de l'autorité
	 * @var array
	 */
	private $p_perso;
	
	/**
	 *
	 * @var string
	 */
	private $audit_type;
	
	/**
	 * Tableau des identifiants de concepts composés utilisant cette autorité
	 * @var array
	 */
	private $concepts_ids;
	
	public function __construct($id=0, $num_object=0, $type_object=0){
	    $this->id = $id*1;
	    $this->num_object = $num_object*1;
	    $this->type_object = $type_object*1;
	    $this->get_datas();
	}
	
	public function get_datas() {
	    if(!$this->id) {
			$query = "select id_authority, num_statut, authorities_statut_label, authorities_statut_class_html from authorities join authorities_statuts on authorities_statuts.id_authorities_statut = authorities.num_statut where num_object=".$this->num_object." and type_object=".$this->type_object;
	        $result = pmb_mysql_query($query);
	        if($result && pmb_mysql_num_rows($result)) {
	        	$row = pmb_mysql_fetch_object($result);
	            $this->id = $row->id_authority;
				$this->num_statut = $row->num_statut;
				$this->statut_label = $row->authorities_statut_label;
				$this->statut_class_html = $row->authorities_statut_class_html;
	        } else {
	            $query = "insert into authorities(id_authority, num_object, type_object) values (0, ".$this->num_object.", ".$this->type_object.")";
	            pmb_mysql_query($query);
	            $this->id = pmb_mysql_insert_id();
				$this->num_statut = 1;
				$this->statut_label = '';
				$this->statut_class_html = 'statutnot1';
	        }
		} else {
			$query = "select num_object, type_object, num_statut, authorities_statut_label, authorities_statut_class_html from authorities join authorities_statuts on authorities_statuts.id_authorities_statut = authorities.num_statut where id_authority=".$this->id;
			$result = pmb_mysql_query($query);
			if($result && pmb_mysql_num_rows($result)) {
				$row = pmb_mysql_fetch_object($result);
				$this->num_object = $row->num_object;
				$this->type_object = $row->type_object;
				$this->num_statut = $row->num_statut;
				$this->statut_label = $row->authorities_statut_label;
				$this->statut_class_html = $row->authorities_statut_class_html;
			}
		}
    }
	
	public function get_id() {
	    return $this->id;
	}
	
	public function get_num_object() {
	    return $this->num_object;
	}
	
	public function get_num_statut() {
		return $this->num_statut;
	}
	
	public function get_statut_label() {
		return $this->statut_label;
	}
	
	public function get_statut_class_html() {
		return $this->statut_class_html;
	}
	
	public function set_num_statut($num_statut) {
		$num_statut += 0;
		if(!$num_statut){
			$num_statut = 1;
		}
		$this->num_statut = $num_statut; 
	}
	
	public function update() {
		if($this->num_object && $this->type_object) {
			$query = "update authorities set num_statut='".$this->num_statut."' where num_object=".$this->num_object." and type_object=".$this->type_object;
			$result = pmb_mysql_query($query);
			if($result) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function get_type_object() {
	    return $this->type_object;
	}
	
	public function get_string_type_object() {
		if (!$this->string_type_object) {
		    switch ($this->type_object) {
		    	case AUT_TABLE_AUTHORS :
		    	    $this->string_type_object = 'author';
		    	    break;
		    	case AUT_TABLE_CATEG :
		    	    $this->string_type_object = 'category';
		    	    break;
		    	case AUT_TABLE_PUBLISHERS :
		    	    $this->string_type_object = 'publisher';
		    	    break;
		    	case AUT_TABLE_COLLECTIONS :
		    	    $this->string_type_object = 'collection';
		    	    break;
		    	case AUT_TABLE_SUB_COLLECTIONS :
		    	    $this->string_type_object = 'subcollection';
		    	    break;
		    	case AUT_TABLE_SERIES :
		    	    $this->string_type_object = 'serie';
		    	    break;
		    	case AUT_TABLE_TITRES_UNIFORMES :
		    	    $this->string_type_object = 'titre_uniforme';
		    	    break;
		    	case AUT_TABLE_INDEXINT :
		    	    $this->string_type_object = 'indexint';
		    	    break;
		    	case AUT_TABLE_CONCEPT :
		    	    $this->string_type_object = 'concept';
		    	    break;
		    	case AUT_TABLE_AUTHPERSO :
		    	    $this->string_type_object = 'authperso';
		    	    break;
		    }
		}
	    return $this->string_type_object;
	}
	
	public function delete() {
	    $query = "delete from authorities where num_object=".$this->num_object." and type_object=".$this->type_object;
	    $result = pmb_mysql_query($query);
	    if($result) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	public function get_object_instance($params = array()) {
	    return authorities_collection::get_authority($this->type_object, $this->num_object, $params);
	}

	public function lookup($name,$context) {
		$value = null;
		if(strpos($name,":authority.")!==false){
			$property = str_replace(":authority.","",$name);
			$value = $this->generic_lookup($this, $property);
			if(!$value){
				$value = $this->generic_lookup($this->get_object_instance(), $property);
			}
		} else if (strpos($name,":aut_link.")!==false){
			$this->init_autlink_class();
			$property = str_replace(":aut_link.","",$name);
			$value = $this->generic_lookup($this->autlink_class, $property);
		} else {
			$attributes = explode('.', $name);
			// On regarde si on a directement une instance d'objet, dans le cas des boucles for
			if (is_object($obj = $context->getVariable(substr($attributes[0], 1))) && (count($attributes) > 1)) {
				$value = $obj;
				$property = str_replace($attributes[0].'.', '', $name);
				$value = $this->generic_lookup($value, $property);
			}
		}
		if(!$value){
			$value = null;
		}
		return $value;
	}
	
	private function generic_lookup($obj,$property){
		$attributes = explode(".",$property);
		for($i=0 ; $i<count($attributes) ; $i++){
			if(is_array($obj)){
				$obj = $obj[$attributes[$i]];
			} else if(is_object($obj)){
				$obj = $this->look_for_attribute_in_class($obj, $attributes[$i]);
			} else{
				$obj = null;
				break;
			}
		}
		return $obj;
	}
	
	private function look_for_attribute_in_class($class, $attribute, $parameters = array()) {
		if (is_object($class) && isset($class->{$attribute})) {
			return $class->{$attribute};
		} else if (method_exists($class, $attribute)) {
			return call_user_func_array(array($class, $attribute), $parameters);
		} else if (method_exists($class, "get_".$attribute)) {
			return call_user_func_array(array($class, "get_".$attribute), $parameters);
		} else if (method_exists($class, "is_".$attribute)) {
			return call_user_func_array(array($class, "is_".$attribute), $parameters);
		}
		return null;
	}
	
	public function render($context=array()){
		$template_path =  "./includes/templates/authorities/".$this->get_string_type_object().".html";
		if(file_exists("./includes/templates/authorities/".$this->get_string_type_object()."_subst.html")){
			$template_path =  "./includes/templates/authorities/".$this->get_string_type_object()."_subst.html";
		}
		if(file_exists($template_path)){
			$h2o = new H2o($template_path);
			$h2o->addLookup(array($this,"lookup"));
			echo $h2o->render($context);
		}
	}
	
	/**
	 * Retourn la classe d'affichage des éléments des onglets
	 * @return elements_list_ui
	 */
	public function get_authority_list_ui(){
		global $quoi;

		if(!$this->authority_list_ui){
			$tab = null;

			foreach($this->authority_tabs->get_tabs() as $current_tab){
				if (!$tab && $current_tab->get_nb_results()) {
					$tab = $current_tab;
				}
				if(($current_tab->get_name() == $quoi) && $current_tab->get_nb_results()){
					$tab = $current_tab;
					break;
				}
			}
			if ($tab) {
				$quoi = $tab->get_name();
				switch($tab->get_content_type()){
					case 'records':
						$this->authority_list_ui = new elements_records_list_ui($tab->get_contents(), $tab->get_nb_results(), $tab->is_mixed(), $tab->get_groups(), $tab->get_nb_filtered_results());
						break;
					case 'authorities':
						$this->authority_list_ui = new elements_authorities_list_ui($tab->get_contents(), $tab->get_nb_results(), $tab->is_mixed(), $tab->get_groups(), $tab->get_nb_filtered_results());
						break;
				}
			}
		}
		return $this->authority_list_ui;
	}

	private function init_autlink_class(){
		if(!$this->autlink_class){
			$this->autlink_class = new aut_link($this->type_object, $this->num_object);
		}
	}
	
	public function get_indexing_concepts(){
 		$concepts_list = new skos_concepts_list();
 		switch($this->type_object){
 			case AUT_TABLE_AUTHORS :
 				if ($concepts_list->set_concepts_from_object(TYPE_AUTHOR, $this->num_object)) {
 					return $concepts_list->get_concepts();
 				}
 				break;
			case AUT_TABLE_PUBLISHERS :
				if ($concepts_list->set_concepts_from_object(TYPE_PUBLISHER, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_COLLECTIONS :
				if ($concepts_list->set_concepts_from_object(TYPE_COLLECTION, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_SUB_COLLECTIONS :
				if ($concepts_list->set_concepts_from_object(TYPE_SUBCOLLECTION, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_SERIES :
				if ($concepts_list->set_concepts_from_object(TYPE_SERIE, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_INDEXINT :
				if ($concepts_list->set_concepts_from_object(TYPE_INDEXINT, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_TITRES_UNIFORMES :
				if ($concepts_list->set_concepts_from_object(TYPE_TITRE_UNIFORME, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_CATEG :
				if ($concepts_list->set_concepts_from_object(TYPE_CATEGORY, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
			case AUT_TABLE_AUTHPERSO :
				if ($concepts_list->set_concepts_from_object(TYPE_AUTHPERSO, $this->num_object)) {
					return $concepts_list->get_concepts();
				}
				break;
 		}
		return null;
	}
	
	public function set_authority_tabs($authority_tabs) {
		$this->authority_tabs = $authority_tabs;
	}
	
	public function get_authority_tabs() {
		return $this->authority_tabs;
	}
	
	public function get_type_label(){
		global $msg;
		
		if (!$this->type_label) {
			if ($this->get_type_object() != AUT_TABLE_AUTHPERSO) {
				$this->type_label = self::get_type_label_from_type_id($this->get_type_object());
			} else {
				$auth_datas = $this->get_object_instance()->get_data();
				$this->type_label = $auth_datas['name'];
			}
		}
		return $this->type_label;
	}
	
	public static function get_type_label_from_type_id($type_id) {
		global $msg;
		switch($type_id){
			case AUT_TABLE_AUTHORS :
				return $msg['isbd_author'];
			case AUT_TABLE_PUBLISHERS :
				return $msg['isbd_editeur'];
			case AUT_TABLE_COLLECTIONS :
				return $msg['isbd_collection'];
			case AUT_TABLE_SUB_COLLECTIONS :
				return $msg['isbd_subcollection'];
			case AUT_TABLE_SERIES :
				return $msg['isbd_serie'];
			case AUT_TABLE_INDEXINT :
				return $msg['isbd_indexint'];
			case AUT_TABLE_TITRES_UNIFORMES :
				return $msg['isbd_titre_uniforme'];
			case AUT_TABLE_CATEG :
				return $msg['isbd_categories'];
			case AUT_TABLE_CONCEPT :
				return $msg['concept_menu'];
		}
	}
	
	/**
	 * Retourne les paramètres persos
	 * @return array
	 */
	public function get_p_perso() {
		if (!$this->p_perso) {
			$this->p_perso = array();
			if($this->get_type_object() != AUT_TABLE_CONCEPT){
				$parametres_perso = new parametres_perso($this->get_prefix_for_pperso());
				$ppersos = $parametres_perso->show_fields($this->num_object);
				if(is_array($ppersos['FIELDS'])){
					foreach ($ppersos['FIELDS'] as $pperso) {
						$this->p_perso[] = $pperso;
					}
				}
			}
		}
		return $this->p_perso;
	}
	
	public function get_prefix_for_pperso(){
		switch($this->get_type_object()){
			case AUT_TABLE_CATEG:
				return 'categ';
			case AUT_TABLE_TITRES_UNIFORMES:
				return 'tu';
			default :
				return $this->get_string_type_object();
		}
	}
	
	public function get_audit_type() {
		if (!$this->audit_type) {
			switch ($this->type_object) {
				case AUT_TABLE_AUTHORS :
					$this->audit_type = AUDIT_AUTHOR;
					break;
				case AUT_TABLE_CATEG :
					$this->audit_type = AUDIT_CATEG;
					break;
				case AUT_TABLE_PUBLISHERS :
					$this->audit_type = AUDIT_PUBLISHER;
					break;
				case AUT_TABLE_COLLECTIONS :
					$this->audit_type = AUDIT_COLLECTION;
					break;
				case AUT_TABLE_SUB_COLLECTIONS :
					$this->audit_type = AUDIT_SUB_COLLECTION;
					break;
				case AUT_TABLE_SERIES :
					$this->audit_type = AUDIT_SERIE;
					break;
				case AUT_TABLE_TITRES_UNIFORMES :
					$this->audit_type = AUDIT_TITRE_UNIFORME;
					break;
				case AUT_TABLE_INDEXINT :
					$this->audit_type = AUDIT_INDEXINT;
					break;
				case AUT_TABLE_CONCEPT :
					$this->audit_type = AUDIT_CONCEPT;
					break;
				case AUT_TABLE_AUTHPERSO :
// 					$this->audit_type = (1000 + );
					break;
			}
		}
		return $this->audit_type;
	}
	
	public function get_special() {
		global $include_path;
	
		$special_file = $include_path.'/templates/authorities/special/authority_special.class.php';
		if (file_exists($special_file)) {
			require_once($special_file);
			return new authority_special($this);
		}
		return null;
	}
	
	public function get_map_profiles(){
		$returnedDatas = array();
		switch($this->type_object){
			case AUT_TABLE_AUTHORS :
				
				break;
			case AUT_TABLE_CATEG :
				
				break;
			case AUT_TABLE_PUBLISHERS :
				
				break;
			case AUT_TABLE_COLLECTIONS :
		
				break;
			case AUT_TABLE_SUB_COLLECTIONS :
		
				break;
			case AUT_TABLE_SERIES :
	
				break;
			case AUT_TABLE_TITRES_UNIFORMES :
				$mapper = form_mapper::getMapper('tu');
				break;
			case AUT_TABLE_INDEXINT :
	
				break;
			case AUT_TABLE_CONCEPT :
	
				break;
			case AUT_TABLE_AUTHPERSO :

				break;
		}
		
		if($mapper){
			$mapper->setId($this->num_object);
			$destinations = $mapper->getDestinations();
			foreach($destinations as $dest){
				$returnedDatas[] = $mapper->getProfiles($dest);
			}
		}
		return $returnedDatas;
	}

	/**
	 * Renvoie le tableau des identifiants de concepts composés utilisant cette autorité
	 * @return array
	 */
	public function get_concepts_ids() {
		if (!isset($this->concepts_ids)) {
			$this->concepts_ids = array();
			$vedette_composee_found = vedette_composee::get_vedettes_built_with_element($this->get_num_object(), $this->get_string_type_object());
			foreach($vedette_composee_found as $vedette_id){
				$this->concepts_ids[] = vedette_composee::get_object_id_from_vedette_id($vedette_id, TYPE_CONCEPT_PREFLABEL);
			}
		}
		return $this->concepts_ids;
	}
	
}
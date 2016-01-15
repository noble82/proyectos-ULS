<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: elements_authorities_list_ui.class.php,v 1.3 2016-01-04 10:39:02 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/elements_list/elements_list_ui.class.php');
require_once($class_path.'/authority.class.php');
require_once($include_path.'/h2o/pmb_h2o.inc.php');

/**
 * Classe d'affichage d'un onglet qui affiche une liste d'autorité
 * @author vtouchard
 *
 */
class elements_authorities_list_ui extends elements_list_ui {
	
	public function get_elements_list(){
		if (!$this->elements_list) {
			$this->elements_list = array();
			$recherche_ajax_mode=0;
			$nb=0;
			$this->elements_list = $this->generate_elements_list($this->contents,0);
		}
		return $this->elements_list;
	}
	
	private function generate_elements_list($contents, $group_id){
		$elements_list = '';
		$recherche_ajax_mode = 0;
		$nb = 0;
		foreach($contents as $authority_id){
			if(!$recherche_ajax_mode && ($nb++>5)){
				$recherche_ajax_mode = 1;
			}
			$authority = new authority($authority_id);
			$elements_list.= $this->generate_authority($authority, $recherche_ajax_mode, $group_id);
		}
		return $elements_list;
	}
	
	/**
	 * Permet de générer l'affichage d'un élément de liste de type autorité
	 * @param authority $authority
	 * @param bool $recherche_ajax_mode
	 * @param int $group_id Identifiant du groupe
	 * @return string 
	 */
	private function generate_authority($authority, $recherche_ajax_mode, $group_id){
		global $include_path;
		$template_path = $include_path.'/templates/authorities/list/'.$authority->get_string_type_object().'.html';
		if(file_exists($include_path.'/templates/authorities/list/'.$authority->get_string_type_object().'_subst.html')){
			$template_path = $include_path.'/templates/authorities/list/'.$authority->get_string_type_object().'_subst.html';
		}
		if(file_exists($template_path)){
			$h2o = new H2o($template_path);
			$context = array('list_element' => $authority, 'group_id'=>$group_id);
			return $h2o->render($context);
		}
		return '';
	}

}
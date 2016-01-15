<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authority_tabs_author.class.php,v 1.7 2016-01-04 10:39:01 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/authorities/tabs/authority_tabs.class.php');

class authority_tabs_author extends authority_tabs {
	
	/**
	 * Constructeur
	 * @param authority $authority
	 */
	public function __construct($authority){
		parent::__construct($authority);
	}
	
	public static function get_author_records_per_role($tab, $filter, $element_id) {
		global $dbh, $msg;
	
		//Récupération du nombre de notice liées
		$groups = array();
		$query = "SELECT count(responsability_notice) as nb, vedette_object.object_id from responsability join vedette_link on vedette_link.num_object=responsability.id_responsability and vedette_link.type_object in (".TYPE_NOTICE_RESPONSABILITY_PRINCIPAL.",".TYPE_NOTICE_RESPONSABILITY_SECONDAIRE.",".TYPE_NOTICE_RESPONSABILITY_AUTRE.") join vedette_object on vedette_object.num_vedette=vedette_link.num_vedette where responsability.responsability_author = ".$element_id." and vedette_object.object_type > 1000 GROUP BY vedette_object.object_id";
		$result = pmb_mysql_query($query, $dbh);	
		if ($result && pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_object($result)) {
				if(!isset($groups[$row->object_id])){
					$authperso = new authority(0,$row->object_id, AUT_TABLE_AUTHPERSO);
					$groups[$row->object_id] = array(
							'label' => $authperso->get_object_instance()->get_header(),
							'nb_results' => $row->nb
					);
				}
			}
		}
		$elements_ids = array();
		if (count($groups)) {
			// On trie le tableau
			uasort($groups, array('authority_tabs', '_sort_groups_by_label'));
			$tab->add_groups($filter['name'], array(
					'label' => $filter['label'],
					'elements' => $groups
			));
			
			$filter_values = $tab->get_filter_values($filter['name']);
	
			//Si on a des résultats; on passe à la suite
			if($filter_values && count($filter_values)){
				$query = "select responsability_notice from responsability join vedette_link on vedette_link.num_object=responsability.id_responsability and vedette_link.type_object in (".TYPE_NOTICE_RESPONSABILITY_PRINCIPAL.",".TYPE_NOTICE_RESPONSABILITY_SECONDAIRE.",".TYPE_NOTICE_RESPONSABILITY_AUTRE.") join vedette_object on vedette_object.num_vedette=vedette_link.num_vedette where responsability.responsability_author = ".$element_id." and vedette_object.object_type > 1000";
				$query.= ' and vedette_object.object_id in ("'.implode('","', $filter_values).'")';
				$query.= ' group by responsability.responsability_notice';
				$result = pmb_mysql_query($query,$dbh);
				if(pmb_mysql_num_rows($result)){
					while($row = pmb_mysql_fetch_object($result)){
						$elements_ids[] = $row->responsability_notice;
					}
				}
			}
		}
		return $elements_ids;
	}
	
	public static function get_author_tu_per_role($tab, $filter, $element_id) {
		global $dbh, $msg;
	
		//Récupération du nombre de notice liées
		$groups = array();
		$query = "SELECT count(responsability_tu_num) as nb, vedette_object.object_id from responsability_tu join vedette_link on vedette_link.num_object=responsability_tu.id_responsability_tu and vedette_link.type_object= ".TYPE_TU_RESPONSABILITY." join vedette_object on vedette_object.num_vedette=vedette_link.num_vedette where responsability_tu.responsability_tu_author_num = ".$element_id." and vedette_object.object_type > 1000 GROUP BY vedette_object.object_id";
		$result = pmb_mysql_query($query, $dbh);	
		if ($result && pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_object($result)) {
				if(!isset($groups[$row->object_id])){
					$authperso = new authority(0,$row->object_id, AUT_TABLE_AUTHPERSO);
					$groups[$row->object_id] = array(
							'label' => $authperso->get_object_instance()->get_header(),
							'nb_results' => $row->nb
					);
				}
				$nb_results+= $row->nb;
			}
		}
		$elements_ids = array();
		if (count($groups)) {
			// On trie le tableau
			uasort($groups, array('authority_tabs', '_sort_groups_by_label'));
			$tab->add_groups($filter['name'], array(
					'label' => $filter['label'],
					'elements' => $groups
			));

			$filter_values = $tab->get_filter_values($filter['name']);
	
			//Si on a des résultats; on passe à la suite
			if($filter_values && count($filter_values)){
				$query = "select responsability_tu_num, vedette_object.object_id from responsability_tu join vedette_link on vedette_link.num_object=responsability_tu.id_responsability_tu and vedette_link.type_object= ".TYPE_TU_RESPONSABILITY." join vedette_object on vedette_object.num_vedette=vedette_link.num_vedette join titres_uniformes on titres_uniformes.tu_id=responsability_tu.responsability_tu_num where responsability_tu.responsability_tu_author_num = ".$element_id." and vedette_object.object_type > 1000";
				$query.= ' and vedette_object.object_id in ("'.implode('","', $filter_values).'")';
				$query.= ' group by responsability_tu.responsability_tu_num';
				$result = pmb_mysql_query($query,$dbh);
				if(pmb_mysql_num_rows($result)){
					while($row = pmb_mysql_fetch_object($result)){
						$elements_ids[] = $row->responsability_tu_num;
					}
				}
			}
		}
		return $elements_ids;
		
	}
}
<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authority_tabs.class.php,v 1.5 2016-01-05 17:26:53 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/elements_list_tab.class.php');
require_once($class_path.'/skos/skos_concept.class.php');
require_once($class_path.'/indexation.class.php');
require_once($class_path.'/authorities/tabs/authority_tabs_parser.class.php');

class authority_tabs {
	
	/**
	 * Instance de la classe authority associée
	 * @var authority
	 */
	protected $authority;
	
	/**
	 * Tableau des onglets de l'autorité
	 * @var elements_list_tab Tableau des onglets
	 */
	protected $tabs;
	
	/**
	 * Constructeur
	 * @param authority $authority Instance de la classe authority associée
	 */
	public function __construct($authority){
		$this->authority = $authority;
		$parser = new authority_tabs_parser();
		$this->tabs = $parser->get_tabs_for($authority->get_string_type_object());
		$this->init_tabs_contents();
	}
	
	/**
	 * Retourne le tableau des onglets
	 * @return authority_tab Tableau d'authority_tab
	 */
	public function get_tabs(){
		return $this->tabs;
	}
	
	/**
	 * Ajoute un onglet au tableau
	 * @param authority_tab $tab Onglet à ajouter
	 */
	protected function add_tab($tab) {
		if ($tab) {
			$this->tabs[] = $tab;
		}
	}
	
	/**
	 * Retourne la portion de requête pour la limite des résultats
	 * @return string Portion de la requête
	 */
	protected static function get_limit(){
		global $pmb_nb_elems_per_tab;
		global $tab_page;
		global $tab_nb_per_page;
		
		if (!$tab_nb_per_page) {
			$tab_nb_per_page = $pmb_nb_elems_per_tab;
		}
		if($tab_page){
			return ' limit '.(($tab_page-1) * ($tab_nb_per_page*1)).', '.($tab_nb_per_page*1).' ';
		}
		return ' limit '.($tab_nb_per_page*1).' ';
	}
	
	public static function _sort_groups_by_label($a, $b) {
		if (strtolower($a['label']) == strtolower($b['label'])) {
			return 0;
		}
		return (strtolower($a['label']) < strtolower($b['label'])) ? -1 : 1;
	}
	
	protected function init_tabs_contents() {
		global $dbh, $quoi;
		
		foreach ($this->tabs as $tab) {
			$callable = $tab->get_callable();
			if (count($callable)) {
				call_user_func_array(array($callable['class'], $callable['method']), array($tab, $this->authority));
			} else {
				$query_elements = $tab->get_query_elements();
				$nb_result = 0;

				if (($query_elements['getconcepts'] != 'true') || (($query_elements['getconcepts'] == 'true') && count($this->authority->get_concepts_ids()))) {
					// Si on a besoin des concepts composés, et qu'aucun n'est trouvé, ça ne sert à rien d'aller plus loin
					$query_clauses = $this->get_query_clauses($query_elements);
					$query = 'select count(distinct '.$query_elements['table'].'.'.$query_elements['select'].')';
					$query.= $query_clauses['from'];
					$query.= $query_clauses['where'];
					$nb_result = pmb_mysql_result(pmb_mysql_query($query, $dbh), 0, 0);
				}
				
				$tab->set_nb_results($nb_result);
				if (!$quoi && $nb_result) {
					// Si $quoi n'est pas valorisé et qu'on a des résultats, on valorise $quoi avec cet onglet
					$quoi = $tab->get_name();
				}
				$elements_ids = array();
				if ($nb_result && ($quoi == $tab->get_name())) {
					$filtered_elements = $this->get_tab_filters($tab);
					
					if (!$tab->has_filters_values() || ($tab->has_filters_values() && count($filtered_elements))) {
						// On n'a pas de filtre coché ou on a au moins un résultat après filtrage, sinon on ne fait rien
						$tab->set_nb_filtered_results(count($filtered_elements));
						$query = 'select '.$query_elements['table'].'.'.$query_elements['select'].' as element_id';
						$query.= $query_clauses['from'];
						$query.= $query_clauses['where'];
						if (count($filtered_elements)) {
							$query.= ' and '.$query_elements['table'].'.'.$query_elements['select'].' in ('.implode(',', $filtered_elements).')';
						}
						$query.= $query_clauses['order'];
						$query.= self::get_limit();
						$result = pmb_mysql_query($query, $dbh);
						if($result && pmb_mysql_num_rows($result)){
							while($row = pmb_mysql_fetch_object($result)){
								if ($tab->get_content_type() == 'authorities') {
									$authority =  new authority(0, $row->element_id, $tab->get_content_authority_type());
									$elements_ids[] = $authority->get_id();
								} else {
									$elements_ids[] = $row->element_id;
								}
							}
						}
					}
				}
				$tab->set_contents($elements_ids);
			}
		}
	}
	
	/**
	 * 
	 * @param elements_list_tab $tab
	 * @return multitype:NULL
	 */
	protected function get_tab_filters($tab) {
		global $dbh, $msg;

		$query_elements = $tab->get_query_elements();
		$query_clauses = $this->get_query_clauses($query_elements);
		$filters = $tab->get_filters();
		$elements_ids = array();
		
		foreach ($filters as $filter) {
			$result_ids = array();
			
			if ($filter['type'] == 'callable') {
				// Si c'est un filtre de type callable, on appelle la méthode
				$result_ids = call_user_func_array(array($filter['class'], $filter['method']), array($tab, $filter, $this->authority->get_num_object()));
			} else {
				// Sinon on construit les requêtes qui vont bien
				if ($filter['type'] == 'marc_list') {
					$marc_list = new marc_list($filter['marcname']);
				}
				$groups = array();
				
				$query = 'select count('.$query_elements['table'].'.'.$query_elements['select'].') as nb, '.$query_elements['table'].'.'.$filter['field'].' as group_id';
				$query.= $query_clauses['from'];
				$query.= $query_clauses['where'];
				$query.= ' group by '.$query_elements['table'].'.'.$filter['field'];
				$result = pmb_mysql_query($query, $dbh);
				if (pmb_mysql_num_rows($result)) {
					while ($row = pmb_mysql_fetch_object($result)) {
						if(!isset($groups[$row->group_id])){
							$label = '';
							if ($filter['type'] == 'marc_list') {
								if ($filter['marcname'] != 'oeuvre_link') {
									$label = $marc_list->table[$row->group_id];
								} else {
									// Dans le cas d'oeuvre_link.xml on a un étage de plus...
									foreach ($marc_list->table as $link_type) {
										if (isset($link_type[$row->group_id])) {
											$label = $link_type[$row->group_id];
											break;
										}
									}
								}
							}
							$groups[$row->group_id] = array(
									'label' => $label,
									'nb_results' => $row->nb
							);
						}
					}
				}
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
						$query = 'select '.$query_elements['table'].'.'.$query_elements['select'].' as element_id';
						$query.= $query_clauses['from'];
						$query.= $query_clauses['where'];
						$query.= ' and '.$query_elements['table'].'.'.$filter['field'].' in ("'.implode('","', $filter_values).'")';
						$result = pmb_mysql_query($query,$dbh);
						if(pmb_mysql_num_rows($result)){
							while($row = pmb_mysql_fetch_object($result)){
								$result_ids[] = $row->element_id;
							}
						}
					}
				}
			}
			if (count($elements_ids) && count($result_ids)) {
				$elements_ids = array_intersect($elements_ids, $result_ids);
			} else if (count($result_ids)) {
				$elements_ids = $result_ids;
			}
		}
		return $elements_ids;
	}
	
	protected function get_query_clauses($query_elements) {
		$query_clauses = array();
		$tables = array();
		
		// Clause from
		$query_clauses['from'] = ' from '.$query_elements['table'];
		$tables[] = $query_elements['table'];
		foreach ($query_elements['join'] as $join) {
			$query_clauses['from'].= ' join '.$join['table'];
			$tables[] = $join['table'];
			$query_clauses['from'].= ' on '.$query_elements['table'].'.'.$join['referencefield'].' = '.$join['table'].'.'.$join['externalfield'];
			if ($join['condition']) {
				$query_clauses['from'].= ' and '.$join['condition'];
			}
		}
		
		// Clause where
		$query_clauses['where'] = '';
		foreach ($query_elements['elementfield'] as $elementfield) {
			if (!$query_clauses['where']) {
				$query_clauses['where'].= ' where (';
			} else {
				$query_clauses['where'].= ' or';
			}
			$query_clauses['where'].= ' '.$query_elements['table'].'.'.$elementfield.' = '.$this->authority->get_num_object();
		}
		if ($query_clauses['where']) $query_clauses['where'].= ')';
		foreach ($query_elements['condition'] as $condition) {
			if (!$query_clauses['where']) {
				$query_clauses['where'].= ' where';
			} else {
				$query_clauses['where'].= ' and';
			}
			$query_clauses['where'].= ' '.$condition;
		}
		if ($query_elements['getconcepts'] == 'true') {
			$concepts_ids = $this->authority->get_concepts_ids();
			if (count($concepts_ids)) {
				if (!$query_clauses['where']) {
					$query_clauses['where'].= ' where';
				} else {
					$query_clauses['where'].= ' and';
				}
				$query_clauses['where'].= ' '.$query_elements['conceptfield'].' in ('.implode(',', $concepts_ids).')';
			}
		}
		
		// Clause order
		$query_clauses['order'] = '';
		if ($query_elements['order']) {
			// On commence par faire une jointure si nécessaire
			if ($query_elements['order']['table'] && !in_array($query_elements['order']['table'], $tables)) {
				$query_clauses['from'].= ' join '.$query_elements['order']['table'];
				$query_clauses['from'].= ' on '.$query_elements['table'].'.'.$query_elements['order']['referencefield'].' = '.$query_elements['order']['table'].'.'.$query_elements['order']['externalfield'];
			}
			$query_clauses['order'].= ' order by '.($query_elements['order']['table'] ? $query_elements['order']['table'] : $query_elements['table']).'.'.$query_elements['order']['field'];
		}
		return $query_clauses;
	}
	
	/**
	 * Méthode permettant de récupérer les autorités indexées avec un concept utilisant cette autorité
	 * @param elements_list_tab $tab
	 * @param authority_tabs $authority_tabs
	 * @return authority_tab Onglet
	 */
	protected static function get_tab_authorities_indexed_with_concept($tab, $authority){
		global $dbh, $msg;
		
		$types_needed = array(TYPE_AUTHOR, TYPE_CATEGORY, TYPE_PUBLISHER, TYPE_COLLECTION, TYPE_SUBCOLLECTION, TYPE_SERIE, TYPE_TITRE_UNIFORME, TYPE_INDEXINT, TYPE_AUTHPERSO);
		
		$concepts_ids = $authority->get_concepts_ids();
		$nb_result = 0;
		
		if (count($concepts_ids)) {
			$query = 'select count(distinct num_object, type_object) from index_concept where num_concept in ('.implode(',', $concepts_ids).') and type_object in ('.implode(',', $types_needed).')';
			$nb_result = pmb_mysql_result(pmb_mysql_query($query, $dbh), 0, 0);
		}
		
		$tab->set_nb_results($nb_result);
		if (!$quoi && $nb_result) {
			// Si $quoi n'est pas valorisé et qu'on a des résultats, on valorise $quoi avec cet onglet
			$quoi = $tab->get_name();
		}
		if ($nb_result && ($quoi == $tab->get_name())) {
			// On définit les filtres
			$filter = array(
					'name' => 'common_indexed_authorities_by_types',
					'label' => $msg['authority_tabs_common_indexed_authorities_by_types']
			);
			$tab->set_filters(array($filter));
			$groups = array();
			$query = 'select count(distinct num_object) as nb, type_object, id_authperso, authperso_name from index_concept left join authperso_authorities on num_object = id_authperso_authority and type_object = '.TYPE_AUTHPERSO.' left join authperso on id_authperso = authperso_authority_authperso_num where num_concept in ('.implode(',', $concepts_ids).') and type_object in ('.implode(',', $types_needed).') group by type_object, id_authperso';
			$result = pmb_mysql_query($query, $dbh);
			if ($result && pmb_mysql_num_rows($result)) {
				while ($row = pmb_mysql_fetch_object($result)) {
					if (($row->type_object == TYPE_AUTHPERSO) && !isset($groups[1000 + $row->id_authperso])) {
						$groups[1000 + $row->id_authperso] = array(
								'label' => $row->authperso_name,
								'nb_results' => $row->nb
						);
					} else if (!isset($groups[$row->type_object])){
						$groups[$row->type_object] = array(
								'label' => authority::get_type_label_from_type_id(index_concept::get_aut_table_type_from_type($row->type_object)),
								'nb_results' => $row->nb
						);
					}
				}
			}
			if (count($groups)) {
				// On trie le tableau
				uasort($groups, array('authority_tabs', '_sort_groups_by_label'));
				$tab->add_groups($filter['name'], array(
						'label' => $filter['label'],
						'elements' => $groups
				));
				
				$filter_values = $tab->get_filter_values($filter['name']);

				$authpersos_needed = array();
				if ($filter_values && count($filter_values)) {
					$types_needed = array();
					foreach ($filter_values as $value) {
						if ($value > 1000) {
							if (!in_array(TYPE_AUTHPERSO, $types_needed)) {
								$types_needed[] = TYPE_AUTHPERSO;
							}
							$authpersos_needed[] = $value - 1000;
						} else {
							$types_needed[] = $value;
						}
					}
					
				}
				
				$query = 'select SQL_CALC_FOUND_ROWS num_object, type_object, authperso_authority_authperso_num';
				$query.= ' from index_concept left join authperso_authorities on num_object = id_authperso_authority and type_object = '.TYPE_AUTHPERSO;
				$query.= ' where num_concept in ('.implode(',', $concepts_ids).') and type_object in ('.implode(',', $types_needed).')';
				// si on a des filtres sur des authorités persos
				if (count($authpersos_needed)) {
					$query.= ' and (authperso_authority_authperso_num is null or authperso_authority_authperso_num in ('.implode(',', $authpersos_needed).'))';
				}
				$query.= authority_tabs::get_limit();
				// on lance la requête
				$result = pmb_mysql_query($query, $dbh);
				$records_ids = array();
				if($result && pmb_mysql_num_rows($result)){
					while($row = pmb_mysql_fetch_object($result)){
						$authority = new authority(0, $row->num_object, index_concept::get_aut_table_type_from_type($row->type_object));
						$records_ids[] = $authority->get_id();
					}
				}
				$nb_filtered_results = pmb_mysql_result(pmb_mysql_query('select FOUND_ROWS()'), 0, 0);
				$tab->set_nb_filtered_results($nb_filtered_results);
				$tab->set_contents($records_ids);
			}
		}
	}
}
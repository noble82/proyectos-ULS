<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: authority_tabs_parser.class.php,v 1.3 2016-01-04 16:02:41 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class authority_tabs_parser {
	
	/**
	 * Fichier xml à utiliser
	 * @var string
	 */
	protected $xml_file;
	
	/**
	 * Chemin entier vers le fichier xml
	 * @var string
	 */
	protected $full_path;
	
	/**
	 * Tableau des onglets parsés
	 * @var elements_list_tab
	 */
	protected $tabs;
	
	/**
	 * Constructeur
	 * @param string $xml_file Fichier xml à utiliser
	 * @param string $full_path Chemin entier vers le fichier xml
	 */
	public function __construct($xml_file = '', $full_path = '') {
		$this->xml_file = $xml_file;
		$this->full_path = $full_path;
		$this->parse_file();
	}

	/**
	 * Parse le fichier xml
	 */
	private function parse_file() {
		global $base_path, $include_path;
		global $msg;
			
		if(!$this->xml_file) {
			$this->xml_file = "display_tabs";
		}
		if(!$this->full_path){
			$filepath = $include_path."/authorities/".$this->xml_file."_subst.xml";
			if (!file_exists($filepath)) {
				$filepath = $include_path."/authorities/".$this->xml_file.".xml";
			}
		} else {
			$filepath = $this->full_path.$this->xml_file."_subst.xml";
			if (!file_exists($filepath)) {
				$filepath = $this->full_path.$this->xml_file.".xml";
			}
		}
		$fileInfo = pathinfo($filepath);
		$tempFile = $base_path."/temp/XML".preg_replace("/[^a-z0-9]/i","",$fileInfo['dirname'].$fileInfo['filename'].$charset).".tmp";
		$dejaParse = false;
		if (file_exists($tempFile) ) {
			//Le fichier XML original a-t-il été modifié ultérieurement ?
			if (filemtime($filepath) > filemtime($tempFile)) {
				//on va re-générer le pseudo-cache
				unlink($tempFile);
			} else {
				$dejaParse = true;
			}
		}
		if ($dejaParse) {
			$tmp = fopen($tempFile, "r");
			$cache = unserialize(fread($tmp,filesize($tempFile)));
			fclose($tmp);
			if(count($cache) == 1){
				$this->tabs = $cache[0];
			}else{
				//SOUCIS de cache...
				unlink($tempFile);
				$this->parse_search_file();
			}
		} else {
			$fp=fopen($filepath,"r") or die("Can't find XML file");
			$size=filesize($filepath);
	
			$xml=fread($fp,$size);
			fclose($fp);
			$tabs=_parser_text_no_function_($xml, "PMBTABS");
				
			$this->tabs = array();
			//Lecture des onglets
			foreach ($tabs['TAB'] as $tab) {
				$current_tab = new elements_list_tab($tab['NAME'], get_msg_to_display($tab['LABEL']), $tab['CONTENTTYPE']);
				if ($tab['CONTENTTYPE'] == 'authorities') {
					// Si on a affaire à un onlget d'autorité, on regarde s'il est spécialisé dans un type d'autorité
					if ($tab['AUTHORITYTYPE'][0]['value'] && defined($tab['AUTHORITYTYPE'][0]['value'])) {
						$current_tab->set_content_authority_type(constant($tab['AUTHORITYTYPE'][0]['value']));
					}
				}
				// on récupère les éléments de requête
				if ($tab['QUERY'][0]) {
					$query_elements = $this->parse_query_elements($tab['QUERY'][0]);
					$current_tab->set_query_elements($query_elements);
				}
				// on récupère le callable
				if ($tab['CALLABLE'][0]) {
					$callable = $this->parse_callable($tab['CALLABLE'][0]);
					$current_tab->set_callable($callable);
				}
				// on récupère les filtres
				if ($tab['FILTERS'][0]['FILTER']) {
					$filters = $this->parse_filters($tab['FILTERS'][0]['FILTER']);
					$current_tab->set_filters($filters);
				}
				$this->tabs[$tab['FOR']][] = $current_tab;
			}
			$tmp = fopen($tempFile, "wb");
			fwrite($tmp,serialize(array($this->tabs)));
			fclose($tmp);
		}
	}
	
	/**
	 * Retourne un tableau formaté contenant les élements de la requête
	 * @param array $query Structure parsée de la requête
	 * @return array Tableau formaté contenant les élements de la requête
	 */
	private function parse_query_elements($query) {
		$join = array();
		if ($query['JOIN']) {
			foreach ($query['JOIN'] as $field) {
				$join[] = array(
						'table' => $field['TABLE'][0]['value'],
						'referencefield' => $field['REFERENCEFIELD'][0]['value'],
						'externalfield' => $field['EXTERNALFIELD'][0]['value'],
						'condition' => $field['CONDITION'][0]['value'],
				);
			}
		}
		$elementfield = array();
		if ($query['ELEMENTFIELD']) {
			foreach ($query['ELEMENTFIELD'] as $field) {
				$elementfield[] = $field['value'];
			}
		}
		$condition = array();
		if ($query['CONDITION']) {
			foreach ($query['CONDITION'] as $field) {
				$condition[] = $field['value'];
			}
		}
		$order = array();
		if ($query['ORDER']) {
			$order = array(
					'table' => $query['ORDER'][0]['TABLE'][0]['value'],
					'field' => $query['ORDER'][0]['FIELD'][0]['value'],
					'referencefield' => $query['ORDER'][0]['REFERENCEFIELD'][0]['value'],
					'externalfield' => $query['ORDER'][0]['EXTERNALFIELD'][0]['value'],
			);
		}
		$query_elements = array(
				'getconcepts' => $query['GETCONCEPTS'],
				'select' => $query['SELECT'][0]['value'],
				'table' => $query['TABLE'][0]['value'],
				'join' => $join,
				'elementfield' => $elementfield,
				'conceptfield' => $query['CONCEPTFIELD'][0]['value'],
				'condition' => $condition,
				'order' => $order
		);
		return $query_elements;
	}
	
	/**
	 * Retourne un tableau formaté contenant les élements du callable
	 * @param array $parsed_callable Structure parsée du callable
	 * @return array Tableau formaté contenant les élements du callable
	 */
	private function parse_callable($parsed_callable) {
		$callable = array(
				'class' => $parsed_callable['CLASS'][0]['value'],
				'method' => $parsed_callable['METHOD'][0]['value']
		);
		return $callable;
	}
	
	/**
	 * Retourne un tableau formaté contenant les élements des filtres
	 * @param array $parsed_filters Structure parsée des filtres
	 * @return array Tableau formaté contenant les élements des filtres
	 */
	private function parse_filters($parsed_filters) {
		foreach ($parsed_filters as $filter) {
			$filters[] =  array(
					'name' => $filter['NAME'],
					'label' => get_msg_to_display($filter['LABEL']),
					'field' => $filter['FIELD'][0]['value'],
					'type' => $filter['TYPE'],
					'marcname' => $filter['MARCNAME'][0]['value'],
					'class' => $filter['CLASS'][0]['value'],
					'method' => $filter['METHOD'][0]['value']
			);
		}
		return $filters;
	}
	
	/**
	 * Retourne les onglets liés à un type d'autorité
	 * @param string $authority_type Type de l'autorité dont on veut les onglets
	 * @return array
	 */
	public function get_tabs_for($authority_type) {
		if (isset($this->tabs[$authority_type])) {
			return array_merge($this->tabs[$authority_type], $this->tabs['common']);
		}
		return $this->tabs['common'];
	}
}
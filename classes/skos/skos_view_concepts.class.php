<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: skos_view_concepts.class.php,v 1.1 2015-11-19 15:59:08 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/h2o/pmb_h2o.inc.php");
include_once($include_path."/templates/skos/skos_view_concepts.tpl.php");
require_once($class_path."/skos/skos_view_concept.class.php");
require_once($class_path."/skos/skos_onto.class.php");

/**
 * class skos_view_concepts
 * Vue de la liste des concepts qui indexent un �l�ment
*/
class skos_view_concepts {
	
	/**
	 * Retourne la liste � afficher
	 * @param array $datas Liste des concepts format�s
	 * @param string $template Nom du template � utiliser
	 * @param array $parameters Param�tres qui influent sur la liste
	 * @return string La liste � afficher
	 */
	static public function render($datas, $template, $parameters = array()) {
		global $$template;
		
		return H2o::parseString($$template)->render(array('concepts_list' => $datas, 'parameters' => $parameters));
	}
	
	/**
	 * Retourne l'affichage des concepts li�s � une notice
	 * 
	 * Recompose le tableau de param�tres via les globales et transmet le bon template au render
	 * @param skos_concepts_list $concepts_list Liste des concepts associ�s � la notice
	 * @return string
	 */
	static public function get_list_in_notice($concepts_list) {
		global $thesaurus_concepts_affichage_ordre, $thesaurus_concepts_concept_in_line;
	
		$concepts = $concepts_list->get_concepts();
		
		$datas = array(
				'title' => $msg['skos_view_concepts_concepts'],
				'elements' => self::get_sorted_concepts($concepts, true)
		);
		
		return self::render($datas, "skos_view_concepts_list_in_notice", array('concepts_in_line' => $thesaurus_concepts_concept_in_line*1));
	}

	/**
	 * Retourne l'affichage des concepts li�s � une autorit�
	 *
	 * Recompose le tableau de param�tres via les globales et transmet le bon template au render
	 * @param skos_concepts_list $concepts_list Liste des concepts associ�s � l'autorit�
	 * @return string
	 */
	static public function get_list_in_authority($concepts_list) {
		global $thesaurus_concepts_affichage_ordre, $thesaurus_concepts_concept_in_line;
		global $msg;
	
		$concepts = $concepts_list->get_concepts();
		
		$datas = array(
				'title' => $msg['skos_view_concepts_concepts'],
				'elements' => self::get_sorted_concepts($concepts, true)
		);

		return self::render($datas, "skos_view_concepts_list_in_authority", array('concepts_in_line' => $thesaurus_concepts_concept_in_line*1));
	}
	
	/**
	 * Retourne l'affichage des enfants d'un concept
	 * @param skos_concepts_list $concepts_list Liste des enfants
	 * @return string
	 */
	static public function get_narrowers_list($concepts_list) {
		global $thesaurus_concepts_affichage_ordre, $thesaurus_concepts_concept_in_line;
	
		$concepts = $concepts_list->get_concepts();
		
		$datas = array(
				'title' => skos_onto::get_property_label("http://www.w3.org/2004/02/skos/core#Concept", "http://www.w3.org/2004/02/skos/core#narrower"),
				'elements' => self::get_sorted_concepts($concepts, false)
		);
		
		return self::render($datas, "skos_view_concepts_narrowers_list", array('concepts_in_line' => $thesaurus_concepts_concept_in_line*1));
	}
	
	/**
	 * Retourne l'affichage des parents d'un concept
	 * @param skos_concepts_list $concepts_list Liste des parents
	 * @return string
	 */
	static public function get_broaders_list($concepts_list) {
		global $thesaurus_concepts_affichage_ordre, $thesaurus_concepts_concept_in_line;
	
		$concepts = $concepts_list->get_concepts();
		
		$datas = array(
				'title' => skos_onto::get_property_label("http://www.w3.org/2004/02/skos/core#Concept", "http://www.w3.org/2004/02/skos/core#broader"),
				'elements' => self::get_sorted_concepts($concepts, false)
		);
		
		return self::render($datas, "skos_view_concepts_broaders_list", array('concepts_in_line' => $thesaurus_concepts_concept_in_line*1));
	}
	
	/**
	 * Retourne l'affichage des concepts compos�s qui utilisent un concept
	 * @param skos_concepts_list $concepts_list Liste des concepts compos�s
	 * @return string
	 */
	static public function get_composed_concepts_list($concepts_list) {
		global $thesaurus_concepts_affichage_ordre, $thesaurus_concepts_concept_in_line;
		global $msg;
	
		$concepts = $concepts_list->get_concepts();
		$datas = array(
				'title' => $msg['skos_view_concepts_composed_concepts'],
				'elements' => self::get_sorted_concepts($concepts, false)
		);
		return self::render($datas, "skos_view_concepts_composed_concepts_list", array('concepts_in_line' => $thesaurus_concepts_concept_in_line*1));
	}
	
	/**
	 * Renvoie un tableau tri� des concepts selon leurs sch�mas
	 * @param skos_concept $concepts Tableau des concepts � trier
	 * @param boolean $all_links Sp�cifie si les liens vers les concepts compos�s doivent �tre d�compos�s
	 * @return skos_concept Tableau tri� [schema][] = concept
	 */
	static protected function get_sorted_concepts($concepts, $all_links) {
		global $msg;
		// On trie le tableau des concepts selon leurs schemas
		$sorted_concepts = array();
		foreach ($concepts as $concept) {
			if ($concept->get_schemes()) {
				$scheme = $concept->get_schemes();
			} else {
				$scheme = $msg['skos_view_concept_no_scheme'];
			}
			$sorted_concepts[$scheme][] = ($all_links ? skos_view_concept::get_concept_in_list_with_all_links($concept) : skos_view_concept::get_concept_in_list($concept));
		}
		//On g�n�re la liste
		foreach ($sorted_concepts as $scheme => $concepts) {
			// On trie par ordre alphab�tique si sp�cifi� en param�tre
			if ($thesaurus_concepts_affichage_ordre != 1) {
				asort($concepts);
			}
		}
		return $sorted_concepts;
	}
}
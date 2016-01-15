<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: marc_table.class.php,v 1.25 2015-10-15 09:16:09 apetithomme Exp $

// classe de gestion des tables MARC en XML

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

if ( ! defined( 'MARC_TABLE_CLASS' ) ) {
  define( 'MARC_TABLE_CLASS', 1 );

require_once("$class_path/XMLlist.class.php");
require_once($class_path.'/XMLlist_links.class.php');

class marc_list {

// propriétés

	public $table;
	public $parser;
	public $inverse_of = array();
	public $attributes = array();
// méthodes

	// constructeur
	function marc_list($type) {
		global $lang;
		global $charset;
		global $include_path;
		switch($type) {
			case 'country':
				$parser = new XMLlist("$include_path/marc_tables/$lang/country.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'icondoc':
				$parser = new XMLlist("$include_path/marc_tables/icondoc.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'icondoc_big':
				$parser = new XMLlist("$include_path/marc_tables/icondoc_big.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'lang':
				$parser = new XMLlist("$include_path/marc_tables/$lang/lang.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'doctype':
				$parser = new XMLlist("$include_path/marc_tables/$lang/doctype.xml", 0);
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'recordtype':
				$parser = new XMLlist("$include_path/marc_tables/$lang/recordtype.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'function':
				$parser = new XMLlist("$include_path/marc_tables/$lang/function.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'literal_function':
				$parser = new XMLlist("$include_path/marc_tables/$lang/literal_function.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'section_995':
				$parser = new XMLlist("$include_path/marc_tables/$lang/section_995.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'typdoc_995':
				$parser = new XMLlist("$include_path/marc_tables/$lang/typdoc_995.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;			
			case 'codstatdoc_995':
				$parser = new XMLlist("$include_path/marc_tables/$lang/codstat_995.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;			
			case 'diacritique':
			// Armelle : a priori plus utile
				$parser = new XMLlist("$include_path/marc_tables/diacritique.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'nivbiblio':
				$parser = new XMLlist("$include_path/marc_tables/$lang/nivbiblio.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;			
			case 'relationtypeup':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtypeup.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;		
			case 'relationtypedown':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtypedown.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case "etat_demandes":
				$parser = new XMLlist("$include_path/marc_tables/$lang/etat_demandes.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case "type_actions":
				$parser = new XMLlist("$include_path/marc_tables/$lang/type_actions_demandes.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;	
			case 'relationtype_aut':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtype_aut.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;	
			case 'relationtype_autup':
				$parser = new XMLlist("$include_path/marc_tables/$lang/relationtype_autup.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;	
			case 'music_key':
				$parser = new XMLlist("$include_path/marc_tables/$lang/music_key.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;	
			case 'music_form':
				$parser = new XMLlist("$include_path/marc_tables/$lang/music_form.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'oeuvre_type':
				$parser = new XMLlist("$include_path/marc_tables/$lang/oeuvre_type.xml");
				$parser->analyser();
				$this->table = $parser->table;
				break;
			case 'oeuvre_nature':
				$parser = new XMLlist("$include_path/marc_tables/$lang/oeuvre_nature.xml");
				$parser->setAttributesToParse(array(array('name' => "NATURE")));				
				$parser->analyser();
				$this->attributes=$parser->getAttributes();
				$this->table = $parser->table;
				break;
			case 'oeuvre_link':
				$parser = new XMLlist_links("$include_path/marc_tables/$lang/oeuvre_link.xml");
				$parser->setAttributesToParse(array(array('name' => 'EXPRESSION', 'default_value' => 'no'), array('name' => 'OTHER_LINK', 'default_value' => 'yes')));
				$parser->analyser();
				$this->table = $parser->table;
				$this->attributes = $parser->getAttributes();
				$this->inverse_of = $parser->inverse_of;
				break;
			default:
				$this->table=array();
				break;
		}
	}

}

class marc_select {

// propriétés

	public $display;
	public $libelle; // libellé du selected

// méthodes

	// constructeur


public function marc_select($type, $name='mySelector', $selected='', $onchange='', $option_premier_code='', $option_premier_info='')
	{
		global $charset;
		
		$source = new marc_list($type);
		$source_tab = $source->table;

		if($option_premier_code!=='' && $option_premier_info!=='') {
			$option_premier_tab = array($option_premier_code=>$option_premier_info);
			$source_tab=$option_premier_tab + $source_tab;
		}
		
		if ($onchange) $onchange=" onchange=\"$onchange\" ";
		$this->display = "<select id='$name' name='$name' $onchange >";
		
// A Revoir, mais je ne pense pas que cela soit encore d'actualité !
//		if($selected) {
			foreach($source_tab as $value=>$libelle) {
				if(is_array($libelle)){
					$this->display.='
						<optgroup label="'.htmlentities($value,ENT_QUOTES,$charset).'">';
					foreach($libelle as $key => $val){
						$this->gen_option($key, $val, $selected);
					}
					$this->display.="
						</optgroup>";	
				}else {
					$this->gen_option($value, $libelle, $selected);
				}
			}

// A Revoir, mais je ne pense pas que cela soit encore d'actualité !			
// 		} else {
// 			// cirque à cause d'un bug d'IE
// 			reset($source_tab);
// 			$this->display .= "<option value='".key($source_tab)."' selected='selected'>";
// 			$this->display .= htmlentities(pos($source_tab),ENT_QUOTES,$charset).'</option>';

// 			while(next($source_tab)) {
// 				$this->display .= "<option value='".key($source_tab)."'>";
// 				$this->display .= htmlentities(pos($source_tab),ENT_QUOTES,$charset).'</option>';
// 			}

// 		}
		$this->display .= "</select>";

	}
	
	private function gen_option($value, $libelle,$selected){
		if(!($value == $selected))
			$tag = "<option value='$value'>";
		else{
			$tag = "<option value='$value' selected='selected'>";
			$this->libelle="$libelle";
		}
		$this->display .= $tag.htmlentities($libelle,ENT_QUOTES,$charset)."</option>";
	}
}

} # fin de déclaration

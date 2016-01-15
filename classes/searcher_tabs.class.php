<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: searcher_tabs.class.php,v 1.4 2015-12-18 10:11:54 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/searcher/searcher_authorities_tab.class.php");
require_once($class_path."/search.class.php");
require_once($class_path."/elements_list/elements_authorities_list_ui.class.php");

class searcher_tabs {
	
	protected $xml_file = "";
	protected $full_path = "";
	protected $default_mode = 0;
	protected $url_target = "";
	protected $tabs = array();
	protected $current_mode = 0;
	protected $object_ids = array();
	protected $search_nb_results = 0;
	protected $page = 0;
	protected $nbepage = 0;
	protected $js_dynamic_check_form = "";
	
    public function __construct($xml_file="",$full_path='') {
    	$this->xml_file = $xml_file;
    	$this->full_path = $full_path;
    	$this->parse_search_file();
    }
       
    protected function parse_field($field) {
    	
    	$parsed_field = array();
    	
    	$parsed_field['ID'] = $field['ID'];
    	$parsed_field['TITLE'] = $field['TITLE'];
    	$parsed_field['INPUT_TYPE']=$field['INPUT'][0]['TYPE'];
    	$parsed_field['INPUT_OPTIONS']=$field['INPUT'][0];
    	$parsed_field['GLOBALVAR']=$field['GLOBALVAR'];
    	if(is_array($field['CLASS'])) {
	    	if($field['CLASS'][0]['NAME']) {
	    		$parsed_field['CLASS'] = $field['CLASS'][0]['NAME'];
	    		if(count($field['CLASS'][0]['FIELDRESTRICT'])) {
	    			$field_restrict = array();
	    			foreach ($field['CLASS'][0]['FIELDRESTRICT'] as $restrict) {
	    				$field_restrict[] = array(
	    						'field' => $restrict['FIELD'][0]['value'],
	    						'values' => explode(',', $restrict['VALUES'][0]['value']),
	    						'op' => $restrict['OP'][0]['value'],
	    						'not' => $restrict['NOT'][0]['value']
	    				);
	    			}
	    			$parsed_field['FIELDRESTRICT'] = $field_restrict;
	    		}
	    	} else {
	    		$parsed_field['CLASS'] = $field['CLASS'][0]['value'];
	    	}
    	}
    	return $parsed_field;
    }
    
    //Parse du fichier de configuration
	protected function parse_search_file() {
    	global $base_path, $include_path;
    	global $msg;
    	
    	if(!$this->fichier_xml) {
    		$this->fichier_xml = "authorities";
    	}
    	if(!$this->full_path){
	    	$filepath = $include_path."/searcher_tabs/".$this->fichier_xml."_subst.xml";
	    	if (!file_exists($filepath)) {
	    		$filepath = $include_path."/searcher_tabs/".$this->fichier_xml.".xml";
	    	}
    	} else {
    		$filepath = $this->full_path.$this->fichier_xml."_subst.xml";
    		if (!file_exists($filepath)) {
    			$filepath = $this->full_path.$this->fichier_xml.".xml";
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
    		if(count($cache) == 3){
    			$this->url_target = $cache[0];
    			$this->default_mode = $cache[1];
    			$this->tabs = $cache[2];
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
			$param=_parser_text_no_function_($xml, "PMBTABS");
	
			//Lecture du mode par défaut
			if($param['DEFAULT_MODE']) {
				$this->default_mode = $param['DEFAULT_MODE'][0]['value']; 
			}
			//Lecture de l'url
			$this->url_target = $param['URL_TARGET'][0]['value'];
					
			$this->tabs = array();
			//Lecture des onglets
			for ($i=0; $i<count($param["TABS"][0]["TAB"]); $i++) {
				$tab = array();
				$p_tab = $param['TABS'][0]['TAB'][$i];
				$tab['TITLE'] = $p_tab['TITLE'];
				$tab['MODE'] = $p_tab['MODE'];
				if($p_tab['MULTISEARCHCRITERIA']=='yes'){
					$tab['MULTISEARCHCRITERIA']=1;
				}else{
					$tab['MULTISEARCHCRITERIA']=0;				
				}
				
				$fields = array();
				$search_fields = $p_tab['SEARCHFIELDS'][0]['FIELD'];
				//Lecture des champs de recherche
				if(is_array($search_fields)){
					for ($j=0; $j<count($search_fields); $j++) {
						$fields[] = $this->parse_field($search_fields[$j]);
					}				
				}			
				$tab['SEARCHFIELDS'] = $fields;
				
				$fields = array();
				$filter_fields = $p_tab['FILTERFIELDS'][0]['FIELD'];
				//Lecture des filtres de recherche
				if(is_array($filter_fields)){
					for ($j=0; $j<count($filter_fields); $j++) {
						$fields[] = $this->parse_field($filter_fields[$j]);
					}
				}
				$tab['FILTERFIELDS'] = $fields;
	
				$this->tabs[$tab['MODE']] = $tab;
			}
			$tmp = fopen($tempFile, "wb");
			fwrite($tmp,serialize(array(
					$this->url_target,
					$this->default_mode,
					$this->tabs,
			)));
			fclose($tmp);
    	}
    }
        
    protected function get_field($field, $type) {
    	global $charset;
    	global $msg;
    	global $lang;
    	
    	//Récupération des valeurs du POST
    	$field_name = $this->get_field_name($field, $type);
    	$values = $this->get_values_from_field($field_name, $type);
    	$display = "<div class='row'>";
    	$display .= "<label class='etiquette'>".get_msg_to_display($field['TITLE'])."</label>";
    	$display .= "</div>";
    	$display .= "<div class='row'>";
    	switch ($field['INPUT_TYPE']) {
    		case "authoritie":
				if ($values[0] != 0) {
					switch ($field ['INPUT_OPTIONS'] ['SELECTOR']) {
						case "auteur" :
							$aut = new auteur($values[0]);
							if ($aut->rejete)
								$libelle = $aut->name . ', ' . $aut->rejete;
							else
								$libelle = $aut->name;
							if ($aut->date)
								$libelle .= " ($aut->date)";
							break;
						case "categorie" :
							$libelle = categories::getlibelle($values[0], $lang);
							break;
						case "editeur" :
							$ed = new editeur($values[0]);
							$libelle = $ed->name;
							if ($ed->ville)
								if ($ed->pays)
									$libelle .= " ($ed->ville - $ed->pays)";
								else
									$libelle .= " ($ed->ville)";
							break;
						case "collection" :
							$coll = new collection($values[0]);
							$libelle = $coll->name;
							break;
						case "subcollection" :
							$coll = new subcollection($values[0]);
							$libelle = $coll->name;
							break;
						case "serie" :
							$serie = new serie($values[0]);
							$libelle = $serie->name;
							break;
						case "indexint" :
							$indexint = new indexint($values[0]);
							if ($indexint->comment)
								$libelle = $indexint->name . " - " . $indexint->comment;
							else
								$libelle = $indexint->name;
							if ($thesaurus_classement_mode_pmb != 0) {
								$libelle = "[" . $indexint->name_pclass . "] " . $libelle;
							}
							break;
						case "titre_uniforme" :
							$tu = new titre_uniforme($values[0]);
							$libelle = $tu->name;
							break;
						default :
							$libelle = $values[0];
							break;
					}
				} else {
					$libelle = "";
				}
				if($field ['INPUT_OPTIONS'] ['ATT_ID_FILTER']) {
					$att_id_filter = "att_id_filter='".$field ['INPUT_OPTIONS'] ['ATT_ID_FILTER']."'";
				} else {
					$att_id_filter = "";
				}
				$display .= "<input type='text' id='".$field_name."' name='".$field_name."[]' autfield='".$field_name."' autocomplete='off' completion='".$field["INPUT_OPTIONS"]["AJAX"]."'  value='" . htmlentities ( $libelle, ENT_QUOTES, $charset ) . "' class='saisie-80em' $att_id_filter />";
    			break;
    		case "text":
    			if($field['INPUT_OPTIONS']['IFSEARCHEMPTY']) {
    				$this->js_dynamic_check_form .= "if(!document.getElementById('".$field_name."').value) document.getElementById('".$field_name."').value='".$field['INPUT_OPTIONS']['IFSEARCHEMPTY']."';";
    			}
    			if (substr($field['INPUT_OPTIONS']['PLACEHOLDER'],0,4)=="msg:") {
    				$input_placeholder = $msg[substr($field['INPUT_OPTIONS']['PLACEHOLDER'],4,strlen($field['INPUT_OPTIONS']['PLACEHOLDER'])-4)];
    			} else {
    				$input_placeholder = $field['INPUT_OPTIONS']["PLACEHOLDER"];
    			}
    			$display.="<input type='text' id='".$field_name."' name='".$field_name."[]' value='".htmlentities($values[0],ENT_QUOTES,$charset)."' class='saisie-80em' ".($input_placeholder?"placeholder='".htmlentities($input_placeholder,ENT_QUOTES,$charset)."'":"")."/>";
    			break;
			case "query_list":
				$query=$field["INPUT_OPTIONS"]["QUERY"][0]["value"];
				if ($field["INPUT_OPTIONS"]["FILTERING"] == "yes") {
    				$query = str_replace("!!acces_j!!", "", $query);
					$query = str_replace("!!statut_j!!", "", $query);
					$query = str_replace("!!statut_r!!", "", $query);
				}
				if ($field["INPUT_OPTIONS"]["QUERY"][0]["USE_GLOBAL"]) {
					$use_global = explode(",", $field["INPUT_OPTIONS"]["QUERY"][0]["USE_GLOBAL"]);
					for($j=0; $j<count($use_global); $j++) {
						$var_global = $use_global[$j];
						global $$var_global;
						$query = str_replace("!!".$var_global."!!", $$var_global, $query);
					}
				}
				
				$display .= "<select name='".$field_name."[]' style='width:40em;'>";
				if($field["INPUT_OPTIONS"]["QUERY"][0]["ALLCHOICE"] == "yes"){
					$display .= "<option value=''>".htmlentities(get_msg_to_display($field["INPUT_OPTIONS"]["QUERY"][0]["TITLEALLCHOICE"]), ENT_QUOTES, $charset)."</option>";
				}
				$result=pmb_mysql_query($query);
				while ($row=pmb_mysql_fetch_array($result)) {
					$display.="<option value='".htmlentities($row[0],ENT_QUOTES,$charset)."' ";
					$as=array_search($row[0],$values);
					if (($as!==null)&&($as!==false)) $display.=" selected";
						$display.=">".htmlentities($row[1],ENT_QUOTES,$charset)."</option>";
				}
				$display.="</select>";
				break;
			case "list":
				$options=$field["INPUT_OPTIONS"]["OPTIONS"][0];
				$display .= "<select name='".$field_name."[]' style='width:40em;'>";
				sort($options["OPTION"]);
				for ($i=0; $i<count($options["OPTION"]); $i++) {
					$display .= "<option value='".htmlentities($options["OPTION"][$i]["VALUE"],ENT_QUOTES,$charset)."' ";
					$as=array_search($options["OPTION"][$i]["VALUE"],$values);
					if (($as!==null)&&($as!==false)) $display .= " selected";
					if (substr($options["OPTION"][$i]["value"],0,4)=="msg:") {
						$display .= ">".htmlentities($msg[substr($options["OPTION"][$i]["value"],4,strlen($options["OPTION"][$i]["value"])-4)],ENT_QUOTES,$charset)."</option>";
					} else {
						$display .= ">".htmlentities($options["OPTION"][$i]["value"],ENT_QUOTES,$charset)."</option>";
					}
				}
				$display .= "</select>";
				break;
			case "marc_list":
				$options=new marc_list($field["INPUT_OPTIONS"]["NAME"][0]["value"]);
				$tmp=array();
				$tmp = $options->table;
				$tmp=array_map("convert_diacrit",$tmp);//On enlève les accents
				$tmp=array_map("strtoupper",$tmp);//On met en majuscule
				asort($tmp);//Tri sur les valeurs en majuscule sans accent
				foreach ( $tmp as $key => $value ) {
					$tmp[$key]=$options->table[$key];//On reprend les bons couples clé / libellé
				}
				$options->table=$tmp;
				reset($options->table);
			
				// gestion restriction par code utilise.
				$existrestrict=false;
				if ($field["INPUT_OPTIONS"]["RESTRICTQUERY"][0]["value"]) {
					$restrictquery=pmb_mysql_query($field["INPUT_OPTIONS"]["RESTRICTQUERY"][0]["value"]);
					if ($restrictqueryrow=@pmb_mysql_fetch_row($restrictquery)) {
						if ($restrictqueryrow[0]) {
							$restrictqueryarray=explode(",",$restrictqueryrow[0]);
							$existrestrict=true;
						}
					}
				}
			
				$display .= "<select name='".$field_name."[]' class=\"ext_search_txt\">";
				if($field["INPUT_OPTIONS"]["RESTRICTQUERY"][0]["ALLCHOICE"] == "yes"){
					$display .= "<option value=''>".htmlentities(get_msg_to_display($field["INPUT_OPTIONS"]["RESTRICTQUERY"][0]["TITLEALLCHOICE"]), ENT_QUOTES, $charset)."</option>";
				}
				while (list($key,$val)=each($options->table)) {
					if ($existrestrict && array_search($key,$restrictqueryarray)!==false) {
						$display .= "<option value='".htmlentities($key,ENT_QUOTES,$charset)."' ";
						$as=array_search($key,$values);
						if (($as!==null)&&($as!==false)) $display .= " selected";
						$display .= ">".htmlentities($val,ENT_QUOTES,$charset)."</option>";
					} elseif (!$existrestrict) {
						$display .= "<option value='".htmlentities($key,ENT_QUOTES,$charset)."' ";
						$as=array_search($key,$values);
						if (($as!==null)&&($as!==false)) $display .= " selected";
						$display .= ">".htmlentities($val,ENT_QUOTES,$charset)."</option>";
					}
				}
				$display .= "</select>";
				break;
			case "date":
				$date_formatee = format_date_input($values[0]);
				$date_clic = "onClick=\"openPopUp('./select.php?what=calendrier&caller=search_form&date_caller=".str_replace('-', '', $values[0])."&param1=".$field_name."_date&param2=".$field_name."[]&auto_submit=NO&date_anterieure=YES&format_return=IN', '".$field_name."_date', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\"  ";
				if (substr($field['INPUT_OPTIONS']["PLACEHOLDER"],0,4)=="msg:") {
					$input_placeholder = $msg[substr($field['INPUT_OPTIONS']["PLACEHOLDER"],4,strlen($field['INPUT_OPTIONS']["PLACEHOLDER"])-4)];
				} else {
					$input_placeholder = $field['INPUT_OPTIONS']["PLACEHOLDER"];
				}
				
				$display .= "<input type='hidden' name='".$field_name."_date' value='".str_replace('-','', $values[0])."' />
					<input type='text' name='".$field_name."[]' value='".htmlentities($date_formatee,ENT_QUOTES,$charset)."' ".($input_placeholder?"placeholder='".htmlentities($input_placeholder,ENT_QUOTES,$charset)."'":"")."/>
					<input class='bouton_small' type='button' name='".$field_name."_date_lib_bouton' value='".$msg["bouton_calendrier"]."' ".$date_clic." />";
				break;
    	}
    	$display .= "</div>";
    	
    	return $display;
    }
    
    public function get_form() {
    	global $msg;
    	global $current_module;
    	
    	$form = "";
    	$tab=$this->get_current_tab();
    	
    	$form .= "
    		<form id='search_authorities' class='form-".$current_module."' action='".$this->url_target."&mode=".$tab['MODE']."' method='post' onSubmit='return check_form(this);'>
    		<h3>".get_msg_to_display($tab['TITLE'])."</h3>
    		<div class='form-contenu'>";
    	foreach ($tab['SEARCHFIELDS'] as $search_field) {
    		$form .= $this->get_field($search_field, 'search');
    	}
    	$form .= "
			<div class='row'>
				<span class='saisie-contenu'>
					$msg[155]&nbsp;<a class='aide' title='$msg[1900]$msg[1901]$msg[1902]' href='./help.php?whatis=regex' onclick='aide_regex();return false;'>$msg[1550]</a>
				</span>
			</div>";
    	foreach ($tab['FILTERFIELDS'] as $i=>$filter_field) {
    		if($i%2 == 0) $form .= "<div class='colonne2'>";
    		else $form .= "<div class='colonne_suite'>";
    		$form .= $this->get_field($filter_field, 'filter');
    		$form .= "</div>";
    	}
    	$form .= "
    				<div class='row'></div>
	    		</div>
	    		<div class='row'>
	    			<input type='hidden' name='action' value='search' />
	    			<input class='bouton' type='submit' value='".$msg['search']."' />
	    		</div>
    		</form>
			<script type='text/javascript'>
				document.forms['search_authorities'].elements[0].focus();
				function aide_regex() {
					var fenetreAide;
					var prop = 'scrollbars=yes, resizable=yes';
					fenetreAide = openPopUp('./help.php?whatis=regex', 'regex_howto', 500, 400, -2, -2, prop);
				}
	    		function check_form(obj) {
	    			var searchIsEmpty = true;
	    			".($this->js_dynamic_check_form != "" ? "var has_js_dynamic = true;" : "var has_js_dynamic = false;")."
	    			var query = obj.querySelectorAll('input[type=text]');
	    			for(var i = 0; i < query.length; i++) {
	    				if(query[i].value != '') {
	    					searchIsEmpty = false;
	    				}
    				}
	    			if(searchIsEmpty) {
	    				if(!has_js_dynamic) {
	    					return false;
    					} else {
	    					".$this->js_dynamic_check_form."
    					}
    				}
	    			return true;
    			}			
		</script>
    		<script type='text/javascript' src='./javascript/ajax.js'></script>
			<script>ajax_parse_dom();</script>";
    		
    	return $form;
    }

    public function get_title() {
		global $msg;
		
    	$title = "<h1>";
    	$title .= $msg['search']." : ".get_msg_to_display($this->tabs[$this->current_mode]['TITLE']);    		
    	$title .= "</h1>";
    	 
    	return $title;
    }
    
    public function get_tabs() {
    	
    	$tabs = "<div class='hmenu'>";
    	foreach ($this->tabs as $tab) {
    		$tabs .= "<span ".($this->current_mode == $tab['MODE'] ? "class='selected'" : "")."><a href='".$this->url_target."&mode=".$tab['MODE']."'>".get_msg_to_display($tab['TITLE'])."</a></span>";
    	}
    	$tabs .= "</div>";
    	
    	return $tabs;
    }

    protected function get_values_from_field($field_name) {
    	
    	global $$field_name;
    	 
    	if(is_array($$field_name)) {
    		return $$field_name;
    	} else {
    		return array();
    	}
    }
    
    private function get_field_name($field, $type = 'search') {
    	if($type == 'filter') {
    		$field_name="filter_field_tab_".$field['ID'];
    	} else {
    		$field_name="search_field_tab_".$field['ID'];
    	}
    	return $field_name;
    }
    
    private function get_values_from_form() {

    	$data = array();
    	$tab=$this->get_current_tab();
    	foreach ($tab['SEARCHFIELDS'] as $search_field) {
    		$t = array();
    		$t['id'] = $search_field['ID'];
    		$t['values'] = $this->get_values_from_field($this->get_field_name($search_field, 'search'));
    		$t['class'] = $search_field['CLASS'];
    		if(is_array($search_field['FIELDRESTRICT'])) {
    			$t['fieldrestrict'] = $search_field['FIELDRESTRICT'];
    		}
    		$data['SEARCHFIELDS'][]= $t;
    	}
    	foreach ($tab['FILTERFIELDS'] as $filter_field) {
    		$t = array();
    		$t['id'] = $filter_field['ID'];
    		$t['values'] = $this->get_values_from_field($this->get_field_name($filter_field, 'filter'));
    		$t['globalvar'] = $filter_field['GLOBALVAR'][0]['value'];
    		$data['FILTERFIELDS'][]= $t;
    	}
    	return $data;
    }
    
    private function search() {
    	global $page, $nb_per_page_search;
    	
    	$values = $this->get_values_from_form();
    	$searcher_authorities_tab = new searcher_authorities_tab($values);
    	$this->objects_ids = $searcher_authorities_tab->get_sorted_result("default",$page*$nb_per_page_search,$nb_per_page_search);
    	$this->search_nb_results = $searcher_authorities_tab->get_nb_results();
    }
    
    protected function get_current_tab(){
    	return $this->tabs[$this->current_mode];
    }

    protected function make_hidden_form() {
    	
    	$tab=$this->get_current_tab();
    	$form = "<form name='store_search'  action='".$this->url_target."&mode=".$tab['MODE']."' method='post' style='display : none'>";
    	foreach ($tab['SEARCHFIELDS'] as $search_field) {
    		$field_name = $this->get_field_name($search_field, 'search');
    		$values = $this->get_values_from_field($field_name);
    		for($i=0; $i < count($values); $i++) {
    			$form .= "<input type='hidden' name='".$field_name."[]' value='".$values[$i]."' />";
    		}
    	
    	}
    	foreach ($tab['FILTERFIELDS'] as $filter_field) {
    		$field_name = $this->get_field_name($filter_field, 'filter');
    		$values = $this->get_values_from_field($field_name);
    		for($i=0; $i < count($values); $i++) {
    			$form .= "<input type='hidden' name='".$field_name."[]' value='".$values[$i]."' />";
    		}
    	}
    	$form .= "<input type='hidden' name='action' value='search' />
    		<input type='hidden' name='page' value='".$this->page."'/>
    		</form>";
    	return $form;
    }
    
    protected function get_human_field($field, $values) {
    	global $msg, $charset;
    	
		switch ($field["INPUT_TYPE"]) {
			case "list":
				$options=$field["INPUT_OPTIONS"]["OPTIONS"][0];
				$opt=array();
				for ($j=0; $j<count($options["OPTION"]); $j++) {
					if (substr($options["OPTION"][$j]["value"],0,4)=="msg:") {
						$opt[$options["OPTION"][$j]["VALUE"]]=$msg[substr($options["OPTION"][$j]["value"],4,strlen($options["OPTION"][$j]["value"])-4)];
					} else {
						$opt[$options["OPTION"][$j]["VALUE"]]=$options["OPTION"][$j]["value"];
					}
				}
				for ($j=0; $j<count($values); $j++) {
					$field_aff[$j]=$opt[$values[$j]];
				}
				break;
			case "query_list":
				$requete=$field["INPUT_OPTIONS"]["QUERY"][0]["value"];
				if ($field["FILTERING"] == "yes") {
					$requete = str_replace("!!acces_j!!", "", $requete);
					$requete = str_replace("!!statut_j!!", "", $requete);
					$requete = str_replace("!!statut_r!!", "", $requete);
				}
				if ($field["INPUT_OPTIONS"]["QUERY"][0]["USE_GLOBAL"]) {
					$use_global = explode(",", $field["INPUT_OPTIONS"]["QUERY"][0]["USE_GLOBAL"]);
					for($j=0; $j<count($use_global); $j++) {
						$var_global = $use_global[$j];
						global $$var_global;
						$requete = str_replace("!!".$var_global."!!", $$var_global, $requete);
					}
				}
				$resultat=pmb_mysql_query($requete);
				$opt=array();
				while ($r_=@pmb_mysql_fetch_row($resultat)) {
					$opt[$r_[0]]=$r_[1];
				}
				for ($j=0; $j<count($values); $j++) {
					$field_aff[$j]=$opt[$values[$j]];
				}
				break;
			case "marc_list":
				$opt=new marc_list($field["INPUT_OPTIONS"]["NAME"][0]["value"]);
				for ($j=0; $j<count($values); $j++) {
    				$field_aff[$j]=$opt->table[$values[$j]];
				}
				break;
			case "date":
				$field_aff[0]=format_date($values[0]);
				break;
			case "authoritie":
				for($j=0 ; $j<sizeof($values) ; $j++){
					if(is_numeric($values[$j]) && ($$op == "AUTHORITY")){
						switch ($field['INPUT_OPTIONS']['SELECTOR']){
							case "categorie" :
								$thes = thesaurus::getByEltId ( $values [$j] );
								$values [$j] = categories::getlibelle ( $values [$j], $lang ) . " [" . $thes->libelle_thesaurus . "]";
								if (isset ( $fieldvar ["id_thesaurus"] )) {
									unset ( $fieldvar ["id_thesaurus"] );
								}
								break;
							case "auteur" :
								$aut = new auteur ( $values [$j] );
								if ($aut->rejete)
									$values [$j] = $aut->name . ', ' . $aut->rejete;
								else
									$values [$j] = $aut->name;
								if ($aut->date)
									$values [$j] .= " ($aut->date)";
								break;
							case "editeur" :
								$ed = new editeur ( $values [$j] );
								$values [$j] = $ed->name;
								if ($ed->ville)
									if ($ed->pays)
										$values [$j] .= " ($ed->ville - $ed->pays)";
									else
										$values [$j] .= " ($ed->ville)";
								break;
							case "collection" :
								$coll = new collection ( $values [$j] );
								$values [$j] = $coll->name;
								break;
							case "subcollection" :
								$coll = new subcollection ( $values [$j] );
								$values [$j] = $coll->name;
								break;
							case "serie" :
								$serie = new serie ( $values [$j] );
								$values [$j] = $serie->name;
								break;
							case "indexint" :
								$indexint = new indexint ( $values [$j] );
								if ($indexint->comment)
									$values [$j] = $indexint->name . " - " . $indexint->comment;
								else
									$values [$j] = $indexint->name;
								if ($thesaurus_classement_mode_pmb != 0) {
									$values [$j] = "[" . $indexint->name_pclass . "] " . $values [$j];
								}
								break;
							case "titre_uniforme" :
								$tu = new titre_uniforme ( $values [$j] );
								$values [$j] = $tu->name;
								break;
							case "notice" :
								$requete = "select if(serie_name is not null,if(tnvol is not null,concat(serie_name,', ',tnvol,'. ',tit1),concat(serie_name,'. ',tit1)),tit1) AS tit from notices left join series on serie_id=tparent_id where notice_id='" . $field [$j] . "' ";
								$res = pmb_mysql_query ( $requete );
								if ($res && pmb_mysql_num_rows ( $res )) {
									$values [$j] = pmb_mysql_result ( $res, 0, 0 );
								}
								break;
						}
					}
				}
				$field_aff= $values;
				break;
			default:
				$field_aff[0]=$values[0];
				break;
		}
		return '<b>'.get_msg_to_display($field['TITLE']).'</b> '.implode(' ', $field_aff);
    }
    
    protected function make_human_query() {
    	global $msg;
    	
    	$human_queries = array();
    	$tab=$this->get_current_tab();
    	foreach ($tab['SEARCHFIELDS'] as $search_field) {
    		$values = $this->get_values_from_field($this->get_field_name($search_field, 'search'));
    		if(is_array($values) && ($values[0] != '')) {
    			$human_queries[] = $this->get_human_field($search_field, $values);
    		}
    	}
    	foreach ($tab['FILTERFIELDS'] as $filter_field) {
    		$values = $this->get_values_from_field($this->get_field_name($filter_field, 'filter'));
    		if(is_array($values) && ($values[0] != '')) {
    			$human_queries[] = $this->get_human_field($filter_field, $values);
    		}
    	}
    	$research=implode(', ', $human_queries);
    	if($this->search_nb_results) {
    		$research .= " => ".sprintf($msg["searcher_results"], $this->search_nb_results);
    	} else {
    		$research .= " => ".sprintf($msg[1915], $this->search_nb_results);
    	}
    	return "<div class='othersearchinfo'>$research</div>";
    }
    	
    protected function pager() {
    	global $msg;
    	global $page, $nb_per_page_search;
    
    	if (!$this->search_nb_results) return;
    	
    	if($page) $this->page = $page;
    	$this->nbepage=ceil($this->search_nb_results/$nb_per_page_search);
    	$suivante = $this->page+1;
    	$precedente = $this->page-1;
    	if (!$this->page) $page_en_cours=0 ;
    	else $page_en_cours=$this->page ;
    
    	// affichage du lien précédent si nécéssaire
    	if($precedente >= 0)
    		$nav_bar .= "<a href='#' onClick=\"document.store_search.page.value=$precedente; document.store_search.submit(); return false;\"><img src='./images/left.gif' border='0'  title='$msg[48]' alt='[$msg[48]]' hspace='3' align='middle'></a>";
    
    	$deb = $page_en_cours - 10 ;
    	if ($deb<0) $deb=0;
    	for($i = $deb; ($i < $this->nbepage) && ($i<$page_en_cours+10); $i++) {
    		if($i==$page_en_cours) $nav_bar .= "<strong>".($i+1)."</strong>";
    		else $nav_bar .= "<a href='#' onClick=\"document.store_search.page.value=$i; document.store_search.submit(); return false;\">".($i+1)."</a>";
    		if($i<$this->nbepage) $nav_bar .= " ";
    	}
    
    	if($suivante<$this->nbepage)
    		$nav_bar .= "<a href='#' onClick=\"document.store_search.page.value=$suivante; document.store_search.submit(); return false;\"><img src='./images/right.gif' border='0' title='$msg[49]' alt='[$msg[49]]' hspace='3' align='middle'></a>";
    
    	// affichage de la barre de navigation
    	print "<div align='center'>$nav_bar</div>";
    }
    
    public function show_result() {
    	global $msg,$charset,$dbh;
    	global $begin_result_liste;
    	global $end_result_liste;
    	
    	print $this->make_hidden_form();
    	print $this->make_human_query();
    	if(count($this->objects_ids)) {
    		$elements_authorities_list_ui = new elements_authorities_list_ui($this->objects_ids, $this->search_nb_results, 1);
    		$elements = $elements_authorities_list_ui->get_elements_list();
    		print $begin_result_liste;
    		print $elements[0];
    		print $end_result_liste;
    		$this->pager();
    	}
    }
    
    protected function is_multi_search_criteria(){

    	$tab=$this->get_current_tab();
    	return $tab['MULTISEARCHCRITERIA'];
    }
    
    public function proceed($mode=0, $action="") {
    	
    	if(!$mode) {
    		$this->current_mode=$this->default_mode;
    	} else {
    		$this->current_mode=$mode;
    	}
    	print $this->get_title();
    	print $this->get_tabs();

    	if($this->is_multi_search_criteria()){
    		$sc=new search_authorities(true, 'search_fields_authorities');
    		$tab=$this->get_current_tab();
    		switch($action){
    			case 'search':
    				print $sc->show_results($this->url_target."&mode=".$tab['MODE']."&action=search",$this->url_target."&mode=".$tab['MODE'], true, '', true );
    				break;
    			default:
    				print $sc->show_form($this->url_target."&mode=".$tab['MODE'],$this->url_target."&mode=".$tab['MODE']."&action=search");
    				break;
    		}
       	} else {
       		switch($action){
       			case 'search':
       				$this->search();
       				print $this->show_result();
       				break;
       			default:
       				print $this->get_form();
       				break;
       		}
       	}
    }
}
?>
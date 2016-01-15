<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: XMLlist_links.class.php,v 1.5 2015-12-18 09:37:26 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classe de gestion des documents XML

class XMLlist_links extends XMLlist {

	public $inverse_of = array();	// Tableau des attributs inverseOf dans le fichier XML
	public $sens = '';					// Attribut sens dans le fichier XML
	
	// constructeur
	function XMLlist_links($fichier, $s=1) {
		parent::XMLlist($fichier,$s);
	}
		                

	//Méthodes
	function debutBalise($parser, $nom, $attributs) {
		parent::debutBalise($parser, $nom, $attributs);
		global $_starttag;
	
		if($nom == 'ENTRY' && $attributs['INVERSEOF']){
			$this->inverse_of[$attributs['CODE']] = $attributs['INVERSEOF'];
		}
		$this->sens = 'flat';
		if($nom == 'ENTRY' && $attributs['SENS']){
			$this->sens = $attributs['SENS'];
		}
	}
	
	//Méthodes
	function debutBaliseSubst($parser, $nom, $attributs) {
		global $_starttag;
		parent::debutBaliseSubst($parser, $nom, $attributs);
		
		if($nom == 'ENTRY' && $attributs['INVERSEOF']){
			$this->inverse_of[$attributs['CODE']] = $attributs['INVERSEOF'];
		}
		$this->sens = 'flat';
		if($nom == 'ENTRY' && $attributs['SENS']){
			$this->sens = $attributs['SENS'];
		}
		$table = $this->table;
		foreach($table as $sens => $infos){
			foreach($infos as $code => $label){
				if($code== $attributs['CODE']){
					unset($this->table[$sens][$code]);
					break;
				}
			}
		
		}
	}
	
	function finBalise($parser, $nom) {
		parent::finBalise($parser, $nom);
		$this->sens = '';
	}

	function finBaliseSubst($parser, $nom) {
		parent::finBaliseSubst($parser, $nom);
		$this->sens = '';
	}
	
	function texte($parser, $data) {
		global $_starttag; 
		if($this->current){
			if ($_starttag) {
				$this->table[$this->sens][$this->current] = $data;
				$_starttag=false;
			} else {
				$this->table[$this->sens][$this->current].= $data;
			}
		}
	}

	function texteSubst($parser, $data) {
		global $_starttag; 
		$this->flag_elt = true;
		if ($this->current) {
		if ($_starttag) {
				$this->table[$this->sens][$this->current] = $data;
				$_starttag=false;
			} else {
				$this->table[$this->sens][$this->current].= $data;
			}
		}
	}
	

 // Modif Armelle Nedelec recherche de l'encodage du fichier xml et transformation en charset'
 	function analyser() 
 	{
 		global $charset;
 		global $base_path;
		if (!($fp = @fopen($this->fichierXml, "r"))) {
			die("impossible d'ouvrir le fichier XML $this->fichierXml");
		}
 		//vérification fichier pseudo-cache dans les temporaires
		$fileInfo = pathinfo($this->fichierXml);
		if($this->fichierXmlSubst && file_exists($this->fichierXmlSubst)){
			$tempFile = $base_path."/temp/XMLWithSubst".preg_replace("/[^a-z0-9]/i","",$fileInfo['dirname'].$fileInfo['filename'].$charset).".tmp";
			$with_subst=true;
		}else{
			$tempFile = $base_path."/temp/XML".preg_replace("/[^a-z0-9]/i","",$fileInfo['dirname'].$fileInfo['filename'].$charset).".tmp";
			$with_subst=false;
		}
		
		$dejaParse = false;
		if(file_exists($tempFile)){
			//Le fichier XML original a-t-il été modifié ultérieurement ?
			if(filemtime($this->fichierXml)>filemtime($tempFile)){
				//on va re-générer le pseudo-cache
				unlink($tempFile);
			} else {
				//On regarde aussi si le fichier subst à été modifié après le fichier temp
				if($with_subst){
					if(filemtime($this->fichierXmlSubst)>filemtime($tempFile)){
						//on va re-générer le pseudo-cache
						unlink($tempFile);
					} else {
						$dejaParse = true;
					}
				}else{
					$dejaParse = true;
				}
			}
		}
		if($dejaParse){
			fclose($fp);
			$tmp = fopen($tempFile, "r");
			$tables = unserialize(fread($tmp,filesize($tempFile)));
			if(count($tables)!= 3){
				unlink($tempFile);
				$this->analyser();
				return;
			}
			$this->table = $tables[0];
			$this->inverse_of = $tables[1];
			$this->attributes = $tables[2];
			fclose($tmp);
		} else {
			$this->table = array();
			$this->inverse_of = array();
			$this->attributes = array();
			$file_size=filesize ($this->fichierXml);
			$data = fread ($fp, $file_size);
	
	 		$rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";
			if (preg_match($rx, $data, $m)) $encoding = strtoupper($m[1]);
				else $encoding = "ISO-8859-1";
			
	 		$this->analyseur = xml_parser_create($encoding);
	 		xml_parser_set_option($this->analyseur, XML_OPTION_TARGET_ENCODING, $charset);		
			xml_parser_set_option($this->analyseur, XML_OPTION_CASE_FOLDING, true);
			xml_set_object($this->analyseur, $this);
			xml_set_element_handler($this->analyseur, "debutBalise", "finBalise");
			xml_set_character_data_handler($this->analyseur, "texte");
		
			fclose($fp);
	
			if ( !xml_parse( $this->analyseur, $data, TRUE ) ) {
				die( sprintf( "erreur XML %s à la ligne: %d ( $this->fichierXml )\n\n",
				xml_error_string(xml_get_error_code( $this->analyseur ) ),
				xml_get_current_line_number( $this->analyseur) ) );
			}
	
			xml_parser_free($this->analyseur);
	
			if ($fp = @fopen($this->fichierXmlSubst, "r")) {
				$file_sizeSubst=filesize ($this->fichierXmlSubst);
				if($file_sizeSubst) {
					$data = fread ($fp, $file_sizeSubst);
					fclose($fp);
			 		$rx = "/<?xml.*encoding=[\'\"](.*?)[\'\"].*?>/m";
					if (preg_match($rx, $data, $m)) $encoding = strtoupper($m[1]);
						else $encoding = "ISO-8859-1";
					$this->analyseur = xml_parser_create($encoding);
					xml_parser_set_option($this->analyseur, XML_OPTION_TARGET_ENCODING, $charset);		
					xml_parser_set_option($this->analyseur, XML_OPTION_CASE_FOLDING, true);
					xml_set_object($this->analyseur, $this);
					xml_set_element_handler($this->analyseur, "debutBaliseSubst", "finBaliseSubst");
					xml_set_character_data_handler($this->analyseur, "texteSubst");
					if ( !xml_parse( $this->analyseur, $data, TRUE ) ) {
						die( sprintf( "erreur XML %s à la ligne: %d ( $this->fichierXmlSubst )\n\n",
						xml_error_string(xml_get_error_code( $this->analyseur ) ),
						xml_get_current_line_number( $this->analyseur) ) );
						}
					xml_parser_free($this->analyseur);
				}	
			}
			
			if ($this->s && is_array($this->table)) {
				reset($this->table);
				$tmp = array();
				foreach($this->table as $sens => $links){
					$tmp[$sens] = array_map("convert_diacrit",$this->table[$sens]); //On enlève les accents
					$tmp[$sens]=array_map("strtoupper",$tmp[$sens]);//On met en majuscule
					asort($tmp[$sens]);//Tri sur les valeurs en majuscule sans accent
					foreach ( $tmp[$sens] as $key => $value ) {
						$tmp[$sens][$key]= $this->table[$sens][$key];
					}
				}
				$this->table=$tmp;
			}			
			//on écrit le temporaire
			$tmp = fopen($tempFile, "wb");
			fwrite($tmp,serialize(array($this->table,$this->inverse_of,$this->attributes)));
			fclose($tmp);
		}
	}
}
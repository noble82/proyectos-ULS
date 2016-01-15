<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: XMLlist.class.php,v 1.34 2015-10-15 09:16:09 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

// classe de gestion des documents XML

if ( ! defined( 'XML_LIST_CLASS' ) ) {
  define( 'XML_LIST_CLASS', 1 );

class XMLlist {
	
	public $analyseur;
	public $fichierXml;
	public $fichierXmlSubst; // nom du fichier XML de substitution au cas o�.
	public $current;
	public $table;
	public $table_js;
	public $tablefav;
	public $flag_fav;
	public $s;
	public $flag_elt ; // pour traitement des entr�es supprim�es
	public $flag_order;
	public $order;
	public $js_group;
	public $attributesToParse=array();
	public $attributes=array();

	// constructeur
	function XMLlist($fichier, $s=1) {
		$this->fichierXml = $fichier;
		$this->fichierXmlSubst = str_replace(".xml", "", $fichier)."_subst.xml" ;
		$this->s = $s;
		$this->flag_order = false;		
	}
		                

	//M�thodes
	function debutBalise($parser, $nom, $attributs) {
		global $_starttag; $_starttag=true;
		if($nom == 'ENTRY' && $attributs['CODE'])
			$this->current = $attributs['CODE'];
		if($nom == 'ENTRY' && $attributs['ORDER']) {
			$this->flag_order = true;
			$this->order[$attributs['CODE']] =  $attributs['ORDER'];
		}	
		if($nom == 'ENTRY' && $attributs['JS']){
			$this->js_group = $attributs['JS'];
		}
		foreach ($this->attributesToParse as $attribute){
			if ($nom == 'ENTRY' && isset($attribute['default_value'])) {
				$this->attributes[$attributs['CODE']][$attribute['name']] = $attribute['default_value'];
			}
			if ($nom == 'ENTRY' && $attributs[$attribute['name']]){
				$this->attributes[$attributs['CODE']][$attribute['name']]=$attributs[$attribute['name']];
			}
		}
		if($nom == 'XMLlist') {
			$this->table = array();
			$this->fav = array();
		}
	}
	
	/**
	 * D�finit une s�rie d'attributs suppl�mentaires � parser
	 * @param array $attributes array('name','default_value')
	 */
	function setAttributesToParse($attributes=array()){
		$this->attributesToParse=$attributes;
	}

	function getAttributes(){
		return $this->attributes;
	}
	  
	//M�thodes
	function debutBaliseSubst($parser, $nom, $attributs) {
		global $_starttag; $_starttag=true;
		if($nom == 'ENTRY' && $attributs['CODE']) {
			$this->flag_elt = false ;
			$this->current = $attributs['CODE'];
		}
		if($nom == 'ENTRY' && $attributs['ORDER']) {
			$this->flag_order = true;
			$this->order[$attributs['CODE']] =  $attributs['ORDER'];
		}
		if($nom == 'ENTRY' && $attributs['JS']){
			$this->js_group = $attributs['JS'];
		}
		foreach ($this->attributesToParse as $attribute){
			if ($nom == 'ENTRY' && isset($attribute['default_value'])) {
				$this->attributes[$attributs['CODE']][$attribute['name']] = $attribute['default_value'];
			}
			if ($nom == 'ENTRY' && $attributs[$attribute['name']]) {
				$this->attributes[$attributs['CODE']][$attribute['name']] = $attributs[$attribute['name']];
			}
		}
		if($nom == 'ENTRY' && $attributs['FAV']) {
			$this->flag_fav =  $attributs['FAV'];
		}
	}
	
	function finBalise($parser, $nom) {
		// ICI pour affichage des codes des messages en dur 
		if ($_COOKIE[SESSname."-CHECK-MESSAGES"]==1 && strpos($this->fichierXml, "messages")) {
			$this->table[$this->current] = "__".$this->current."**".$this->table[$this->current];
		} 
		$this->current = '';
		$this->js_group = "";
		}

	function finBaliseSubst($parser, $nom) {
		// ICI pour affichage des codes des messages en dur 
		if ($_COOKIE[SESSname."-CHECK-MESSAGES"]==1 && strpos($this->fichierXml, "messages")) {
			$this->table[$this->current] = "__".$this->current."**".$this->table[$this->current];
		} 
		if ((!$this->flag_elt) && ($nom=='ENTRY')) unset($this->table[$this->current]) ;
		$this->current = '';
		$this->js_group = "";
		$this->flag_fav =  false;
		}
	
	function texte($parser, $data) {
		global $_starttag; 
		if($this->current)
			if ($_starttag) {
				if($this->js_group){
					$this->table_js[$this->js_group][$this->current] = $data;
				}else{
					$this->table[$this->current] = $data;
				}
				$_starttag=false;
			} else {
				if($this->js_group){
					$this->table_js[$this->js_group][$this->current].= $data;
				}else{
					$this->table[$this->current] .= $data;
				}
			}
		}

	function texteSubst($parser, $data) {
		global $_starttag; 
		$this->flag_elt = true ;
		if ($this->current) {
			if ($_starttag) {
				if($this->js_group){
					$this->table_js[$this->js_group][$this->current] = $data;
				}else{
					$this->table[$this->current] = $data;
				}
				$_starttag=false;
			} else {
				if($this->js_group){
					$this->table_js[$this->js_group][$this->current].= $data;
				}else{
					$this->table[$this->current] .= $data;
				}
			}
			if ($this->flag_fav) $this->tablefav[$this->current] = $this->flag_fav;
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
 		//v�rification fichier pseudo-cache dans les temporaires
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
			//Le fichier XML original a-t-il �t� modifi� ult�rieurement ?
			if(filemtime($this->fichierXml)>filemtime($tempFile)){
				//on va re-g�n�rer le pseudo-cache
				unlink($tempFile);
			} else {
				//On regarde aussi si le fichier subst � �t� modifi� apr�s le fichier temp
				if($with_subst){
					if(filemtime($this->fichierXmlSubst)>filemtime($tempFile)){
						//on va re-g�n�rer le pseudo-cache
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
			if(count($tables)!= 4){
				unlink($tempFile);
				$this->analyser();
				return;
			}
			$this->table = $tables[0];
			$this->table_js = $tables[1];
			$this->tablefav = $tables[2];
			$this->attributes = $tables[3];
			fclose($tmp);
		} else {
			$this->table = array();
			$this->table_js = array();
			$this->tablefav = array();
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
				die( sprintf( "erreur XML %s � la ligne: %d ( $this->fichierXml )\n\n",
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
						die( sprintf( "erreur XML %s � la ligne: %d ( $this->fichierXmlSubst )\n\n",
						xml_error_string(xml_get_error_code( $this->analyseur ) ),
						xml_get_current_line_number( $this->analyseur) ) );
						}
					xml_parser_free($this->analyseur);
				}	
			}
			if ($this->s && is_array($this->table)) {
				reset($this->table);
				$tmp=array();
				$tmp=array_map("convert_diacrit",$this->table);//On enl�ve les accents
				$tmp=array_map("strtoupper",$tmp);//On met en majuscule
				asort($tmp);//Tri sur les valeurs en majuscule sans accent
				foreach ( $tmp as $key => $value ) {
	       			$tmp[$key]=$this->table[$key];//On reprend les bons couples cl� / libell�
				}
				$this->table=$tmp;
			}
			if ($this->s && is_array($this->table_js)) {
				reset($this->table_js);
				$tmp=array();
				$tmp=array_map("convert_diacrit",$this->table_js);//On enl�ve les accents
				$tmp=array_map("strtoupper",$tmp);//On met en majuscule
				asort($tmp);//Tri sur les valeurs en majuscule sans accent
				foreach ( $tmp as $key => $value ) {
					$tmp[$key]=$this->table_js[$key];//On reprend les bons couples cl� / libell�
				}
				$this->table_js=$tmp;
			}
			if ($this->s && is_array($this->tablefav) && count($this->tablefav)) {
				reset($this->tablefav);
				$tmp=array();
				$tmp=array_map("convert_diacrit",$this->tablefav);//On enl�ve les accents
				$tmp=array_map("strtoupper",$tmp);//On met en majuscule
				asort($tmp);//Tri sur les valeurs en majuscule sans accent
				foreach ( $tmp as $key => $value ) {
					$tmp[$key]=$this->tablefav[$key];//On reprend les bons couples cl� / libell�
				}
				$this->tablefav=$tmp;
			}	
			if ($this->s && is_array($this->attributes)) {
				reset($this->attributes);
				$tmp=array();				
				foreach ( $this->attributes as  $key => $attributes ) {		
					$tmp_attributes=array();		
					$tmp_attributes=array_map("convert_diacrit",$attributes);//On enl�ve les accents
					$tmp_attributes=array_map("strtoupper",$tmp_attributes);//On met en majuscule
					asort($tmp);
					$tmp[$key]=$tmp_attributes;
				}
				$this->attributes=$tmp;				
			}			
			if($this->flag_order == true){
				$table_tmp = array();
				asort($this->order);
				foreach ($this->order as $key =>$value){
					$table_tmp[$key] = $this->table[$key];
					unset($this->table[$key]);
				}
				$this->table = $table_tmp + $this->table;//array_merge r��crivait les cl�s num�riques donc probl�me.
				$table_tmp = array();
				asort($this->order);
				foreach ($this->order as $key =>$value){
					$table_tmp[$key] = $this->table_js[$key];
					unset($this->table_js[$key]);
				}
				$this->table_js = $table_tmp + $this->table_js;//array_merge r��crivait les cl�s num�riques donc probl�me.
				if (count($this->tablefav)) {
					$table_tmp = array();
					asort($this->order);
					foreach ($this->order as $key =>$value){
						if (isset($this->tablefav[$key])) {
							$table_tmp[$key] = $this->tablefav[$key];
							unset($this->tablefav[$key]);
						}
					}
					$this->tablefav = $table_tmp + $this->tablefav;//array_merge r��crivait les cl�s num�riques donc probl�me.
				}
				$table_tmp = array();
				asort($this->order);
				foreach ($this->order as $key =>$value){
					$table_tmp[$key] = $this->attributes[$key];
					unset($this->attributes[$key]);
				}
				$this->attributes = $table_tmp + $this->attributes;//array_merge r��crivait les cl�s num�riques donc probl�me.
			}
			
			//on �crit le temporaire
			$tmp = fopen($tempFile, "wb");
			fwrite($tmp,serialize(array($this->table,$this->table_js,$this->tablefav,$this->attributes)));
			fclose($tmp);
		}
	}
}

} # fin de d�finition

<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: notice_tpl_gen.class.php,v 1.8 2015-04-03 11:16:20 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once ($class_path . "/parse_format.class.php");
require_once ($class_path . "/notice_info.class.php");

class notice_tpl_gen {
	
	// ---------------------------------------------------------------
	//		propri�t�s de la classe
	// ---------------------------------------------------------------	
	var $id;		// MySQL id in table 'notice_tpl'
	var $name;		// nom du template
	var $comment;	// description du template
	var $code ; 	// Code du template
	
	// ---------------------------------------------------------------
	//		constructeur
	// ---------------------------------------------------------------
	function notice_tpl_gen($id=0) {			
		$this->id = $id;
		$this->getData();
	}
	
	// ---------------------------------------------------------------
	//		getData() : r�cup�ration infos 
	// ---------------------------------------------------------------
	function getData() {
		global $dbh;

		$this->name = '';			
		$this->comment = '';
		$this->code =array();
		if($this->id) {
			$requete = "SELECT * FROM notice_tpl WHERE notpl_id='".$this->id."' LIMIT 1 ";
			$result = @pmb_mysql_query($requete, $dbh);
			if(pmb_mysql_num_rows($result)) {
				$temp = pmb_mysql_fetch_object($result);				
				$this->name	= $temp->notpl_name;
				$this->comment	= $temp->notpl_comment	;
				// r�cup code		
				$requete = "SELECT * FROM notice_tplcode  WHERE num_notpl='".$this->id."' ";
				$result_code = @pmb_mysql_query($requete, $dbh);
				if(pmb_mysql_num_rows($result_code)) {
					while(($temp_code= pmb_mysql_fetch_object($result_code))) {
						$this->code[$temp_code->notplcode_localisation][$temp_code->notplcode_niveau_biblio] [$temp_code->notplcode_typdoc]=$temp_code->nottplcode_code;	
					}
				}			
			} else {
				// pas trouv� avec cette cl�
				$this->id = 0;								
			}
		}
	}
	
	function build_notice($id_notice,$location=0,$in_relation=false){
		global $dbh,$parser_environnement;
		
		$parser_environnement['id_template'] = $this->id;
		$parser=new parse_format('notice_tpl.inc.php',$in_relation);			
		
		$requete = "SELECT typdoc, niveau_biblio FROM notices WHERE notice_id='".$id_notice."' LIMIT 1 ";
		$result = @pmb_mysql_query($requete, $dbh);
		if(pmb_mysql_num_rows($result)) {
			$temp = pmb_mysql_fetch_object($result);				
			$typdoc	= $temp->typdoc;			
			$niveau_biblio	= $temp->niveau_biblio;				
			//$niveau_hierar	= $temp->niveau_hierar;		
		} else return "";
		
		// Recherche du code � appliquer (du particulier au g�n�ral)
		if($this->code[$location][$niveau_biblio][$typdoc]) {
			$code=$this->code[$location][$niveau_biblio][$typdoc];
		} elseif ($this->code[$location][$niveau_biblio][0]) {
			$code=$this->code[$location][$niveau_biblio][0];
			
		} elseif ($this->code[0][$niveau_biblio][$typdoc]) {
			$code=$this->code[0][$niveau_biblio][$typdoc];
		} elseif ($this->code[0][$niveau_biblio][0]) {
			$code=$this->code[0][$niveau_biblio][0];
			
		} elseif ($this->code[0][0][$typdoc]) {
			$code=$this->code[0][0][$typdoc];
		} elseif ($this->code[0][0][0]) {
			$code=$this->code[0][0][0];
		} else return "";
		
		$temp = pmb_mysql_fetch_object($result);							
		$parser->cmd = $code;
		$parser_environnement['id_notice']=$id_notice;
		
		return $parser->exec_cmd();		
	}
	
	static function gen_tpl_select($select_name="notice_tpl", $selected_id=0, $onchange="",$no_affempty=0,$no_aff_defaut=0) {		
		global $msg,$dbh;
		// 
		$requete = "SELECT notpl_id, if(notpl_comment!='',concat(notpl_name,'. ',notpl_comment),notpl_name) as nom FROM notice_tpl ORDER BY notpl_name ";
		$result = pmb_mysql_query($requete, $dbh);
		if(!pmb_mysql_num_rows($result) && !$no_affempty) return '';	
		if(!$no_aff_defaut)
			return gen_liste ($requete, "notpl_id", "nom", $select_name, $onchange, $selected_id, 0, $msg["notice_tpl_list_default"], 0,$msg["notice_tpl_list_default"], 0) ;
		else
			return gen_liste ($requete, "notpl_id", "nom", $select_name, $onchange, $selected_id, 0, '', 0,'', 0) ;
				
		
	}
} // fin class 



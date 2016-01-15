<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: aut_link.class.php,v 1.16 2015-11-19 15:59:08 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");
// gestion des liens entre autorit�s

require_once("$class_path/marc_table.class.php");
require_once("$class_path/author.class.php");
require_once("$class_path/editor.class.php");
require_once("$class_path/collection.class.php");
require_once("$class_path/subcollection.class.php");
require_once("$class_path/indexint.class.php");
require_once("$class_path/serie.class.php");
require_once("$class_path/category.class.php");
require_once("$class_path/titre_uniforme.class.php");
require_once("$class_path/authperso.class.php");
require_once("$class_path/indexation_authority.class.php");
require_once($include_path."/templates/aut_link.tpl.php");

define('AUT_TABLE_AUTHORS',1);
define('AUT_TABLE_CATEG',2);
define('AUT_TABLE_PUBLISHERS',3);
define('AUT_TABLE_COLLECTIONS',4);
define('AUT_TABLE_SUB_COLLECTIONS',5);
define('AUT_TABLE_SERIES',6);
define('AUT_TABLE_TITRES_UNIFORMES',7);
define('AUT_TABLE_INDEXINT',8);
define('AUT_TABLE_AUTHPERSO',9);
define('AUT_TABLE_CONCEPT',10);
// authperso >1000

$aut_table_name_list=array(
	AUT_TABLE_AUTHORS => 'authors',
	AUT_TABLE_CATEG => 'categ',
	AUT_TABLE_PUBLISHERS=> 'publishers',
	AUT_TABLE_COLLECTIONS => 'collection',
	AUT_TABLE_SUB_COLLECTIONS => 'sub_collections',
	AUT_TABLE_SERIES => 'series',
	AUT_TABLE_TITRES_UNIFORMES => 'titres_uniformes',
	AUT_TABLE_INDEXINT => 'indexint',
	AUT_TABLE_CONCEPT => 'concept'
); 

// d�finition de la classe de gestion des liens entre autorit�s
class aut_link {

	function aut_link($aut_table,$id) {
		$this->aut_table = $aut_table;
		$this->id = $id;
		$this->getdata();
	}	

	function getdata() {
		global $dbh,$msg;
		global $aut_table_name_list;
		global $pmb_opac_url;
		
		$this->aut_table_name = $aut_table_name_list[$this->aut_table];
		$this->aut_list=array();		
			
		$rqt="select * from aut_link where (aut_link_from='".$this->aut_table."'	and aut_link_from_num='".$this->id."' )
		or ( aut_link_to='".$this->aut_table."' and aut_link_to_num='".$this->id."' and aut_link_reciproc=1 )
		order by aut_link_type ";				
		$aut_res=pmb_mysql_query($rqt, $dbh);
		$i=0;
		while($row = pmb_mysql_fetch_object($aut_res)){
			$i++;
			$this->aut_list[$i]["to"]=$row->aut_link_to;
			$this->aut_list[$i]["to_num"]=$row->aut_link_to_num;				
			$this->aut_list[$i]["type"]=$row->aut_link_type;						
			$this->aut_list[$i]["reciproc"]=$row->aut_link_reciproc;					
			$this->aut_list[$i]["comment"]=$row->aut_link_comment;	
						
			if(($this->aut_table==$row->aut_link_to ) and ($this->id == $row->aut_link_to_num)) {
				$this->aut_list[$i]["flag_reciproc"]=1;							
				$this->aut_list[$i]["to"]=$row->aut_link_from;
				$this->aut_list[$i]["to_num"]=$row->aut_link_from_num;				
			}	
			else $this->aut_list[$i]["flag_reciproc"]=0;
			
			switch($this->aut_list[$i]["to"]){
				case AUT_TABLE_AUTHORS :
					$auteur = new auteur($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$auteur->isbd_entry; 
					$this->aut_list[$i]["libelle"]="[".$msg[133]."] ".$auteur->isbd_entry; 
				break;
				case AUT_TABLE_CATEG :
					$categ = new category($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$categ->libelle;
					$this->aut_list[$i]["libelle"]="[".$msg[134]."] ".$categ->libelle;		
				break;
				case AUT_TABLE_PUBLISHERS :					
					$ed = new editeur($this->aut_list[$i]["to_num"]) ;
					$this->aut_list[$i]["isbd_entry"]=$ed->isbd_entry;	
					$this->aut_list[$i]["libelle"]="[".$msg[135]."] ".$ed->isbd_entry;			
				break;
				case AUT_TABLE_COLLECTIONS :
					$subcollection = new collection($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$subcollection->isbd_entry;
					$this->aut_list[$i]["libelle"]="[".$msg[136]."] ".$subcollection->isbd_entry;
				break;
				case AUT_TABLE_SUB_COLLECTIONS :
					$collection = new subcollection($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$collection->isbd_entry;
					$this->aut_list[$i]["libelle"]="[".$msg[137]."] ".$collection->isbd_entry;
				break;
				case AUT_TABLE_SERIES :
					$serie = new serie($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$serie->name;
					$this->aut_list[$i]["libelle"]="[".$msg[333]."] ".$serie->name;
				break;
				case AUT_TABLE_TITRES_UNIFORMES :
					$tu = new titre_uniforme($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$tu->name;	
					$this->aut_list[$i]["libelle"]="[".$msg["aut_menu_titre_uniforme"]."] ".$tu->name;					
				break;
				case AUT_TABLE_INDEXINT :
					$indexint = new indexint($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$indexint->display;
					$this->aut_list[$i]["libelle"]="[".$msg["indexint_menu"]."] ".$indexint->display;				
				break;
				case AUT_TABLE_CONCEPT :	
					$concept= new concept($this->aut_list[$i]["to_num"]);
					$this->aut_list[$i]["isbd_entry"]=$concept->get_display_label();
					$this->aut_list[$i]["libelle"]="[".$msg["concept_menu"]."] ".$concept->get_display_label();	
				break;
				default:
					if($this->aut_list[$i]["to"]>1000){
						// authperso
						$authperso = new authperso($this->aut_list[$i]["to"]-1000);
						$isbd=$authperso->get_isbd($this->aut_list[$i]["to_num"]);
						$this->aut_list[$i]["isbd_entry"]=$isbd;
						$this->aut_list[$i]["libelle"]="[".$authperso->info['name']."] ".$isbd;
						$this->aut_list[$i]["url_to_gestion"]="./autorites.php?categ=authperso&sub=authperso_form&id_authperso=&id=".$this->aut_list[$i]["to_num"];
						$this->aut_list[$i]["url_to_opac"]=$pmb_opac_url."index.php?lvl=authperso_see&id=".$this->aut_list[$i]["to_num"];
					}
				
				break;
			}
			if($this->aut_list[$i]["flag_reciproc"]){
				$type_relation=new marc_select("relationtype_autup","f_aut_link_type$i", $this->aut_list[$i]["type"]);
			}else {
				$type_relation=new marc_select("relationtype_aut","f_aut_link_type$i", $this->aut_list[$i]["type"]);
			}
			$this->aut_list[$i]["relation_libelle"]=$type_relation->libelle;
		}
	}

	function get_form($caller="categ_form") {
		global $msg,$add_aut_link,$aut_link0,$aut_link1,$form_aut_link;
		global $thesaurus_concepts_active,$charset;
		
		$form=$add_aut_link;
		$js_aut_link_table_list="
		var aut_link_table_select=Array();
		aut_link_table_select[".AUT_TABLE_AUTHORS."]='./select.php?what=auteur&caller=$caller&dyn=2&param1=';		
		aut_link_table_select[".AUT_TABLE_CATEG."]='./select.php?what=categorie&caller=$caller&dyn=2&parent=1&p1=';
		aut_link_table_select[".AUT_TABLE_PUBLISHERS."]='./select.php?what=editeur&caller=$caller&dyn=2&p1=';
		aut_link_table_select[".AUT_TABLE_COLLECTIONS."]='./select.php?what=collection&caller=$caller&dyn=2&p1=';
		aut_link_table_select[".AUT_TABLE_SUB_COLLECTIONS."]='./select.php?what=subcollection&caller=$caller&dyn=2&p1=';
		aut_link_table_select[".AUT_TABLE_SERIES."]='./select.php?what=serie&caller=$caller&dyn=2&param1=';
		aut_link_table_select[".AUT_TABLE_TITRES_UNIFORMES."]='./select.php?what=titre_uniforme&caller=$caller&dyn=2&param1=';
		aut_link_table_select[".AUT_TABLE_INDEXINT."]='./select.php?what=indexint&caller=$caller&dyn=2&param1=';
		aut_link_table_select[".AUT_TABLE_CONCEPT."]='./select.php?what=ontology&caller=$caller&objs=$objs&element=concept&dyn=2&param1=';
		";
		
		if($thesaurus_concepts_active) $concept_sel="<option value='".AUT_TABLE_CONCEPT."'>".$msg["ontology_skos_menu"]."</option>";
		$aut_table_list="
		<select id='f_aut_link_table_list' name='f_aut_link_table_list'>
			<option value='".AUT_TABLE_AUTHORS."' selected='selected'>".$msg[133]."</option>
			<option value='".AUT_TABLE_CATEG."'>".$msg[134]."</option>
			<option value='".AUT_TABLE_PUBLISHERS."'>".$msg[135]."</option>
			<option value='".AUT_TABLE_COLLECTIONS."'>".$msg[136]."</option>
			<option value='".AUT_TABLE_SUB_COLLECTIONS."'>".$msg[137]."</option>
			<option value='".AUT_TABLE_SERIES."'>".$msg[333]."</option>
			<option value='".AUT_TABLE_TITRES_UNIFORMES."'>".$msg["aut_menu_titre_uniforme"]."</option>
			<option value='".AUT_TABLE_INDEXINT."'>".$msg["indexint_menu"]."</option>
			$concept_sel
			!!autpersos!!
		</select>";
		$authpersos = new authpersos();
		$info=$authpersos->get_data();
		$tpl_list="";
		foreach($info as $elt){
			$tpl_elt="<option value='!!id_authperso!!'>!!name!!</option>";
			$tpl_elt=str_replace('!!name!!',$elt['name'], $tpl_elt);
			$tpl_elt=str_replace('!!id_authperso!!',$elt['id'] + 1000, $tpl_elt);
			$js_aut_link_table_list.="aut_link_table_select[".($elt['id'] + 1000)."]='./select.php?what=authperso&authperso_id=".$elt['id']."&caller=$caller&dyn=2&param1=';";
			$tpl_list.=$tpl_elt;
		}		
		$aut_table_list=str_replace('!!autpersos!!',$tpl_list, $aut_table_list);
		
		$i=0;
		if(!count($this->aut_list)){		
			// pas d'enregistrement	
			$form.=$aut_link0;
			
			$liste_type_relation=new marc_select("relationtype_aut","f_aut_link_type$i", $aut["type"],"","","",array(array('name'=>'data-form-name','value'=>'f_aut_link_type')));	
			$form=str_replace("!!aut_link_type!!",$liste_type_relation->display,$form);				
			$form=str_replace("!!aut_link_reciproc!!","unchecked='unchecked'",$form);	
			$form=str_replace("!!aut_link!!",$i,$form);	
			$form=str_replace("!!aut_link_libelle!!","",$form);
			$form=str_replace("!!aut_link_table!!","",$form);
			$form=str_replace("!!aut_link_id!!","",$form);	
			$form=str_replace("!!aut_link_comment!!","",$form);
			$i++;
		} else{			
			foreach ($this->aut_list as $aut) {	
				// Construction de chaque ligne du formulaire	
				if($i) $form_suivant=$aut_link1; else $form_suivant=$aut_link0;		
				if($aut["flag_reciproc"]){
					$liste_type_relation=new marc_select("relationtype_autup","f_aut_link_type$i", $aut["type"],"","","",array(array('name'=>'data-form-name','value'=>'f_aut_link_type')));
				}else {
					$liste_type_relation=new marc_select("relationtype_aut","f_aut_link_type$i", $aut["type"],"","","",array(array('name'=>'data-form-name','value'=>'f_aut_link_type')));
				}
				$form_suivant=str_replace("!!aut_link_type!!",$liste_type_relation->display,$form_suivant);
				if($aut["reciproc"]) $check="checked='checked'"; else $check="";
				$form_suivant=str_replace("!!aut_link_reciproc!!",$check,$form_suivant);	
				$form_suivant=str_replace("!!aut_link!!",$i,$form_suivant);
				$form_suivant=str_replace("!!aut_link_libelle!!",htmlentities($aut["libelle"],ENT_QUOTES, $charset,false),$form_suivant);
				$form_suivant=str_replace("!!aut_link_table!!",$aut["to"],$form_suivant);
				$form_suivant=str_replace("!!aut_link_id!!",$aut["to_num"],$form_suivant);
				$form_suivant=str_replace("!!aut_link_comment!!",$aut["comment"],$form_suivant);
				$form.=$form_suivant;		
				$i++;		
			}				
		}
		$form=str_replace("!!max_aut_link!!",$i,$form);
		$form=str_replace("!!js_aut_link_table_list!!",$js_aut_link_table_list,$form);
		$form=str_replace("!!aut_table_list!!",$aut_table_list,$form);
		$form = str_replace("!!aut_link_contens!!", $form , $form_aut_link);
		return $form;
	}
	
	function save_form() {
		global $dbh;
		//max_aut_link
		//f_aut_link_typexxx
		//f_aut_link_tablexxx
		//f_aut_link_idxxx
		global $max_aut_link;
		global $include_path;
		if(!$this->aut_table && !$this->id) return;
		$this->delete_link();
		for($i=0;$i<$max_aut_link;$i++){
			eval("global \$f_aut_link_table".$i.";\$f_aut_link_table= \$f_aut_link_table$i;"); 
			eval("global \$f_aut_link_id".$i.";\$f_aut_link_id= \$f_aut_link_id$i;"); 
			eval("global \$f_aut_link_type".$i.";\$f_aut_link_type= \$f_aut_link_type$i;"); 
			eval("global \$f_aut_link_reciproc".$i.";\$f_aut_link_reciproc= \$f_aut_link_reciproc$i;"); 
			eval("global \$f_aut_link_comment".$i.";\$f_aut_link_comment= \$f_aut_link_comment$i;");
			
			// Les selecteurs de concept retourne l'uri et non id 
			if($f_aut_link_table==AUT_TABLE_CONCEPT && !is_numeric($f_aut_link_id)){ 
				$f_aut_link_id=onto_common_uri::get_id($f_aut_link_id);				
			}
			if($f_aut_link_reciproc)$f_aut_link_reciproc=1;
			if($f_aut_link_id && $f_aut_link_table && $f_aut_link_type && !(($this->aut_table == $f_aut_link_table) && ($this->id == $f_aut_link_id))) {
	 			$requete="INSERT INTO aut_link (aut_link_from, aut_link_from_num, aut_link_to,aut_link_to_num , aut_link_type, aut_link_reciproc, aut_link_comment) 
	 			VALUES ('".$this->aut_table."', '".$this->id."','".$f_aut_link_table."', '".$f_aut_link_id."', '".$f_aut_link_type."', '".$f_aut_link_reciproc."','".$f_aut_link_comment."')";
				pmb_mysql_query($requete);		
			}	
			if($f_aut_link_reciproc){
				$indexation_authority = new indexation_authority($include_path."/indexation/authorities/titres_uniformes/champs_base.xml", "authorities", AUT_TABLE_TITRES_UNIFORMES);
				$indexation_authority->maj($f_aut_link_id,'aut_link');
			}
		}
	}
			
	// delete tous les liens (from vers to) de cette autorit� 
	function delete_link() {
		global $dbh;
		if(!$this->aut_table && !$this->id) return;
		$requete="DELETE FROM aut_link WHERE aut_link_from='".$this->aut_table."' and aut_link_from_num='".$this->id."' ";
		pmb_mysql_query($requete, $dbh);
		$requete="DELETE FROM aut_link WHERE aut_link_to='".$this->aut_table."' and aut_link_to_num='".$this->id."' and aut_link_reciproc=1 ";
		pmb_mysql_query($requete, $dbh);
	}		
	
	// delete tous les liens (from et to) de cette autorit� 
	function delete() {
		global $dbh;
		if(!$this->aut_table && !$this->id) return;
		$requete="DELETE FROM aut_link WHERE aut_link_from='".$this->aut_table."' and aut_link_from_num='".$this->id."' ";
		pmb_mysql_query($requete, $dbh);
		$requete="DELETE FROM aut_link WHERE aut_link_to='".$this->aut_table."' and aut_link_to_num='".$this->id."' ";
		pmb_mysql_query($requete, $dbh);
	}	
	
	// copie les liens from et to par une autre autorit�
	function add_link_to($copy_table,$copy_num) {
		global $dbh;
		if(!$this->aut_table && !$this->id && !$copy_link_to && !$copy_link_to_num) return;
		
		foreach ($this->aut_list as $aut) {		
			if($aut["flag_reciproc"]){
		 		$requete="INSERT INTO aut_link (aut_link_from, aut_link_from_num, aut_link_to,aut_link_to_num , aut_link_type, aut_link_reciproc, aut_link_comment) 
		 		VALUES ('".$aut["to"]."', '".$aut["to_num"]."','".$copy_table."', '".$copy_num."', '".$aut["type"]."', '".$aut["reciproc"]."','".$aut["comment"]."')";					
			}else {
		 		$requete="INSERT INTO aut_link (aut_link_from, aut_link_from_num, aut_link_to,aut_link_to_num , aut_link_type, aut_link_reciproc, aut_link_comment) 
		 		VALUES ('".$copy_table."', '".$copy_num."','".$aut["to"]."', '".$aut["to_num"]."', '".$aut["type"]."', '".$aut["reciproc"]."','".$aut["comment"]."')";							
			}
			@pmb_mysql_query($requete);
		}		
	}
	
	function get_display($caller="categ_form") {
		global $msg;
		if(!count($this->aut_list)) return"";
	
		$aut_see_link = "./autorites.php?categ=see&sub=!!type!!&id=!!to_num!!";		

		$marc_table=new marc_list("relationtype_aut");
		$liste_type_relation = $marc_table->table;
		$marc_tableup=new marc_list("relationtype_autup");
		$liste_type_relationup = $marc_tableup->table;
	
		$aff="<ul>";
		foreach ($this->aut_list as $aut) {
			switch($aut["to"]){
				case "1" :
					$type = "author";
					break;
				case "2" :
					$type = "category";
					break;
				case "3" :
					$type = "publisher";
					break;
				case "4" :
					$type = "collection";
					break;
				case "5" :
					$type = "subcollection";
					break;
				case "6" :
					$type = "serie";
					break;
				case "7" :
					$type = "titre_uniforme";
					break;
				case "8" :
					$type = "indexint";
					break;
				case "9" :
					$type = "authperso";
					break;
				case "10" :
					$type = "concept";
					break;
			}
			$aff.="<li>";
			if($aut["reciproc"])	$aff.=$liste_type_relationup[$aut["type"]]." : ";
			else	$aff.=$liste_type_relation[$aut["type"]]." : ";
			$link =str_replace("!!to_num!!",$aut["to_num"],$aut_see_link);
			$link = str_replace("!!type!!",$type,$link);
			$aff.=" <a href=".$link.">".$aut["libelle"]."</a>";
			if($aut["comment"]) {
				$aff.=" (".$aut["comment"].")";
			}
			$aff.="</li>";
		}
		$aff.="</ul>";
		return $aff;
	}
// fin class
}
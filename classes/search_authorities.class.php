<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: search_authorities.class.php,v 1.2 2015-12-04 16:29:07 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

//Classe de gestion des recherches avancees des autorités

require_once($class_path."/search.class.php");

class search_authorities extends search {
	
	public function filter_searchtable_from_accessrights($table) {
		global $dbh;
		global $gestion_acces_active,$gestion_acces_user_authority;
		global $PMBUserId;
		
		if($gestion_acces_active && $gestion_acces_user_authority){
			//En vue de droits d'accès
		}
	}
	
	protected function sort_results($table) {
		global $nb_per_page_search;
		global $page;
		 
		$start_page=$nb_per_page_search*$page;
		 
		return $table;
	}
	
	protected function get_display_nb_results($nb_results) {
		global $msg;
		 
		return " => ".$nb_results." ".$msg["search_extended_authorities_found"]."<br />\n";
	}
	
	protected function show_objects_results($table, $has_sort) {
		global $dbh;
		global $search;
		global $nb_per_page_search;
		global $page;
		
		$start_page=$nb_per_page_search*$page;
		$nb = 0;
		
		$query = "select $table.*,authorities.num_object,authorities.type_object from ".$table.",authorities where authorities.id_authority=$table.id_authority";
		if(count($search) > 1 && !$has_sort) {
			//Tri à appliquer par défaut
		}		
		$query .= " limit ".$start_page.",".$nb_per_page_search;
		
		$result=pmb_mysql_query($query, $dbh);
		$objects_ids = array();
		while ($row=pmb_mysql_fetch_object($result)) {
			$objects_ids[] = $row->id_authority;
		}
		if(count($objects_ids)) {
			$elements_authorities_list_ui = new elements_authorities_list_ui($objects_ids, count($objects_ids), 1);
			$elements = $elements_authorities_list_ui->get_elements_list();
			print $elements[0];
		}
	}
	
	protected function get_display_actions() {
		return "";
	}
	
	protected function get_display_icons($nb_results, $recherche_externe = false) {
		return "";
	}
	
}
?>
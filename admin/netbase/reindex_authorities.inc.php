<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: reindex_authorities.inc.php,v 1.2 2015-11-13 08:36:09 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/indexation_authority.class.php");
require_once($class_path."/indexation_authperso.class.php");

// la taille d'un paquet de notices
$lot = REINDEX_PAQUET_SIZE; // defini dans ./params.inc.php

// taille de la jauge pour affichage
$jauge_size = GAUGE_SIZE;
$jauge_size .= "px";

// initialisation de la borne de départ
if (!isset($start)) {
	$start=0;
	//remise a zero de la table au début
	pmb_mysql_query("TRUNCATE authorities_words_global_index",$dbh);
	pmb_mysql_query("ALTER TABLE authorities_words_global_index DISABLE KEYS",$dbh);

	pmb_mysql_query("TRUNCATE authorities_fields_global_index",$dbh);
	pmb_mysql_query("ALTER TABLE authorities_fields_global_index DISABLE KEYS",$dbh);
}

$v_state=urldecode($v_state);

// on commence par :
if (!isset($index_quoi)) $index_quoi='AUTHORS';

switch ($index_quoi) {	
	case 'AUTHORS':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM authors", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_authors"], ENT_QUOTES, $charset)."</h2>";
		
		$query = pmb_mysql_query("SELECT author_id as id from authors LIMIT $start, $lot", $dbh);
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/authors/champs_base.xml", "authorities", AUT_TABLE_AUTHORS);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"AUTHORS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_authors"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_authors"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"PUBLISHERS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'PUBLISHERS':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM publishers", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_publishers"], ENT_QUOTES, $charset)."</h2>";
		
		$query = pmb_mysql_query("SELECT ed_id as id from publishers LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/publishers/champs_base.xml", "authorities", AUT_TABLE_PUBLISHERS);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"PUBLISHERS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_publishers"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_publishers"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"CATEGORIES\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'CATEGORIES':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(distinct num_noeud) FROM categories", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_categories"], ENT_QUOTES, $charset)."</h2>";
		
		$req = "select distinct num_noeud as id from categories limit $start, $lot ";
		$query = pmb_mysql_query($req, $dbh);
		 
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/categories/champs_base.xml", "authorities", AUT_TABLE_CATEG);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"CATEGORIES\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_categories"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_categories"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"COLLECTIONS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'COLLECTIONS':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM collections", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_collections"], ENT_QUOTES, $charset)."</h2>";
		
		$query = pmb_mysql_query("SELECT collection_id as id from collections LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/collections/champs_base.xml", "authorities", AUT_TABLE_COLLECTIONS);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"COLLECTIONS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_collections"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_collections"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"SUBCOLLECTIONS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'SUBCOLLECTIONS':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM sub_collections", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_sub_collections"], ENT_QUOTES, $charset)."</h2>";
		
		$query = pmb_mysql_query("SELECT sub_coll_id as id from sub_collections LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/subcollections/champs_base.xml", "authorities", AUT_TABLE_SUB_COLLECTIONS);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"SUBCOLLECTIONS\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_sub_collections"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_sub_collections"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"SERIES\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'SERIES':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM series", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_series"], ENT_QUOTES, $charset)."</h2>";
		
		$query = pmb_mysql_query("SELECT serie_id as id from series LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/series/champs_base.xml", "authorities", AUT_TABLE_SERIES);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"SERIES\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_series"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_series"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"DEWEY\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'DEWEY':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM indexint", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
		
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_indexint"], ENT_QUOTES, $charset)."</h2>";
		
		$query = pmb_mysql_query("SELECT indexint_id as id from indexint LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
		
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
			
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
			
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
			
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
			
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/indexint/champs_base.xml", "authorities", AUT_TABLE_INDEXINT);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value=\"$next\">
				<input type='hidden' name='count' value=\"$count\">
				<input type='hidden' name='index_quoi' value=\"DEWEY\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_indexint"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_indexint"], ENT_QUOTES, $charset);
			print "
				<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
				<input type='hidden' name='spec' value=\"$spec\">
				<input type='hidden' name='start' value='0'>
				<input type='hidden' name='count' value='0'>
				<input type='hidden' name='index_quoi' value=\"TITRES_UNIFORMES\">
				</form>
				<script type=\"text/javascript\"><!-- 
					setTimeout(\"document.forms['current_state'].submit()\",1000); 
					-->
				</script>";	
		}
		break ;
	
	case 'TITRES_UNIFORMES':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM titres_uniformes", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
	
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_titres_uniformes"], ENT_QUOTES, $charset)."</h2>";
	
		$query = pmb_mysql_query("SELECT tu_id as id from titres_uniformes LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
	
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
				
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
				
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
				
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
				
			$indexation_authority = new indexation_authority($include_path."/indexation/authorities/titres_uniformes/champs_base.xml", "authorities", AUT_TABLE_TITRES_UNIFORMES);
			while($row = pmb_mysql_fetch_object($query)) {
				$indexation_authority->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
			<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
			<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			<input type='hidden' name='spec' value=\"$spec\">
			<input type='hidden' name='start' value=\"$next\">
			<input type='hidden' name='count' value=\"$count\">
			<input type='hidden' name='index_quoi' value=\"TITRES_UNIFORMES\">
			</form>
			<script type=\"text/javascript\"><!--
			setTimeout(\"document.forms['current_state'].submit()\",1000);
			-->
			</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_titres_uniformes"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_titres_uniformes"], ENT_QUOTES, $charset);
			print "
			<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
			<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			<input type='hidden' name='spec' value=\"$spec\">
			<input type='hidden' name='start' value='0'>
			<input type='hidden' name='count' value='0'>
			<input type='hidden' name='index_quoi' value=\"AUTHPERSO\">
			</form>
			<script type=\"text/javascript\"><!--
			setTimeout(\"document.forms['current_state'].submit()\",1000);
			-->
			</script>";
		}
		break ;
		
	case 'AUTHPERSO':
		if (!$count) {
			$elts = pmb_mysql_query("SELECT count(1) FROM authperso_authorities", $dbh);
			$count = pmb_mysql_result($elts, 0, 0);
		}
	
		print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_reindex_authperso"], ENT_QUOTES, $charset)."</h2>";
	
		$query = pmb_mysql_query("SELECT id_authperso_authority as id, authperso_authority_authperso_num from authperso_authorities ORDER BY authperso_authority_authperso_num LIMIT $start, $lot");
		if (pmb_mysql_num_rows($query)) {
	
			// définition de l'état de la jauge
			$state = floor($start / ($count / $jauge_size));
	
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$jauge_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";
	
			// calcul pourcentage avancement
			$percent = floor(($start/$count)*100);
	
			// affichage du % d'avancement et de l'état
			print "<div align='center'>$percent%</div>";
	
			$id_authperso = 0;
			while($row = pmb_mysql_fetch_object($query)) {
				if(!$id_authperso || ($id_authperso != $row->authperso_authority_authperso_num)) {
					$indexation_authperso = new indexation_authperso($include_path."/indexation/authorities/authperso/champs_base.xml", "authorities", AUT_TABLE_AUTHPERSO, $row->authperso_authority_authperso_num);
					$id_authperso = $row->authperso_authority_authperso_num;
				}				
				$indexation_authperso->maj($row->id);
			}
			pmb_mysql_free_result($query);
			$next = $start + $lot;
			print "
			<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
			<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			<input type='hidden' name='spec' value=\"$spec\">
			<input type='hidden' name='start' value=\"$next\">
			<input type='hidden' name='count' value=\"$count\">
			<input type='hidden' name='index_quoi' value=\"AUTHPERSO\">
			</form>
			<script type=\"text/javascript\"><!--
			setTimeout(\"document.forms['current_state'].submit()\",1000);
			-->
			</script>";
		} else {
			// mise à jour de l'affichage de la jauge
			print "<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>";
			print "<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>";
			print "<div align='center'>100%</div>";
			$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_authperso"], ENT_QUOTES, $charset)." $count ".htmlentities($msg["nettoyage_res_reindex_authperso"], ENT_QUOTES, $charset);
			print "
			<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
			<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			<input type='hidden' name='spec' value=\"$spec\">
			<input type='hidden' name='start' value='0'>
			<input type='hidden' name='count' value='0'>
			<input type='hidden' name='index_quoi' value=\"FINI\">
			</form>
			<script type=\"text/javascript\"><!--
			setTimeout(\"document.forms['current_state'].submit()\",1000);
			-->
			</script>";
		}
		break ;
			
	case 'FINI':
		$spec = $spec - INDEX_AUTHORITIES;
		pmb_mysql_query("ALTER TABLE authorities_words_global_index ENABLE KEYS",$dbh);
		pmb_mysql_query("ALTER TABLE authorities_fields_global_index ENABLE KEYS",$dbh);
		$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_reindex_fini"], ENT_QUOTES, $charset);
		print "
			<form class='form-$current_module' name='process_state' action='./clean.php?spec=$spec&start=0' method='post'>
				<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
			</form>
			<script type=\"text/javascript\"><!--
				setTimeout(\"document.forms['process_state'].submit()\",1000);
				-->
			</script>";
		break ;
}

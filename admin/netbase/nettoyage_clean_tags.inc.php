<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: nettoyage_clean_tags.inc.php,v 1.3 2015-04-03 11:16:18 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/notice.class.php");

// la taille d'un paquet de notices
$lot = NOEXPL_PAQUET_SIZE*10; // defini dans ./params.inc.php
// taille de la jauge pour affichage
$jauge_size = GAUGE_SIZE;

// initialisation de la borne de d�part
if(!isset($start)) $start=0;

$v_state=urldecode($v_state);

if(!$count) {
	$notices = pmb_mysql_query("SELECT count(1) FROM notices", $dbh);
	$count = pmb_mysql_result($notices, 0, 0);
}

print "<br /><br /><h2 align='center'>".htmlentities($msg["nettoyage_clean_tags"], ENT_QUOTES, $charset)."</h2>";

$query = pmb_mysql_query("SELECT notice_id FROM notices LIMIT $start, $lot");
if(pmb_mysql_num_rows($query)) {

    // d�finition de l'�tat de la jauge
    $state = floor($start / ($count / $jauge_size));

    // mise � jour de l'affichage de la jauge
    print "<table border='0' align='center' width='$jauge_size' cellpadding='0' border='0'><tr><td class='jauge'>";
    print "<img src='../../images/jauge.png' width='$state' height='16'></td></tr></table>";

    // calcul pourcentage avancement
    $percent = floor(($start/$count)*100);

    // affichage du % d'avancement et de l'�tat
    print "<div align='center'>$percent%</div>";
   	while ($row = pmb_mysql_fetch_row($query) )  { 		
		notice::majNotices_clean_tags($row[0]);
   	}
   	pmb_mysql_free_result($query);
	$next = $start + $lot;
 	print "
	<form class='form-$current_module' name='current_state' action='./clean.php' method='post'>
		<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
		<input type='hidden' name='spec' value=\"$spec\">
		<input type='hidden' name='start' value=\"$next\">
		<input type='hidden' name='count' value=\"$count\">
	</form>
	<script type=\"text/javascript\">
	<!--
		document.forms['current_state'].submit();
	-->
	</script>";
} else {
	$spec = $spec - NETTOYAGE_CLEAN_TAGS;
	$v_state .= "<br /><img src=../../images/d.gif hspace=3>".htmlentities($msg["nettoyage_clean_tags_status"], ENT_QUOTES, $charset);
	$v_state .= $count." ".htmlentities($msg["nettoyage_clean_tags_status_end"], ENT_QUOTES, $charset);
	$opt = pmb_mysql_query('OPTIMIZE TABLE notices');
	// mise � jour de l'affichage de la jauge
	print "
	<table border='0' align='center' width='$table_size' cellpadding='0'><tr><td class='jauge'>
	<img src='../../images/jauge.png' width='$jauge_size' height='16'></td></tr></table>
	<div align='center'>100%</div>";

	print "
	<form class='form-$current_module' name='process_state' action='./clean.php' method='post'>
		<input type='hidden' name='v_state' value=\"".urlencode($v_state)."\">
		<input type='hidden' name='spec' value=\"$spec\">
	</form>
	<script type=\"text/javascript\">
	<!--
		document.forms['process_state'].submit();
	-->
	</script>";	
}	
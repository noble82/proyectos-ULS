<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: workflow.inc.php,v 1.1 2016-01-07 07:48:53 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//dépendances
require_once($class_path.'/scan_request/scan_request_status.class.php');

$scan_request_status=new scan_request_status();

switch($action) {
	case 'save':
		$scan_request_status->save_workflow();
		print $scan_request_status->get_form_workflow();
		break;
	default:
		print $scan_request_status->get_form_workflow();
		break;
}
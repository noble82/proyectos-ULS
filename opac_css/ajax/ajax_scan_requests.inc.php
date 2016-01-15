<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_scan_requests.inc.php,v 1.1 2016-01-13 15:41:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($base_path."/classes/scan_request/scan_request.class.php");

switch($sub){
	case 'form':
		switch ($action){
			case 'create':
				$scan_request=new scan_request();
				$scan_request_date = extraitdate($scan_request_date);
				$scan_request_deadline_date = extraitdate($scan_request_deadline_date);
				$scan_request_wish_date = extraitdate($scan_request_wish_date);
				$scan_request->get_values_from_form();
				$saved = $scan_request->save();
				if($saved) {
					print $msg['scan_request_saved'];
					print " ".str_replace('!!link!!', './empr.php?tab=scan_requests&lvl=scan_request&sub=display&id='.$scan_request->get_id(), $msg['scan_request_saved_see_link']);
				} else {
					print $msg['scan_request_cant_save'];
				}
				break;
				
		}
		break;
}
?>
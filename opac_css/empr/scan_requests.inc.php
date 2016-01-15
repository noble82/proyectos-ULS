<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_requests.inc.php,v 1.1 2016-01-13 15:41:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path.'/scan_request/scan_requests.class.php');
require_once($class_path.'/scan_request/scan_request.class.php');

switch($lvl){
	case 'scan_request':
		$scan_request = new scan_request($id);
		switch($sub) {
			case 'edit':
				switch ($from) {
					case 'caddie':
						$notices = $_SESSION['cart'];
						$scan_request->add_linked_records(array('notices' => $notices));
						break;
					case 'checked':
						$notices = $notice;
						$scan_request->add_linked_records(array('notices' => $notices));
						break;
				}
				print $scan_request->get_form();
				break;
			case 'save':
				$scan_request->get_values_from_form();
				$scan_request->save();
				print $scan_request->get_display();
				break;
			case 'cancel':
				if ($_SESSION['id_empr_session'] && $scan_request->get_status()->is_cancelable()) {
					$scan_request->delete();
					print $msg['scan_request_deleted'];
				} else {
					print $msg['scan_request_cant_delete'];
				}
				$scan_requests = new scan_requests($_SESSION['id_empr_session']);
				print $scan_requests->get_display_list();
				break;
			case 'display':
			default :
				print $scan_request->get_display();
				break;
		}
		break;
	case 'scan_requests_list':
	default :
		$scan_requests = new scan_requests($_SESSION['id_empr_session']);
		print $scan_requests->get_display_list();
	break;
}
?>
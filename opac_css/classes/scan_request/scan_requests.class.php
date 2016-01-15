<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_requests.class.php,v 1.2 2016-01-13 15:41:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/scan_request/scan_request.class.php');
require_once($include_path.'/h2o/pmb_h2o.inc.php');
include_once($include_path.'/templates/scan_request.tpl.php');

class scan_requests {
	
	/**
	 * Tableau des scan_requests de la liste
	 * @var scan_request
	 */
	protected $scan_requests;
	
	/**
	 * Identifiant de l'emprunteur
	 * @var int
	 */
	protected $empr_id;
	
	public function __construct($empr_id) {
		$this->empr_id = $empr_id*1;
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		global $dbh;
		$this->scan_requests = array();
		
		$query = 'select id_scan_request from scan_requests where scan_request_num_dest_empr = '.$this->empr_id.' order by scan_request_date';
		$result = pmb_mysql_query($query, $dbh);
		if (pmb_mysql_num_rows($result)) {
			while ($row = pmb_mysql_fetch_object($result)) {
				$this->scan_requests[] = new scan_request($row->id_scan_request);
			}
		}
	}
	
	public function get_display_list() {
		global $include_path, $scan_request_scripts;
		
		$tpl = $include_path.'/templates/scan_request/scan_requests_list.tpl.html';
		if (file_exists($include_path.'/templates/scan_request/scan_requests_list_subst.tpl.html')) {
			$tpl = $include_path.'/templates/scan_request/scan_requests_list_subst.tpl.html';
		}
		$h2o = new H2o($tpl);
		return $scan_request_scripts.$h2o->render(array('scan_requests' => $this));
	}
	
	public function get_scan_requests() {
		return $this->scan_requests;
	}
}
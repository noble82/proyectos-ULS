<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_request.class.php,v 1.2 2016-01-13 15:41:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/scan_request/scan_request_status.class.php');
require_once($class_path.'/scan_request/scan_request_priorities.class.php');
require_once($class_path.'/scan_request/scan_request_priority.class.php');
include_once($include_path.'/templates/scan_request.tpl.php');
include_once($include_path.'/notice_affichage.inc.php');
include_once($include_path.'/bulletin_affichage.inc.php');

class scan_request {
	protected $id;

	protected $title = '';

	protected $desc = '';

	protected $status = null;

	protected $priority = null;

	protected $create_date = null;

	protected $update_date = null;

	protected $date = null;

	protected $wish_date = null;

	protected $deadline_date = null;

	protected $comment = '';

	protected $elapsed_time = 0;

	protected $num_dest_empr = 0;

	protected $num_creator = 0;

	protected $type_creator = 0;

	protected $num_last_user = 0;

	protected $state = 0;
	
	protected $linked_records = array();

	protected $formatted_update_date = null;

	protected $formatted_date = null;

	protected $formatted_wish_date = null;

	protected $formatted_deadline_date = null;
	
	public function __construct($id = 0) {
		$this->id = $id*1;
		$this->fetch_data();
	}
	
	protected function fetch_data() {
		global $dbh;
		
		if ($this->id) {
			$query = 'select * from scan_requests where id_scan_request = '.$this->id;
			$result = pmb_mysql_query($query, $dbh);
			if (pmb_mysql_num_rows($result)) {
				$row = pmb_mysql_fetch_object($result);
				$this->title = $row->scan_request_title;
				$this->desc = $row->scan_request_desc;
				$this->status = new scan_request_status($row->scan_request_num_status);
				$this->priority = new scan_request_priority($row->scan_request_num_priority);
				$this->create_date = $row->scan_request_create_date;
				$this->update_date = $row->scan_request_update_date;
				$this->date = $row->scan_request_date;
				$this->wish_date = $row->scan_request_wish_date;
				$this->deadline_date = $row->scan_request_deadline_date;
				$this->comment = $row->scan_request_comment;
				$this->elapsed_time = $row->scan_request_elapsed_time;
				$this->num_dest_empr = $row->scan_request_num_dest_empr;
				$this->num_creator = $row->scan_request_num_creator;
				$this->type_creator = $row->scan_request_type_creator;
				$this->num_last_user = $row->scan_request_num_last_user;
				$this->state = $row->scan_request_state;
				$this->formatted_update_date = formatdate($this->update_date);
				$this->formatted_date = formatdate($this->date);
				$this->formatted_wish_date = formatdate($this->wish_date);
				$this->formatted_deadline_date = formatdate($this->deadline_date);
				
				$query = 'select * from scan_request_linked_records where scan_request_linked_record_num_request = '.$this->id.' order by scan_request_linked_record_order';
				$result = pmb_mysql_query($query, $dbh);
				while ($row = pmb_mysql_fetch_object($result)) {
					$this->add_linked_record($row->scan_request_linked_record_num_notice, $row->scan_request_linked_record_num_bulletin, $row->scan_request_linked_record_comment);
				}
			}
		}
	}
	
	protected function add_linked_record($notice_id, $bulletin_id, $comment = '') {
		
		if ($notice_id) {
			$display = aff_notice($notice_id, 0, 1, 0, AFF_ETA_NOTICES_REDUIT);
		} else {
			$display = bulletin_header($bulletin_id);
		}
		$this->linked_records[] = array(
				'notice_id' => $notice_id,
				'bulletin_id' => $bulletin_id,
				'display' => $display,
				'comment' => $comment
		);	
	}
	
	public function add_linked_records($objects_ids) {
		if(is_array($objects_ids) && count($objects_ids)) {
			foreach($objects_ids as $type=>$object_ids) {
				foreach ($object_ids as $object_id) {
					if($type == 'notices') {
						$this->add_linked_record($object_id, 0);
					} elseif($type == 'bulletins') {
						$this->add_linked_record(0, $object_id);
					}	
				}
			}
		}
	}
	
	public function get_values_from_form() {
		global $scan_request_title, $scan_request_desc, $scan_request_comment;
		global $scan_request_priority, $scan_request_status, $scan_request_date, $scan_request_wish_date, $scan_request_deadline_date;
		global $scan_request_linked_records_notices, $scan_request_linked_records_bulletins;
		
		$this->title = stripslashes($scan_request_title);
		$this->desc = stripslashes($scan_request_desc);
		$this->comment = stripslashes($scan_request_comment);
		$this->priority = new scan_request_priority($scan_request_priority);
		$this->status = new scan_request_status($scan_request_status);
		$this->date = $scan_request_date;
		$this->wish_date = $scan_request_wish_date;
		$this->deadline_date = $scan_request_deadline_date;
		$this->update_date = date('Y-m-d');
		$this->formatted_date = formatdate($this->date);
		$this->formatted_wish_date = formatdate($this->wish_date);
		$this->formatted_deadline_date = formatdate($this->deadline_date);
		$this->formatted_update_date = format_date($this->update_date);
		$this->num_dest_empr = $_SESSION['id_empr_session'];
		$this->linked_records = array();
		if(is_array($scan_request_linked_records_notices)) {
			foreach ($scan_request_linked_records_notices as $notice_id => $notice) {
				$this->add_linked_record($notice_id, 0, stripslashes($notice['comment'])); 
			}
		}
		if(is_array($scan_request_linked_records_bulletins)) {
			foreach ($scan_request_linked_records_bulletins as $bulletin_id => $bulletin) {
				$this->add_linked_record(0, $bulletin_id, stripslashes($bulletin['comment']));
			}
		}
	}
	
	public function save() {
		global $dbh;
		
		if($this->id) {
			$query = 'update scan_requests set ';
			$where = 'where id_scan_request='.$this->id;
		} else {
			$query = 'insert into scan_requests set ';
			$query .= 'scan_request_create_date=now(),
					scan_request_num_creator='.$this->num_dest_empr.',
					scan_request_num_dest_empr='.$this->num_dest_empr.',
					scan_request_type_creator=2,
					scan_request_num_status='.$this->status->get_id().',';
			$where = '';
		}
		$query .= 'scan_request_title="'.addslashes($this->title).'",
				scan_request_desc="'.addslashes($this->desc).'",
				scan_request_comment="'.addslashes($this->comment).'",
				scan_request_num_priority="'.$this->priority->get_id().'",
				scan_request_date="'.$this->date.'",
				scan_request_wish_date="'.$this->wish_date.'",
				scan_request_deadline_date="'.$this->deadline_date.'",
				scan_request_update_date=now()
				';
		$query .= $where;
		$result = pmb_mysql_query($query);
		
		if ($result) {
			// On sauve les documents liés
			$is_new = false;
			if (!$this->id) {
				$is_new = true;
				$this->id = pmb_mysql_insert_id($dbh);
			}
			foreach ($this->linked_records as $linked_record) {
				$result = $this->_save_linked_record($linked_record, $is_new);
				if(!$result) return false;
			} 
			return true;
		}
		return false;
	}
	
	protected function _save_linked_record($linked_record, $is_new) {
		
		if($is_new) {
			$query = 'insert into scan_request_linked_records set 
					scan_request_linked_record_num_request="'.$this->id.'",
					scan_request_linked_record_num_notice="'.$linked_record['notice_id'].'",
					scan_request_linked_record_num_bulletin="'.$linked_record['bulletin_id'].'",
					scan_request_linked_record_comment="'.addslashes($linked_record['comment']).'",
					scan_request_linked_record_order="'.$linked_record['order'].'"';
		} else {
			$query = 'update scan_request_linked_records set 
					scan_request_linked_record_comment="'.addslashes($linked_record['comment']).'",
					scan_request_linked_record_order="'.$linked_record['order'].'"
					where scan_request_linked_record_num_request="'.$this->id.'"
					and scan_request_linked_record_num_notice="'.$linked_record['notice_id'].'"
					and scan_request_linked_record_num_bulletin="'.$linked_record['bulletin_id'].'"';		
		}
		$result = pmb_mysql_query($query);
		return $result;
	}
	
	public function get_form() {
		global $charset, $msg;
		global $scan_request_form, $scan_request_form_delete_button;
		
		$display = $scan_request_form;
		
		if($this->id){
			$display = str_replace('!!form_title!!', htmlentities($msg['scan_request_edit_form'], ENT_QUOTES, $charset), $display);
		} else {
			$display = str_replace('!!form_title!!', htmlentities($msg['scan_request_create_form'], ENT_QUOTES, $charset), $display);
		}
		$delete_button = '';
		if($this->status && $this->status->is_cancelable()) {
			$delete_button = $scan_request_form_delete_button;
		}
		$display = str_replace('!!delete_button!!', $delete_button, $display);
		$display = str_replace('!!id!!', $this->id, $display);
		
		$display = str_replace('!!form_content!!', $this->get_form_content(), $display);
		
		return $display;
	}
	
	public function get_form_in_record($record_id, $record_type = 'notices') {
		global $charset;
		global $scan_request_form_in_record;
		
		$display = $scan_request_form_in_record;
		
		$display = str_replace('!!record_id!!', $record_id, $display);
		// TODO Attention bulletin/notice avec mm id
		$id_suffix = '_'.$record_id;
		$display = str_replace('!!id_suffix!!', htmlentities($id_suffix, ENT_QUOTES, $charset), $display);
		$display = str_replace('!!record_type!!', $record_type, $display);
		$display = str_replace('!!record_id!!', $record_id, $display);
		$display = str_replace('!!form_content!!', $this->get_form_content($id_suffix), $display);
		
		return $display;
	}
	
	public function get_form_content($id_suffix = '') {
		global $charset;
		global $scan_request_form_content, $opac_scan_request_status, $scan_request_linked_record;
		
		$display = $scan_request_form_content;

		$display = str_replace('!!title!!', htmlentities($this->title, ENT_QUOTES, $charset), $display);
		$display = str_replace('!!desc!!', htmlentities($this->desc, ENT_QUOTES, $charset), $display);
		$display = str_replace('!!comment!!', htmlentities($this->comment, ENT_QUOTES, $charset), $display);
		
		$selected_priority = 0;
		if ($this->priority) {
			$selected_priority = $this->priority->get_id();
		}
		$scan_request_priorities = new scan_request_priorities();
		$display = str_replace('!!priority!!', $scan_request_priorities->get_selector_options($selected_priority), $display);
		$display = str_replace('!!date!!', ($this->date ? substr($this->date,0,10) : date('Y-m-d')), $display);
		$display = str_replace('!!wish_date!!', ($this->wish_date ? substr($this->wish_date,0,10) : date('Y-m-d')), $display);
		$display = str_replace('!!deadline_date!!', ($this->deadline_date ? substr($this->deadline_date,0,10) : date('Y-m-d')), $display);
		
		if($this->status) {
			$status = $this->status->get_id();
		} else {
			$status = $opac_scan_request_status;
		}
		$display = str_replace('!!status!!', $status, $display);
		$display = str_replace('!!id!!', $this->id, $display);
		
		$linked_records_display = '';
		foreach ($this->linked_records as $linked_record) {
			$linked_record_display = $scan_request_linked_record;
			$linked_record_display = str_replace('!!linked_record_display!!', $linked_record['display'], $linked_record_display);
			if ($linked_record['notice_id']) {
				$linked_record_type = 'notices';
				$linked_record_id = $linked_record['notice_id'];
			} else {
				$linked_record_type = 'bulletins';
				$linked_record_id = $linked_record['bulletin_id'];
			}
			$linked_record_display = str_replace('!!linked_record_type!!', $linked_record_type, $linked_record_display);
			$linked_record_display = str_replace('!!linked_record_id!!', $linked_record_id, $linked_record_display);
			$linked_record_display = str_replace('!!linked_record_comment!!', $linked_record['comment'], $linked_record_display);
			$linked_records_display.= $linked_record_display;
		}
		$display = str_replace('!!linked_records!!', $linked_records_display, $display);
		$display = str_replace('!!id_suffix!!', htmlentities($id_suffix, ENT_QUOTES, $charset), $display);
		
		return $display;
	}
	
	public function get_display() {
		global $include_path;
		
		$tpl = $include_path.'/templates/scan_request/scan_request.tpl.html';
		if (file_exists($include_path.'/templates/scan_request/scan_request_subst.tpl.html')) {
			$tpl = $include_path.'/templates/scan_request/scan_request_subst.tpl.html';
		}
		$h2o = new H2o($tpl);
		return $h2o->render(array('scan_request' => $this));
	}
	
	public function get_display_in_list() {
		global $include_path;
		
		$tpl = $include_path.'/templates/scan_request/scan_request_in_list.tpl.html';
		if (file_exists($include_path.'/templates/scan_request/scan_request_in_list_subst.tpl.html')) {
			$tpl = $include_path.'/templates/scan_request/scan_request_in_list_subst.tpl.html';
		}
		$h2o = new H2o($tpl);
		return $h2o->render(array('scan_request' => $this));
	}
	
	public function delete() {
		global $dbh;
		
		$query = 'delete from scan_request_linked_records where scan_request_linked_record_num_request = '.$this->id;
		pmb_mysql_query($query, $dbh);
		$query = 'delete from scan_requests where id_scan_request = '.$this->id;
		pmb_mysql_query($query, $dbh);
	}

	public function get_id() {
		return $this->id;
	}

	public function get_title() {
		return $this->title;
	}

	public function get_desc() {
		return $this->desc;
	}

	public function get_status() {
		return $this->status;
	}

	public function get_priority() {
		return $this->priority;
	}

	public function get_create_date() {
		return $this->create_date;
	}

	public function get_update_date() {
		return $this->update_date;
	}

	public function get_date() {
		return $this->date;
	}

	public function get_wish_date() {
		return $this->wish_date;
	}

	public function get_deadline_date() {
		return $this->deadline_date;
	}

	public function get_comment() {
		return $this->comment;
	}

	public function get_elapsed_time() {
		return $this->elapsed_time;
	}

	public function get_num_dest_empr() {
		return $this->num_dest_empr;
	}

	public function get_num_creator() {
		return $this->num_creator;
	}

	public function get_type_creator() {
		return $this->type_creator;
	}

	public function get_num_last_user() {
		return $this->num_last_user;
	}

	public function get_state() {
		return $this->state;
	}
	
	public function get_linked_records() {
		return $this->linked_records;
	}
	
	public function get_formatted_update_date() {
		return $this->formatted_update_date;
	}
	
	public function get_formatted_date() {
		return $this->formatted_date;
	}
	
	public function get_formatted_wish_date() {
		return $this->formatted_wish_date;
	}
	
	public function get_formatted_deadline_date() {
		return $this->formatted_deadline_date;
	}
	
	public function get_display_link() {
		global $base_path;
		return $base_path.'/empr.php?tab=scan_requests&lvl=scan_request&sub=display&id='.$this->id;
	}
	
	public function get_edit_link() {
		global $base_path;
		return $base_path.'/empr.php?tab=scan_requests&lvl=scan_request&sub=edit&id='.$this->id;
	}

	public function get_cancel_link() {
		global $base_path;
		return $base_path.'/empr.php?tab=scan_requests&lvl=scan_request&sub=cancel&id='.$this->id;
	}
	
	public function set_id($id) {
		$this->id = $id;
	}

	public function set_title($title) {
		$this->title = $title;
	}

	public function set_desc($desc) {
		$this->desc = $desc;
	}

	public function set_status($status) {
		$this->status = $status;
	}

	public function set_priority($priority) {
		$this->priority = $priority;
	}

	public function set_create_date($create_date) {
		$this->create_date = $create_date;
	}

	public function set_update_date($update_date) {
		$this->update_date = $update_date;
	}

	public function set_date($date) {
		$this->date = $date;
	}

	public function set_wish_date($wish_date) {
		$this->wish_date = $wish_date;
	}

	public function set_deadline_date($deadline_date) {
		$this->deadline_date = $deadline_date;
	}

	public function set_comment($comment) {
		$this->comment = $comment;
	}

	public function set_elapsed_time($elapsed_time) {
		$this->elapsed_time = $elapsed_time;
	}

	public function set_num_dest_empr($num_dest_empr) {
		$this->num_dest_empr = $num_dest_empr;
	}

	public function set_num_creator($num_creator) {
		$this->num_creator = $num_creator;
	}

	public function set_type_creator($type_creator) {
		$this->type_creator = $type_creator;
	}

	public function set_num_last_user($num_last_user) {
		$this->num_last_user = $num_last_user;
	}

	public function set_state($state) {
		$this->state = $state;
	}
}
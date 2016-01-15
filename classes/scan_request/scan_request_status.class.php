<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_request_status.class.php,v 1.3 2016-01-13 15:41:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path."/templates/scan_request/scan_request_status.tpl.php");

class scan_request_status {
	private $scan_request_status;	//tableau des status 
	private $scan_request_status_workflow; //workflow : tableau des status from to
	
	public function __construct(){
		$this->fetch_data();
	}
		
	protected function fetch_data(){
		$this->scan_request_status = array();
		$this->scan_request_status_workflow = array();
		
		$rqt = "select * from scan_request_status order by scan_request_status_label asc";
		$res = pmb_mysql_query($rqt);
		if(pmb_mysql_num_rows($res)){
			while($row = pmb_mysql_fetch_object($res)){
				$this->scan_request_status[] =array(
					'id' => $row->id_scan_request_status,
					'label' => $row->scan_request_status_label,
					'opac_show' => $row->scan_request_status_opac_show,
					'class_html' => $row->scan_request_status_class_html,
					'user_infos_editable' => $row->scan_request_status_user_infos_editable,
					'user_list_editable' => $row->scan_request_status_user_list_editable,
					'user_cancelable' => $row->scan_request_status_user_cancelable
				);
				$this->scan_request_status_workflow[$row->id_scan_request_status]= array();
			}

			$rqt = "select * from scan_request_status_workflow ";
			$res = pmb_mysql_query($rqt);
			if(pmb_mysql_num_rows($res)){
				while($row = pmb_mysql_fetch_object($res)){					
					$this->scan_request_status_workflow[$row->scan_request_status_workflow_from_num][] =$row->scan_request_status_workflow_to_num;
				}
			}
		}
	}

	public function get_scan_request_status(){
		return $this->scan_request_status;
	}
	public function get_scan_request_status_workflow(){
		return $this->scan_request_status_workflow;
	}

	public function get_selector_options($selected=0){
		global $charset;
		global $deflt_scan_request_status;
		
		if(!$selected){
			$selected=$deflt_scan_request_status;
		}		
		$options = "";
		for($i=0 ; $i<count($this->scan_request_status) ; $i++){
			$options.= "
			<option value='".$this->scan_request_status[$i]['id']."'".($this->scan_request_status[$i]['id']==$selected ? "selected='selected'" : "").">".htmlentities($this->scan_request_status[$i]['label'],ENT_QUOTES,$charset)."</option>";	
		}
		return $options;
	}
	
	public function get_list($form_link="./admin.php?categ=scan_request&sub=status&action=edit"){
		global $msg,$charset;
		
		$table = "
		<table>
			<tr>
				<th>".$msg['scan_request_status_label']."</th>
				<th>".$msg['scan_request_status_visible']."</th>
			</tr>";
		for($i=0 ; $i<count($this->scan_request_status) ; $i++){
			$class = ($i%2 ? "odd":"even");
			$table.= "
			<tr class='".($i%2 ? "odd":"even")."' onclick='document.location=\"".$form_link."&id=".$this->scan_request_status[$i]['id']."\"' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\">
				<td><span class='".$this->scan_request_status[$i]['class_html']."'  style='margin-right: 3px;'><img src='./images/spacer.gif' width='10' height='10' /></span>
					".htmlentities($this->scan_request_status[$i]['label'],ENT_QUOTES,$charset)."
				</td>
				<td>".($this->scan_request_status[$i]['opac_show'] ? "X" : "")."</td>
			</tr>";
		}
		$table.= "
		</table>
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<input type='button' class='bouton' value='".$msg['editorial_content_publication_state_add']."' onclick='document.location=\"".$form_link."\"'/>
		</div>";
		return $table;
	}
	
	public function get_form($id=0,$url="./admin.php?categ=scan_request&sub=status"){
		global $msg,$charset;
		global $scan_request_status_form;
		
		
		$form =str_replace("!!action!!",$url,$scan_request_status_form);
		if($id){
			for($i=0 ; $i<count($this->scan_request_status) ; $i++){
				if($this->scan_request_status[$i]['id'] == $id){
					$publication_state = $this->scan_request_status[$i];
					break;
				}
			}
		}
		if($publication_state['id']){
			$form = str_replace("!!form_title!!",$msg['editorial_content_publication_state_edit'],$form);
			$form = str_replace("!!label!!",htmlentities($publication_state['label'],ENT_QUOTES,$charset),$form);
			$form = str_replace("!!visible!!",($publication_state['opac_show'] ? "checked='checked'": ""),$form);
			$form = str_replace("!!cancelable!!",($publication_state['user_cancelable'] ? "checked='checked'": ""),$form);
			$form = str_replace("!!list_editable!!",($publication_state['user_list_editable'] ? "checked='checked'": ""),$form);
			$form = str_replace("!!infos_editable!!",($publication_state['user_infos_editable'] ? "checked='checked'": ""),$form);
			$form = str_replace("!!id!!",$publication_state['id'],$form);
			$form = str_replace("!!bouton_supprimer!!","<input type='button' class='bouton' value=' ".$msg[63]." ' onclick='confirmation_delete(\"&action=delete&id=".$publication_state['id']."\",\"".htmlentities($publication_state['label'],ENT_QUOTES,$charset)."\")'/>",$form);
			$form.= confirmation_delete($url);
		}else{
			$form = str_replace("!!form_title!!",$msg['editorial_content_publication_state_add'],$form);	
			$form = str_replace("!!label!!","",$form);
			$form = str_replace("!!visible!!","",$form);
			$form = str_replace("!!cancelable!!","",$form);
			$form = str_replace("!!list_editable!!","",$form);
			$form = str_replace("!!infos_editable!!","",$form);
			$form = str_replace("!!id!!",0,$form);
			$form = str_replace("!!bouton_supprimer!!","",$form);
		}
		for ($i=1;$i<=20; $i++) {
			if ($publication_state['class_html']=="statutnot".$i) $checked = "checked";
			else $checked = "";
			$couleur[$i]="<span for='statutnot".$i."' class='statutnot".$i."' style='margin: 7px;'><img src='./images/spacer.gif' width='10' height='10' />
					<input id='statutnot".$i."' type=radio name='scan_request_status_class_html' value='statutnot".$i."' $checked class='checkbox' /></span>";
			if ($i==10) $couleur[10].="<br />";
			elseif ($i!=20) $couleur[$i].="<b>|</b>";
		}
		$couleurs=implode("",$couleur);
		$form = str_replace('!!class_html!!', $couleurs, $form);
		
		return $form;
	}

	public function get_form_workflow($url="./admin.php?categ=scan_request&sub=workflow"){
		global $msg,$charset, $current_module;
		global $opac_scan_request_status;
		
		$form=
		"<h1>".$msg["admin_scan_request_workflow_form_title"]."</h1>
		<form class='form-$current_module' id='userform' name='scan_request_status_workflow_form' method='post' action='".$url."&action=save'>
			<table>
				<tr>
					<th colspan='2'>".$msg["admin_scan_request_workflow_title"]."</th>
					<th colspan=".count($this->scan_request_status).">".$msg["admin_scan_request_workflow_after_title"]."</th>
				</tr>
				<tr>
					<th></th>
					<th>".$msg["scan_request_workflow_opac_status"]."</th>
				";
					$ligne="";
					foreach($this->scan_request_status as $statusfrom) {
						$form.="<th>".$statusfrom['label']."</th>";
						if ($parity++ % 2) {
							$pair_impair = "even";
						} else {
							$pair_impair = "odd";
						}
						$ligne.="</tr><tr class='$pair_impair'><td>".$statusfrom['label']."</td>";
						$ligne.="<td ALIGN=center><input type='radio' name='scan_request_opac_status' value='".$statusfrom['id']."' ".($opac_scan_request_status == $statusfrom['id'] ? "checked='checked'" : "")." /></td>";
						foreach($this->scan_request_status as $statusto) {
							if(in_array($statusto['id'],$this->scan_request_status_workflow[$statusfrom['id']])) $check=" checked='checked' ";
							else $check="";
							if($statusfrom['id']==$statusto['id']){
								$ligne.="<td><input value='1' name='scan_request_status_tab[".$statusfrom['id']."][".$statusto['id']."]' type='hidden'  ></td>";
							}else{
								$ligne.="<td ALIGN=center><input value='1' name='scan_request_status_tab[".$statusfrom['id']."][".$statusto['id']."]' type='checkbox' $check ></td>";
							}	
						}
					}
					$form.=$ligne."
				</tr>
			</table>
			<input class='bouton' type='submit' value=' ".$msg[77] ." ' />
		</form>";	
		return $form;
	}

	public function save_workflow(){
		global $dbh;
		global $scan_request_status_tab;
		global $scan_request_opac_status;
		
		$query="TRUNCATE TABLE scan_request_status_workflow";
		pmb_mysql_query($query);	
		foreach ($scan_request_status_tab as $from => $tolist){
			foreach ($tolist as $to => $val){
				$query = "insert into scan_request_status_workflow set scan_request_status_workflow_from_num='".$from."', scan_request_status_workflow_to_num='".$to."'";
				pmb_mysql_query($query);
			}
		}
		$query = "UPDATE parametres SET valeur_param='".$scan_request_opac_status."' WHERE type_param='opac' and sstype_param='scan_request_status'";
		pmb_mysql_query($query);
		$this->fetch_data();		
	}
	public function save(){
		global $dbh;
		global $scan_request_status_label;
		global $scan_request_status_visible;
		global $scan_request_status_visible_abo; 
		global $scan_request_status_id;
		global $scan_request_status_class_html;
		global $scan_request_user_cancelable;
		global $scan_request_user_infos_editable;
		global $scan_request_user_list_editable;
		if($scan_request_status_id){
			$scan_request_status_id+=0;
			$query = "update scan_request_status set ";
			$clause = "where id_scan_request_status = ".$scan_request_status_id;
		}else{
			$query = "insert into scan_request_status set ";
			$clause = "";
		}
		$query.= "
			scan_request_status_label = '".$scan_request_status_label."',
			scan_request_status_opac_show = ".($scan_request_status_visible ? 1 : 0).",
			scan_request_status_user_cancelable = ".($scan_request_user_cancelable ? 1 : 0).",
			scan_request_status_user_list_editable = ".($scan_request_user_list_editable ? 1 : 0).",
			scan_request_status_user_infos_editable = ".($scan_request_user_infos_editable ? 1 : 0).",
			scan_request_status_class_html = '".$scan_request_status_class_html."'";
		$query.= " ".$clause;
		pmb_mysql_query($query);		
		
		$this->fetch_data();
	}
	
	public function delete($id){
		global $msg,$charset;
		$id+=0;
		if(!$id){
			return;
		}
		if($error){
			print "
			<script type='text/javascript'>
				alert(\"".$msg['cant_delete'].". ".$error."\");
			</script>";
		}else{
			$query = "delete from scan_request_status where id_scan_request_status = ".$id;
			pmb_mysql_query($query);
		}		
		$this->fetch_data();
	}
}
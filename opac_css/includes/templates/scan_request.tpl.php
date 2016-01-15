<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: scan_request.tpl.php,v 1.1 2016-01-13 15:41:42 apetithomme Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

$scan_request_form ='
<form method="post" name="scan_request_form" action="./empr.php?tab=scan_requests&lvl=scan_request&sub=save&id=!!id!!">
	<h3>!!form_title!!</h3>
	<div class="form-contenu">
		!!form_content!!
	</div>
	<div class="row">&nbsp;</div>
	<div class="row">
		<div class="left">
			<input class="bouton" type="button" value=" '.$msg['76'].' " onClick="history.go(-1);">
			<input class="bouton" type="submit" value=" '.$msg['77'].' " onClick=\"return test_form(this.form)\">
		</div>
		<div class="right-clear-right">
			!!delete_button!!
		</div>
	</div>
	<div class="row">&nbsp;</div>
</form>
<script type="text/javascript">
	function test_form(form){
		if(form.scan_request_title.value.length == 0){
			alert("\''.$msg[98].'\'");
			return false;
		}
		return true;
	}

</script>';

$scan_request_form_content = '
		<!-- Titre & description de la demande -->
		<div class="row">
			<div class="row">
				<label for="scan_request_title!!id_suffix!!">'.$msg["scan_request_title"].'</label>
			</div>
			<div class="row">
				<input type="text" id="scan_request_title!!id_suffix!!" name="scan_request_title" value="!!title!!"/>			
			</div>
		</div>
		<div class="row">
			<div class="row">		
				<label for="scan_request_desc!!id_suffix!!">'.$msg["scan_request_desc"].'</label>
			</div>
			<div class="row">
				<textarea id="scan_request_desc!!id_suffix!!" name="scan_request_desc" rows="3" wrap="virtual">!!desc!!</textarea>				
			</div>
		</div>
		
		<!-- Priorite de la demande -->
		<div class="row">
			<div class="colonne3">
				<div class="row">		
					<label for="scan_request_priority!!id_suffix!!">'.$msg["scan_request_priority"].'</label>
				</div>
				<div class="row">
					<select id="scan_request_priority!!id_suffix!!" name="scan_request_priority">
						!!priority!!
					</select>	
				</div>		
			</div>
		</div>
	
		<div class="row">
			<div class="colonne3">
				<div class="row">		
					<label for="scan_request_date!!id_suffix!!">'.$msg["scan_request_date"].'</label>
				</div>
				<div class="row">
					<input type="text" name="scan_request_date" id="scan_request_date!!id_suffix!!" value="!!date!!"  data-dojo-type="dijit/form/DateTextBox" required="true" />
				</div>
			</div>
			<div class="colonne3">
				<div class="row">		
					<label for="scan_request_wish_date!!id_suffix!!">'.$msg["scan_request_wish_date"].'</label>
				</div>
				<div class="row">
					<input type="text" name="scan_request_wish_date" id="scan_request_wish_date!!id_suffix!!" value="!!wish_date!!"  data-dojo-type="dijit/form/DateTextBox" required="true" />
				</div>
			</div>
			<div class="colonne3">
				<div class="row">		
					<label for="scan_request_deadline_date!!id_suffix!!">'.$msg["scan_request_deadline_date"].'</label>
				</div>
				<div class="row">
					<input type="text" name="scan_request_deadline_date" id="scan_request_deadline_date!!id_suffix!!" value="!!deadline_date!!"  data-dojo-type="dijit/form/DateTextBox" required="true" />
				</div>
			</div>
		</div>
		<div class="row">&nbsp;</div>
		<div class="row">
			<div class="row">
				<label>'.$msg["scan_request_linked_records"].'</label>
			</div>
			!!linked_records!!
		</div>
		<div class="row">
			<div class="row">		
				<label for="scan_request_comment!!id_suffix!!">'.$msg["scan_request_comment"].'</label>
			</div>
			<div class="row">
				<textarea id="scan_request_comment!!id_suffix!!" name="scan_request_comment" rows="3" wrap="virtual">!!comment!!</textarea>				
			</div>
		</div>
		<input type="hidden" name="scan_request_status" id="scan_request_status!!id_suffix!!" value="!!status!!"/>';

$scan_request_form_delete_button = '
			<input class="bouton" type="button" value=" '.$msg['scan_request_delete'].' " onClick="if (confirm(\''.addslashes($msg['scan_request_confirm_cancel']).'\')) {document.location=\'./empr.php?tab=scan_requests&lvl=scan_request&sub=cancel&id=!!id!!\'}">';

$scan_request_linked_record = '
	<div class="row">
		<div class="colonne3">
			<div class="row">
				!!linked_record_display!!
			</div>
		</div>
		<div class="colonne">
			<textarea id="scan_request_linked_records_!!linked_record_type!!_!!linked_record_id!!_comment!!id_suffix!!" name="scan_request_linked_records_!!linked_record_type!![!!linked_record_id!!][comment]">!!linked_record_comment!!</textarea>
		</div>
		<input type="hidden" value="scan_request_linked_records_!!linked_record_type!!_!!linked_record_id!!" name="scan_request_linked_records!!id_suffix!![]"/>
	</div>';

$scan_request_form_in_record = "
		<a href='#' onClick='show_scan_request(\"!!id_suffix!!\");return false;'>
			".$msg['do_scan_request_on_document']."
		</a>
		<div id='scan_request!!id_suffix!!' style='display:none;'>
			!!form_content!!
			<div class='row'>
				<div class='left'>
					<input class='bouton' type='button' value='".$msg['76']."' onClick='show_scan_request(\"!!id_suffix!!\");'>
					<input class='bouton' type='button' value='".$msg['77']."' onClick='return create_scan_request_in_record(\"!!id_suffix!!\", \"!!record_type!!\", !!record_id!!);'>
				</div>
			</div>
			<div class='row'>&nbsp;</div>
		</div>";
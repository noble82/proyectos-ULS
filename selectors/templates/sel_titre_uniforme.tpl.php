<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: sel_titre_uniforme.tpl.php,v 1.13 2015-12-28 15:02:33 ngantier Exp $

if (stristr($_SERVER['REQUEST_URI'], "tpl.php")) die("no access");

// templates du sélecteur auteur

//--------------------------------------------
//	$nb_per_page : nombre de lignes par page
//--------------------------------------------
// nombre de références par pages
if ($nb_per_page_a_select != "") 
	$nb_per_page = $nb_per_page_a_select ;
	else $nb_per_page = 10;

//-------------------------------------------
//	$sel_header : header
//-------------------------------------------
$sel_header = "
<div id='att' style='z-Index:1000'></div>		
<script src='javascript/ajax.js'></script>
		
<div class='row'>
	<label for='titre_select_titre_uniforme' class='etiquette'>".$msg["aut_menu_titre_uniforme"]."</label>
	</div>	
<div class='row'>
";

//-------------------------------------------
//	$jscript : script de m.a.j. du parent
//-------------------------------------------
/* pour $dyn=3, renseigner les champs suivants: (passé dans l'url)
 *
* $max_field : nombre de champs existant
* $field_id : id de la clé
* $field_name_id : id  du champ text
* $add_field : nom de la fonction permettant de rajouter un champ
* $myid : id de l'appelant interdisant la meme selection
*/
if ($dyn==3) {
	$jscript ="
<script type='text/javascript'>
	function set_parent(f_caller, id_value, libelle_value, callback){
		var w=window;
		var i=0;
		if(!(typeof w.opener.$add_field == 'function')) {
			w.opener.document.getElementById('$field_id').value = id_value;
			w.opener.document.getElementById('$field_name_id').value = reverse_html_entities(libelle_value);
			parent.parent.close();
			return;
		}
		var n_element=w.opener.document.forms[f_caller].elements['$max_field'].value;
		var flag = 1;
		
		//Vérification que l'élément n'est pas déjà sélectionnée
		for (var i=0; i<n_element; i++) {
			if (w.opener.document.getElementById('$field_id'+i).value==id_value) {
				alert('".addslashes($msg["aut_oeuvre_already_in_use"])."');
				flag = 0;
				break;
			}			
		}
		if(id_value=='$myid'){
			alert('".addslashes($msg["aut_oeuvre_already_in_use"])."');
			flag = 0;
		}		
		if (flag) {
			for (var i=0; i<n_element; i++) {
				if ((w.opener.document.getElementById('$field_id'+i).value==0)||(w.opener.document.getElementById('$field_id'+i).value=='')) break;
			}
		
			if (i==n_element) w.opener.$add_field();
			w.opener.document.getElementById('$field_id'+i).value = id_value;
			w.opener.document.getElementById('$field_name_id'+i).value = reverse_html_entities(libelle_value);
		    if(callback){
			 if(typeof w.opener[callback] == 'function'){
                w.opener[callback](id_value);
		     }
		    }
		}	
	}
</script>";
}elseif ($dyn==2) { // Pour les liens entre autorités
	$jscript = "
	<script type='text/javascript'>
	<!--
	function set_parent(f_caller, id_value, libelle_value)
	{	
		w=window;
		n_aut_link=w.opener.document.forms[f_caller].elements['max_aut_link'].value;
		flag = 1;	
		//Vérification que l'autorité n'est pas déjà sélectionnée
		for (i=0; i<n_aut_link; i++) {
			if (w.opener.document.getElementById('f_aut_link_id'+i).value==id_value && w.opener.document.getElementById('f_aut_link_table'+i).value==$param1) {
				alert('".$msg["term_already_in_use"]."');
				flag = 0;
				break;
			}
		}	
		if (flag) {
			for (i=0; i<n_aut_link; i++) {
				if ((w.opener.document.getElementById('f_aut_link_id'+i).value==0)||(w.opener.document.getElementById('f_aut_link_id'+i).value=='')) break;
			}	
			if (i==n_aut_link) w.opener.add_aut_link();
			
			var selObj = w.opener.document.getElementById('f_aut_link_table_list');
			var selIndex=selObj.selectedIndex;
			w.opener.document.getElementById('f_aut_link_table'+i).value= selObj.options[selIndex].value;
			
			w.opener.document.getElementById('f_aut_link_id'+i).value = id_value;
			w.opener.document.getElementById('f_aut_link_libelle'+i).value = reverse_html_entities('['+selObj.options[selIndex].text+']'+libelle_value);		
		}	
	}
	-->
	</script>
	";
}elseif ($dyn!=1) {
$jscript = "
<script type='text/javascript'>
<!--
function set_parent(f_caller, id_value, libelle_value, callback)
{
	var p1 = '$param1';
	var p2 = '$param2';
	//on enlève le dernier _X
	var tmp_p1 = p1.split('_');
	var tmp_p1_length = tmp_p1.length;
	tmp_p1.pop();
	var p1bis = tmp_p1.join('_');
	
	var tmp_p2 = p2.split('_');
	var tmp_p2_length = tmp_p2.length;
	tmp_p2.pop();
	var p2bis = tmp_p2.join('_');

	var max_aut = window.opener.document.getElementById(p1bis.replace('id','max_aut'));
	if(max_aut && (p1bis.replace('id','max_aut').substr(-7)=='max_aut')){
		var trouve=false;
		var trouve_id=false;
		for(i_aut=0;i_aut<=max_aut.value;i_aut++){
			if(window.opener.document.getElementById(p1bis+'_'+i_aut).value==0){
				window.opener.document.getElementById(p1bis+'_'+i_aut).value=id_value;
				window.opener.document.getElementById(p2bis+'_'+i_aut).value=reverse_html_entities(libelle_value);
				trouve=true;
				break;
			}else if(window.opener.document.getElementById(p1bis+'_'+i_aut).value==id_value){
				trouve_id=true;
			}
		}
		if(!trouve && !trouve_id){
			window.opener.add_line(p1bis.replace('_id',''));
			window.opener.document.getElementById(p1bis+'_'+i_aut).value=id_value;
			window.opener.document.getElementById(p2bis+'_'+i_aut).value=reverse_html_entities(libelle_value);
		}
		if(callback)
			window.opener[callback](p1bis.replace('_id','')+'_'+i_aut);
	}else{
		window.opener.document.forms[f_caller].elements['$param1'].value = id_value;
		window.opener.document.forms[f_caller].elements['$param2'].value = reverse_html_entities(libelle_value);
		if(callback)
			window.opener[callback]('$infield');	
		window.close();
	}
}
-->
</script>
";
} else {
	$jscript = "
<script type='text/javascript'>
<!--
function set_parent(f_caller, id_value, libelle_value)
{
	window.opener.document.getElementById('$param1').value = id_value;
	window.opener.document.getElementById('$param2').value = reverse_html_entities(libelle_value);
	window.close();
}
-->
</script>";
}

//-------------------------------------------
//	$sel_search_form : module de recherche
//-------------------------------------------
$sel_search_form ="
<form name='search_form' method='post' action='$base_url'>
<input type='text' name='f_user_input' value=\"!!deb_rech!!\">&nbsp;
<input type='submit' class='bouton_small' value='$msg[142]' >&nbsp;!!bouton_ajouter!!<br />
</form>
<script type='text/javascript'>
<!--
	document.forms['search_form'].elements['f_user_input'].focus();
-->
</script>
";

// ------------------------------------------
// 	$author_form : form saisie éditeur
// ------------------------------------------
$titre_uniforme_form = "
<script type='text/javascript'>
<!--
	function test_form(form)
	{
		if(form.name.value.length == 0)
			{
				return false;
			}
		return true;
	}
-->

</script>
<form name='saisie_titre_uniforme' method='post' action=\"$base_url&action=update\">
<!-- ajouter un titre uniforme -->
<div class='left'><h3>".$msg["aut_titre_uniforme_ajouter"]."</h3></div>
<div class='right'>
<!-- Selecteur de statut -->
    <label class='etiquette'>".$msg['authorities_statut_label']."</label>
     !!auth_statut_selector!!
</div>
<div class='form-contenu'>
	<div id='el0Child_0' class='row' movable='yes' title=\"".htmlentities($msg["aut_oeuvre_form_oeuvre_type"], ENT_QUOTES, $charset)."\">
		<div class='row'>
			<label class='etiquette' for='oeuvre_type'>".$msg["aut_oeuvre_form_oeuvre_type"]."</label>
		</div>
		<div class='row'>
			!!oeuvre_type!!
		</div>
	</div>
	<div id='el0Child_1' class='row' movable='yes' title=\"".htmlentities($msg["aut_oeuvre_form_oeuvre_nature"], ENT_QUOTES, $charset)."\">			
		<div class='row'>
			<label class='etiquette' for='oeuvre_nature'>".$msg["aut_oeuvre_form_oeuvre_nature"]."</label>
		</div>
		<div class='row'>
			!!oeuvre_nature!!
		</div>
	</div>
	<!--	nom	-->
	<div class='row'>
		<label class='etiquette' for='form_name'>".$msg["aut_titre_uniforme_form_nom"]."</label>
	</div>
	<div class='row'>
		<input type='text' class='saisie-50em' name='name' value=\"!!deb_saisie!!\" />
	</div>
	!!authors!!
</div>	
<div class='row'>
	<input type='button' class='bouton_small' value='$msg[76]' onClick=\"document.location='$base_url&what=titre_uniforme';\">
	<input type='submit' value='$msg[77]' class='bouton_small' onClick=\"return test_form(this.form)\">
	</div>
</form>
<script type='text/javascript'>
	document.forms['saisie_titre_uniforme'].elements['name'].focus();
</script>
";

//-------------------------------------------
//	$sel_footer : footer
//-------------------------------------------
$sel_footer = "
</div>
";

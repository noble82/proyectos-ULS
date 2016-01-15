<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: demandes.tpl.php,v 1.17 2015-08-05 15:33:03 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

// $acquisition_menu : menu page acquisition
$demandes_menu = "
<div id='menu'>
	<h3 onclick='menuHide(this,event)'>".$msg['demandes_menu_liste']."</h3>
	<ul>
		<li><a href='./demandes.php?categ=list&idetat=0'>".$msg['demandes_menu_all']."</a></li>
		<li><a href='./demandes.php?categ=list&idetat=1'>".$msg['demandes_menu_a_valide']."</a></li>
		<li><a href='./demandes.php?categ=list&idetat=2&iduser=".SESSuserid."'>".$msg['demandes_menu_en_cours']."</a></li>
		<li><a href='./demandes.php?categ=list&idetat=3&iduser=".SESSuserid."'>".$msg['demandes_menu_refuse']."</a></li>
		<li><a href='./demandes.php?categ=list&idetat=4&iduser=".SESSuserid."'>".$msg['demandes_menu_fini']."</a></li>
		<li><a href='./demandes.php?categ=list&idetat=5&iduser=".SESSuserid."'>".$msg['demandes_menu_abandon']."</a></li>
		<li><a href='./demandes.php?categ=list&idetat=6&iduser=".SESSuserid."'>".$msg['demandes_menu_archive']."</a></li>
		<li><a href='./demandes.php?categ=list&iduser=-1'>".$msg[demandes_menu_not_assigned]."</a></li>				
	</ul>	
	<h3 onclick='menuHide(this,event)'>".$msg['demandes_menu_action']."</h3>
	<ul>
		<li><a href='./demandes.php?categ=action&sub=com'>".$msg['demandes_menu_comm']."</a></li>
		<li><a href='./demandes.php?categ=action&sub=rdv_plan'>".$msg['demandes_menu_rdv_planning']."</a></li>
		<li><a href='./demandes.php?categ=action&sub=rdv_val'>".$msg['demandes_menu_rdv_a_valide']."</a></li>
	</ul>
	<div id='div_alert' class='erreur'>$aff_alerte</div>
</div>
";

// $demandes_layout : layout page demandes
$demandes_layout = "
<div id='conteneur' class='$current_module'>
$demandes_menu
<div id='contenu'>
";

// $demandes_layout_end : layout page demandes (fin)
$demandes_layout_end = "
</div>
</div>
";


$form_filtre_demande = "
 <script type='text/javascript'>
	function filtrer_user(){
 		document.forms['search'].submit();
	} 
</script>
<h1>".$msg['demandes_gestion']." : ".$msg['demandes_search_form']." !!etat_demandes!!</h1>
<form class='form-".$current_module."' id='search' name='search' method='post' action=\"./demandes.php?categ=list\">
	<h3>".$msg['demandes_search_filtre_form']."</h3>
	<input type='hidden' name='act' id='act' />
	<div class='form-contenu'>
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_titre']."</label>
		</div>
		<div class='row'>
			<input type='texte' class='saisie-30em' name='user_input' id='user_input' value='!!user_input!!'/>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_user_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_etat_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_periode_filtre']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<input type='hidden' id='idempr' name='idempr' value='!!idempr!!' />
				<input type='text' id='empr_txt' name='empr_txt' class='saisie-20emr' value='!!empr_txt!!'/>
				<input type='button' class='bouton_small' value='...' onclick=\"openPopUp('./select.php?what=origine&caller=search&param1=idempr&param2=empr_txt&deb_rech='+".pmb_escape()."(this.form.empr_txt.value)+'&filtre=ONLY_EMPR&callback=filtrer_user".($pmb_lecteurs_localises ? "&empr_loca='+this.form.dmde_loc.value": "'").", 'select_user', 400, 400, -2, -2, 'scrollbars=yes, toolbar=no, dependent=yes, resizable=yes')\" />
				<input type='button' class='bouton_small' value='X' onclick=\"document.getElementById('idempr').value=0;document.getElementById('empr_txt').value='';\" />
			</div>
			<div class='colonne3'>
				!!state!!
			</div>
			<div class='colonne3'>
				!!periode!!
			</div>
		</div>
		<div class='row'> 
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_affectation_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme_filtre']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type_filtre']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				!!affectation!!
			</div>
			<div class='colonne3'>
				!!theme!!
			</div>
			<div class='colonne3'>
				!!type!!
			</div>
		</div>";
if($pmb_lecteurs_localises)
$form_filtre_demande .="
		<div class='row'> 
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_localisation_filtre']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				!!localisation!!
			</div>
		</div>";
$form_filtre_demande .="
		<div class='row'></div>
		!!champs_perso!!
		<div class='row'></div>
	</div>
	<div class='row'></div>
	<div class='row'>
		<input type='submit' class='bouton' name='search_dmd' id='search_dmd' value='".$msg['demandes_search']."' onclick='this.form.act.value=\"search\"'/>
		<input type='submit' class='bouton' name='new_dmd' id='new_dmd' value='".$msg['demandes_new']."' onclick='this.form.act.value=\"new\"'/>
	</div>
</form>

";

$form_liste_demande ="
<script src='./includes/javascript/dynamic_element.js' type='text/javascript'></script>
<script type='text/javascript'>

var base_path = '.';
var imgOpened = new Image();
imgOpened.src = base_path+'/images/minus.gif';
var imgClosed = new Image();
imgClosed.src = base_path+'/images/plus.gif';
var imgPatience =new Image();
imgPatience.src = base_path+'/images/patience.gif';
var expandedDb = '';

function expand_action(el, id_demande , unexpand) {
	if (!isDOM){
    	return;
	}
	
	var whichEl = document.getElementById(el + 'Child');
	var whichElTd = document.getElementById(el + 'ChildTd');
	var whichIm = document.getElementById(el + 'Img');
	
  	if(whichEl.style.display == 'none') {
		if(whichElTd.innerHTML==''){
			var req = new http_request();
			req.request('./ajax.php?module=ajax&categ=demandes&quoifaire=show_list_action',true,'id_demande='+id_demande,true,function(data){
		  		whichElTd.innerHTML=data;
			});
		}
		whichEl.style.display  = '';
    	if (whichIm){
    		whichIm.src= imgOpened.src;
    	}
    	changeCoverImage(whichEl);
	}else if(unexpand) {
    	whichEl.style.display='none';
    	if (whichIm){
    		whichIm.src=imgClosed.src;
    	}
  	}		
}

 function verifChk(txt) {
		
	var elts = document.forms['liste'].elements['chk[]'];
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;
	nb_chk = 0;
	if (elts_cnt) {
		for(var i=0; i < elts.length; i++) {
			if (elts[i].checked) nb_chk++;
		}
	} else {
		if (elts.checked) nb_chk++;
	}
	if (nb_chk == 0) {
		alert(\"".$msg['demandes_nocheck']."\");
		return false;	
	}
	
	if(txt == 'suppr'){
		var sup = confirm(\"".$msg['demandes_confirm_suppr']."\");
		if(!sup) 
			return false;
		return true;
	}
	
	return true;
}

function alert_progressiondemande(){
	alert(\"".$msg['demandes_progres_ko']."\");
}
			
function change_read(el, id_demande) {
	if (!isDOM){
    	return;
	}		
	var whichEl = document.getElementById(el);	
	var whichIm1 = document.getElementById(el + 'Img1');
	var whichIm2 = document.getElementById(el + 'Img2');	
	var whichTr = whichIm1.parentNode.parentNode;
	
	var req = new http_request();
	req.request('./ajax.php?module=demandes&categ=dmde&quoifaire=change_read',true,'id_demande='+id_demande,true,function(data){
 		if(data == 1){
			if(whichIm1.style.display == ''){
				whichIm1.style.display = 'none';
				whichIm2.style.display = '';
			} else {
				whichIm1.style.display = '';
				whichIm2.style.display = 'none';	
			}
		
			if(whichIm1.parentNode.parentNode.style.fontWeight == ''){
				whichIm1.parentNode.parentNode.style.fontWeight = 'bold';
				
			} else {
				whichIm1.parentNode.parentNode.style.fontWeight = '';
				
			}
 		}
	});		
}
			
</script>";

if($demandes_notice_auto)
	$demandes_notice_auto_tpl="<th>".$msg['demandes_notice']."</th>";
if(!$opac_demandes_affichage_simplifie) 
$form_liste_demande.="
<form class='form-".$current_module."' id='liste' name='liste' method='post' action=\"./empr.php?tab=request&lvl=list_dmde\">
	<input type='hidden' name='act' id='act' />
	<input type='hidden' name='state' id='state' />
	<h3>".$msg['demandes_liste']."</h3>
	<div class='row'>
		!!select_etat!!
	</div>
	<div class='form-contenu'>
		<table>
			<tbody>
				<tr>
					<th></th>
					<th></th>
					<th>".$msg['demandes_titre']."</th>
					!!entete_etat!!
					<th>".$msg['demandes_date_dmde']."</th>
					<th>".$msg['demandes_date_butoir']."</th>
					<th>".$msg['demandes_user']."</th>
					<th>".$msg['demandes_progression']."</th>
					!!header_champs_perso!!
					<th>".$msg['demandes_linked_record']."</th>
					$demandes_notice_auto_tpl
				</tr>
				!!liste_dmde!!				
			</tbody>
		</table>
	</div>
	<div class='row'></div>
</form>	
";
else
$form_liste_demande.="
<form class='form-".$current_module."' id='liste' name='liste' method='post' action=\"./empr.php?tab=request&lvl=list_dmde\">
	<input type='hidden' name='act' id='act' />
	<input type='hidden' name='state' id='state' />
	<h3>".$msg['demandes_liste']."</h3>
	<div class='row'>
		!!select_etat!!
	</div>
	<div class='form-contenu'>
		<table>
			<tbody>
				<tr>
					<th></th>
					<th></th>
					<th>".$msg['demandes_titre']."</th>
					!!entete_etat!!
					<th>".$msg['demandes_date_dmde']."</th>
					!!header_champs_perso!!
					<th>".$msg['demandes_linked_record']."</th>
					$demandes_notice_auto_tpl
				</tr>
				!!liste_dmde!!				
			</tbody>
		</table>
	</div>
	<div class='row'></div>
</form>	
";

if(!$opac_demandes_affichage_simplifie){
	$date_prevue_label_tpl="<label class='etiquette'>".$msg['demandes_date_prevue']."</label>";
	$date_prevue_tpl="
		<input type='hidden' id='date_prevue' name='date_prevue' value='!!date_prevue!!' />
		<input type='button' class='bouton' id='date_prevue_btn' name='date_prevue_btn' value='!!date_prevue_btn!!' onClick=\"openPopUp('./select.php?what=calendrier&caller=modif_dmde&date_caller=!!date_prevue!!&param1=date_prevue&param2=date_prevue_btn&auto_submit=NO&date_anterieure=YES', 'date_prevue', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\"/>
				";
	$date_echeance_label_tpl="<label class='etiquette'>".$msg['demandes_date_butoir']."</label>";
	$date_echeance_tpl="
		<input type='hidden' id='date_fin' name='date_fin' value='!!date_fin!!' />
		<input type='button' class='bouton' id='date_fin_btn' name='date_fin_btn' value='!!date_fin_btn!!' onClick=\"openPopUp('./select.php?what=calendrier&caller=modif_dmde&date_caller=!!date_fin!!&param1=date_fin&param2=date_fin_btn&auto_submit=NO&date_anterieure=YES', 'date_fin', 250, 300, -2, -2, 'toolbar=no, dependent=yes, resizable=yes')\"/>
			";
}	
$form_modif_demande = "
<form class='form-".$current_module."' id='modif_dmde' name='modif_dmde' method='post' action=\"!!form_action!!\">
	<h3>!!form_title!!</h3>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='idempr' name='idempr' value='!!idempr!!' />
	<input type='hidden' id='idetat' name='idetat' value='!!idetat!!' />
	<input type='hidden' id='iduser' name='iduser' value='!!iduser!!' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>		
				<label class='etiquette'>".$msg['demandes_theme']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']."</label>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_etat']."</label>
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				!!select_theme!!
			</div>
			<div class='colonne3'>
				!!select_type!!
			</div>
			<div class='colonne3'>
				!!value_etat!!
			</div>
		</div>
			
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_titre']."</label>
		</div>
		<div class='row'>
			<input class='saisie-50em' type='texte' id='titre' name='titre' value='!!titre!!' />
		</div>
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_sujet']."</label>
		</div>
		<div class='row'>
			<textarea id='sujet' name='sujet' cols='55' rows='4' wrap='virtual'>!!sujet!!</textarea>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_dmde']."</label>
			</div>
			<div class='colonne3'>
				$date_prevue_label_tpl
			</div>
			<div class='colonne3'>
				$date_echeance_label_tpl
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<input type='hidden' id='date_debut' name='date_debut' value='!!date_debut!!' />
				!!date_demande!!
			</div>
			<div class='colonne3'>
				$date_prevue_tpl
			</div>
			<div class='colonne3'>
				$date_echeance_tpl
			</div>
		</div>
		!!form_linked_record!!
		<div class='row'>&nbsp;</div>
	</div>
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='$msg[76]' onClick=\"!!cancel_action!!\" />
			<input type='submit' class='bouton' value='$msg[77]' onClick='this.form.act.value=\"save\";return test_form_demand(this.form); ' />
		</div>
	</div>
	<div class='row'></div>
</form>

<script type='text/javascript'>
	function test_form_demand(form) {
		if((form.titre.value.length == 0) || (form.date_debut.value.length == 0)||  (form.date_fin.value.length == 0)){
			alert(\"$msg[demandes_create_ko]\");
			return false;
	    }
	    
	    var deb =form.date_debut.value;
	    var fin = form.date_fin.value;
	   
	    if(deb>fin){
	    	alert(\"$msg[demandes_date_ko]\");
	    	return false;
	    }
		return true;
			
	}
</script>
";
if(!$opac_demandes_affichage_simplifie)
$form_consult_dmde = "
<script src='./includes/javascript/demandes.js' type='text/javascript'></script>
<script src='./includes/javascript/tablist.js' type='text/javascript'></script>
<script src='./includes/javascript/select.js' type='text/javascript'></script>

<form class='form-".$current_module."' id='see_dmde' name='see_dmde' method='post' action=\"./demandes.php?categ=gestion\">
	<h3>!!icone!!!!form_title!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='state' name='state' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme']." : </label>
				!!theme_dmde!!
			</div>
			<div class='colonne3'>		
				<label class='etiquette'>".$msg['demandes_etat']." : </label>
				!!etat_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_dmde']." : </label>
				!!date_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_sujet']." : </label>
				!!sujet_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_prevue']." : </label>
				!!date_prevue_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']." : </label>
				!!type_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_attribution']." : </label>
				!!attribution!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_butoir']." : </label>
				!!date_butoir_dmde!!
			</div>
		</div>	
		
		<div class='row'>
			<div class='colonne3'>
				&nbsp;
			</div>	
			<div class='colonne3'>
				&nbsp;			
			</div>
			<div class='colonne3'>
				<label class='etiquette' >".$msg['demandes_progression']." : </label>
				<span id='progressiondemande_!!iddemande!!' name='progressiondemande_!!iddemande!!' dynamics='demandes,progressiondemande' dynamics_params='text'>!!progression_dmde!!</span>
			</div>
		</div>
		!!form_linked_record!!
		<div class='row'></div>
		<div class='row'>
			!!champs_perso!!
		</div>
		<div class='row'>&nbsp;</div>				
	</div>
	
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"document.location='./empr.php?tab=request&lvl=list_dmde&view=all!!params_retour!!'\" />
			!!demande_modify!!
		</div>
	</div>
	<div class='row'>
		<div class='left'>
			!!add_actions_list!!
		</div>
	</div>
	<div class='row'></div>
</form>
";
else 
$form_consult_dmde = "
<script src='./includes/javascript/demandes.js' type='text/javascript'></script>
<script src='./includes/javascript/tablist.js' type='text/javascript'></script>
<script src='./includes/javascript/select.js' type='text/javascript'></script>

<form class='form-".$current_module."' id='see_dmde' name='see_dmde' method='post' action=\"./demandes.php?categ=gestion\">
	<h3>!!icone!!!!form_title!!</h3>
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='state' name='state' />
	<div class='form-contenu'>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_theme']." : </label>
				!!theme_dmde!!
			</div>
			<div class='colonne3'>		
				<label class='etiquette'>".$msg['demandes_etat']." : </label>
				!!etat_dmde!!
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_date_dmde']." : </label>
				!!date_dmde!!
			</div>
		</div>
		<div class='row'>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_sujet']." : </label>
				!!sujet_dmde!!
			</div>
			<div class='colonne3'>
			</div>
			<div class='colonne3'>
				<label class='etiquette'>".$msg['demandes_type']." : </label>
				!!type_dmde!!
			</div>
		</div>	
		!!form_linked_record!!
		<div class='row'></div>
		<div class='row'>
			!!champs_perso!!
		</div>
		<div class='row'>&nbsp;</div>
	</div>
	
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"document.location='./empr.php?tab=request&lvl=list_dmde&view=all!!params_retour!!'\" />
			!!demande_modify!!
		</div>
	</div>
	<div class='row'>
		<div class='left'>
			!!add_actions_list!!
		</div>
	</div>
	<div class='row'></div>
</form>
";
$form_liste_docnum ="
<form class='form-".$current_module."' id='liste_action' name='liste_action' method='post'>
	<h3 id='htitle'>".$msg['demandes_liste_docnum']."</h3>
	<input type='hidden' id='act' name='act' />
	<input type='hidden' id='iddemande' name='iddemande' value='!!iddemande!!'/>
	<div class='form-contenu' >
		<div class='row'>
			!!liste_docnum!!	
		</div>
	</div>
	<div class='row'>
		<div class='left'>
			<input type='button' class='bouton' value='".$msg['demandes_retour']."' onClick=\"history.go(-1)\" />
			!!btn_attach!!	
		</div>
		<div class='right'>
			<input type='button' class='bouton' name='btn_chk' id='btn_chk' value='".$msg['tout_cocher_checkbox']."' onClick=\"check_all('liste_action','chk',true);\" />
			<input type='button' class='bouton' name='btn_chk' id='btn_chk' value='".$msg['tout_decocher_checkbox']."' onClick=\"check_all('liste_action','chk',false);\" />
			<input type='button' class='bouton' name='btn_chk' id='btn_chk' value='".$msg['inverser_checkbox']."' onClick=\"inverser('liste_action','chk');\" />
		</div>
	</div>
	
</form>

<script type='text/javascript'>

function check_all(the_form,the_objet,do_check){

	var elts = document.forms[the_form].elements[the_objet+'[]'] ;
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;

	if (elts_cnt) {
		for (var i = 0; i < elts_cnt; i++) {
			elts[i].checked = do_check;
		} 
	} else {
		elts.checked = do_check;
	}
	return true;
}

function inverser(the_form,the_objet){

	var elts = document.forms[the_form].elements[the_objet+'[]'] ;
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;

	if (elts_cnt) {
		for (var i = 0; i < elts_cnt; i++) {
			if(elts[i].checked == true) elts[i].checked = false;
			else elts[i].checked = true;
		} 
	} 
	return true;
}

 function verifChk() {
		
	var elts = document.forms['liste_action'].elements['chk[]'];
	var elts_cnt  = (typeof(elts.length) != 'undefined')
              ? elts.length
              : 0;
	nb_chk = 0;
	if (elts_cnt) {
		for(var i=0; i < elts.length; i++) {
			if (elts[i].checked) nb_chk++;
		}
	} else {
		if (elts.checked) nb_chk++;
	}
	if (nb_chk == 0) {
		var sup = confirm(\"".$msg['demandes_confirm_attach_docnum']."\");
		if(!sup) 
			return false;
		return true;
	}
	
	return true;
}
</script>
";

$form_linked_record = "
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_linked_record']."</label>
		</div>
		<div class='row'>
			<a href='!!linked_record_link!!' title='!!linked_record!!' id='demandes_linked_record'>!!linked_record!!</a>
		</div>
		<input type='hidden' name='linked_record_id' value='!!linked_record_id!!'/>";

$form_consult_linked_record = "
		<div class='row'>
			<label class='etiquette'>".$msg['demandes_linked_record']." : </label>
			<a href='!!linked_record_link!!' title='!!linked_record!!' id='demandes_linked_record'>!!linked_record!!</a>
		</div>";
?>
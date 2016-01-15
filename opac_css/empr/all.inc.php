<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: all.inc.php,v 1.54 2015-10-29 10:56:12 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//R�cup�ration des variables post�es, on en aura besoin pour les liens
$page=$_SERVER[SCRIPT_NAME];


if ($dest=="TABLEAU") {
	//Export excel
	$fname=str_replace(" ","",microtime());
	$fname=str_replace("0.","",$base_path."/temp/".$fname);
	require_once ("$class_path/writeexcel/class.writeexcel_workbook.inc.php");
	require_once ("$class_path/writeexcel/class.writeexcel_worksheet.inc.php");
	$workbook = new writeexcel_workbook($fname);
	$worksheet = $workbook->addworksheet();
	//formats
	$workbook->set_custom_color(12, 00, 204, 255);
	$heading_blue = $workbook->addformat(array('fg_color' => 12));
	$heading_10 = $workbook->addformat(array('bold' => 1, 'size' => 10));
	$heading_12 = $workbook->addformat(array('bold' => 1, 'size' => 12));
} else {
	echo"<form action='empr.php' method='post' name='FormEmpr'>";
	echo"<input name='lvl' value='all' type='hidden'>";	
	echo"<input name='prolonge_id' value='0' type='hidden'>";
	
	// Si click bouton de prolongation, et prolongation autoris�e 
	if($prolonge_id>0 && $opac_pret_prolongation==1){
		//Il faut prolonger un livre
		
		$prolongation = TRUE;
		
		//on recupere les informations du pret 
		$query = "select cpt_prolongation, pret_date, pret_retour, expl_location from pret, exemplaires";
		$query .= " where pret_idexpl=expl_id";
		$query .= " and pret_idexpl=".$prolonge_id." limit 1";
		$result = pmb_mysql_query($query, $dbh);
		$data = pmb_mysql_fetch_array($result);
		$cpt_prolongation = $data['cpt_prolongation']; 
		$pret_date =  $data['pret_date'];
		$date_retour = $data['pret_retour'];
		$expl_location = $data['expl_location'];
	
		$duree_prolongation = $opac_pret_duree_prolongation;
		
		// Limitation simple du pret
		if ($pmb_pret_restriction_prolongation==1) {
	
			$pret_nombre_prolongation = $pmb_pret_nombre_prolongation;
		
		} elseif($pmb_pret_restriction_prolongation==2) {
			
			// Limitation du pret par les quotas
			//Initialisation des quotas pour nombre de prolongations
			$qt = new quota("PROLONG_NMBR_QUOTA");
			//Tableau de passage des param�tres
			$struct["READER"] = $id_empr;
			$struct["EXPL"] = $prolonge_id;
			$pret_nombre_prolongation = $qt -> get_quota_value($struct);
		
			//Initialisation des quotas la dur�e de prolongations
			$qt = new quota("PROLONG_TIME_QUOTA");
			$struct["READER"] = $id_empr;
			$struct["EXPL"] = $prolonge_id;	
			$duree_prolongation = $qt -> get_quota_value($struct);	
		
		}
		
		$today = sql_value("SELECT CURRENT_DATE()");
		$date_prolongation = sql_value("SELECT DATE_ADD('$date_retour', INTERVAL $duree_prolongation DAY)");
		$diff = sql_value("SELECT DATEDIFF('$date_retour','$today')");
		
		if($diff<-$duree_prolongation || $diff>$duree_prolongation) {
			$prolongation = FALSE;
			echo $msg["loan_extend_false"]."<br />";		
		}
			
		if($prolongation==TRUE)	{		
			$cpt_prolongation++;
				
			if ($pmb_utiliser_calendrier) {
				$req_date_calendrier = "select date_ouverture from ouvertures where ouvert=1 and num_location='".$expl_location."' and DATEDIFF(date_ouverture,'$date_prolongation')>=0 order by date_ouverture asc limit 1";
				$res_date_calendrier = pmb_mysql_query($req_date_calendrier);
		
				if (pmb_mysql_num_rows($res_date_calendrier) > 0) {
					$date_prolongation=pmb_mysql_result($res_date_calendrier,0,0);
				}
			}
			// Memorisation de la nouvelle date de prolongation	
			$query = "update pret set cpt_prolongation='".$cpt_prolongation."', pret_retour='".$date_prolongation."', niveau_relance = 0, date_relance = '0000-00-00', printed=0 where pret_idexpl=".$prolonge_id;
			$result = pmb_mysql_query($query, $dbh);
		}	
	
	}
}	
	
// REQUETE SQL

$sql = "SELECT notices_m.notice_id as num_notice_mono, bulletin_id, IF(pret_retour>sysdate(),0,1) as retard, expl_id," ;
$sql.= "date_format(pret_retour, '".$msg["format_date_sql"]."') as aff_pret_retour, pret_retour, "; 
$sql.= "date_format(pret_date, '".$msg["format_date_sql"]."') as aff_pret_date, " ;
$sql.= "trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if(mention_date, concat(' (',mention_date,')') ,if (date_date, concat(' (',date_format(date_date, '".$msg["format_date_sql"]."'),')') ,'')))) as tit, if(notices_m.notice_id, notices_m.notice_id, notices_s.notice_id) as not_id, tdoc_libelle, empr_location, location_libelle ";
$sql.= "FROM (((exemplaires LEFT JOIN notices AS notices_m ON expl_notice = notices_m.notice_id ) ";
$sql.= "        LEFT JOIN bulletins ON expl_bulletin = bulletins.bulletin_id) ";
$sql.= "        LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), ";
$sql.= "        docs_type, docs_location , pret, empr ";
$sql.= "WHERE expl_typdoc = idtyp_doc and pret_idexpl = expl_id  and empr.id_empr = pret.pret_idempr and expl_location = idlocation ";
$sql.= $critere_requete;
$req = pmb_mysql_query($sql) or die("Erreur SQL !<br />".$sql."<br />".pmb_mysql_error()); 
$nb_elements = pmb_mysql_num_rows($req) ;

if (!$dest) {
	if ($lvl=="late") $class_aff_expl="class='liste-expl-empr-late'" ;
	$class_aff_expl="class='liste-expl-empr-all'" ;
	if ($opac_empr_export_loans) {
		echo "<input class=\"bouton\" type=\"button\" value=\"".$msg["print_loans_bt"]."\" name=\"print_loans_bt\" id=\"print_loans_bt\" onClick=\"location.href='empr.php?tab=".$tab."&lvl=".$lvl."&dest=TABLEAU'\">";
	}
}
if ($nb_elements) {
	if (!$dest) {
		echo"<table $class_aff_expl width='100%'>";
		echo "<tr>" ;
		if ($lvl!="late") echo "<th><center>".$msg["empr_late"]."</center></th>" ;
		echo "<th>".$msg["title"]."</th>
			<th>".$msg["author"]."</th>
			<th>".$msg["typdoc_support"]."</th>
			<th><center>".$msg["date_loan"]."</center></th>
			<th><center>".$msg["date_back"]."</center></th>";	
		if($opac_pret_prolongation==1 && $allow_prol) {
			echo "<th><center>".$msg["opac_titre_champ_nb_prolongation"]."</center></th>";
			echo "<th><center>".$msg["opac_titre_champ_prolongation"]."</center></th>";
		}
		echo "</tr>" ;
		$odd_even=1;
		$loc_cours="";
		while ($data = pmb_mysql_fetch_array($req)) { 
			if ($loc_cours != $data['location_libelle']) {
				$colspan=5;
				if ($lvl!="late"){
					$colspan++;
				}
				if($opac_pret_prolongation==1 && $allow_prol){
					$colspan+=2;
				}
				$loc_cours = $data['location_libelle'];
				echo "<tr class='tb_pret_location_row'>
								<td colspan='".$colspan."'>".$msg["expl_header_location_libelle"]." : ".$loc_cours."</td>
							</tr>";
			}
			$id_expl =$data['expl_cb'];
			$titre = $data['tit'];
			
			// **********
			$responsab = array("responsabilites" => array(),"auteurs" => array());  // les auteurs
			$responsab = get_notice_authors($data['not_id']) ;
			
			//$this->responsabilites
			$as = array_search ("0", $responsab["responsabilites"]) ;
			if ($as!== FALSE && $as!== NULL) {
				$auteur_0 = $responsab["auteurs"][$as] ;
				$auteur = new auteur($auteur_0["id"]);
				$mention_resp = $auteur->isbd_entry;
			} else {
				$as = array_keys ($responsab["responsabilites"], "1" ) ;
				$aut1_libelle = array();			
				for ($i = 0 ; $i < count($as) ; $i++) {
					$indice = $as[$i] ;
					$auteur_1 = $responsab["auteurs"][$indice] ;
					$auteur = new auteur($auteur_1["id"]);
					$aut1_libelle[]= $auteur->isbd_entry;
				}
				$mention_resp = implode (", ",$aut1_libelle) ;
			}
			
			$mention_resp ? $auteur = $mention_resp : $auteur="";
				
			// on affiche les r�sultats 
			if ($odd_even==0) {
				$pair_impair="odd";
				$odd_even=1;
			} else if ($odd_even==1) {
					$pair_impair="even";
					$odd_even=0;
			}
			
			if ($data['num_notice_mono']) $tr_javascript=" class='$pair_impair expl-empr-retard' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./index.php?lvl=notice_display&id=".$data['num_notice_mono']."&seule=1';\" style='cursor: pointer' ";
				else $tr_javascript=" class='$pair_impair expl-empr-retard' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./index.php?lvl=bulletin_display&id=".$data['bulletin_id']."';\" style='cursor: pointer' ";
			$deb_ligne = "<tr $tr_javascript>";
			echo $deb_ligne ;
			/* test de date de retour d�pass�e */
			if ($lvl!="late") 
				if ($data['retard']) echo "<td class='expl-empr-retard'><center><b>&times;</b></center></td>";
					else echo "<td>&nbsp;</td>";
			echo "<td>".$titre."</td>";    
			echo "<td>".$auteur."</td>";
			echo "<td>".$data["tdoc_libelle"]."</td>";    
			echo "<td><center>".$data['aff_pret_date']."</center></td>"; 
				
			if ($data['retard']) echo "<td class='expl-empr-retard'><center>".$data['aff_pret_retour']."</center></td>";
				else echo "<td><center>".$data['aff_pret_retour']."</center></td>";
			// Param�tre de l'opac $opac_pret_prolongation autorisant la gestion des prolongations
			if ($opac_pret_prolongation==1 && $allow_prol) {
				$prolongation=TRUE;
				$expl_id = $data['expl_id'] ;
				$query = "select cpt_prolongation, pret_date,pret_retour, expl_location, niveau_relance from pret, exemplaires where expl_id=pret_idexpl and pret_idexpl='".$data['expl_id']."'";
				$result = pmb_mysql_query($query, $dbh);
				$data_expl = pmb_mysql_fetch_array($result);
				$nb_prolongation = $cpt_prolongation = $data_expl['cpt_prolongation'];
				$pret_date =  $data_expl['pret_date'];
				$date_retour= $data_expl['pret_retour'];
				$cpt_prolongation++;
				
				$duree_prolongation=$opac_pret_duree_prolongation;	
				$today=sql_value("SELECT CURRENT_DATE()");
				if ($pmb_pret_restriction_prolongation==0) {
					// Aucune limitation des prolongations
					$prolongation=true;
					$duree_prolongation=$opac_pret_duree_prolongation;	
				} else if ($pmb_pret_restriction_prolongation>0) {
					$pret_nombre_prolongation=$pmb_pret_nombre_prolongation;
					if(($pmb_pret_restriction_prolongation==1) && ($cpt_prolongation>$pret_nombre_prolongation)) {
						// Limitation simple de la prolongation
						$prolongation=FALSE;
					} else if($pmb_pret_restriction_prolongation==2) {
						// Limitation du pret par les quotas
						//Initialisation des quotas pour nombre de prolongations
						$qt = new quota("PROLONG_NMBR_QUOTA");
						//Tableau de passage des param�tres
						$struct["READER"] = $id_empr;
						$struct["EXPL"] = $expl_id;						
						$pret_nombre_prolongation=$qt -> get_quota_value($struct);		
	
						if($cpt_prolongation>$pret_nombre_prolongation) $prolongation=FALSE;
	
						//Initialisation des quotas la dur�e de prolongations
						$qt = new quota("PROLONG_TIME_QUOTA");
						$struct["READER"] = $id_empr;
						$struct["EXPL"] = $expl_id;	
						$duree_prolongation=$qt -> get_quota_value($struct);	
					} // fin if gestion par quotas
				} // fin else if pmb_pret_restriction_prolongation>0
	
				$date_prolongation=sql_value("SELECT DATE_ADD('$date_retour', INTERVAL $duree_prolongation DAY)");
				$diff=sql_value("SELECT DATEDIFF('$date_retour','$today')");
				if($diff<-$duree_prolongation || $diff>$duree_prolongation) {
					$prolongation=FALSE;
				}
				
				$req_date_calendrier = "select date_ouverture from ouvertures where ouvert=1 and num_location='".$data_expl['expl_location']."' and DATEDIFF(date_ouverture,'$date_prolongation')>=0 order by date_ouverture asc limit 1";
				$res_date_calendrier = pmb_mysql_query($req_date_calendrier);
	
				if (pmb_mysql_num_rows($res_date_calendrier)) {
					$date_prolongation=pmb_mysql_result($res_date_calendrier,0,0);
				}
					
				// Verif s'il y a des r�sa et plus d'exemplaire dispo
				if ($prolongation==TRUE) {
					if($data['num_notice_mono'])	$data['bulletin_id']=0; 
					else	$data['num_notice_mono']=0;
					// chercher le premier (par ordre de rang, donc de date de d�but de r�sa, non valid�
					$rqt = 	"SELECT count(1) FROM resa 
							WHERE resa_idnotice='".$data['num_notice_mono']."' AND resa_idbulletin='".$data['bulletin_id']."' 
							AND resa_cb='' AND resa_date_fin='0000-00-00' ";	
					$res= pmb_mysql_query($rqt);
					$nbresa = pmb_mysql_result($res, 0, 0);
					if($nbresa){						
						$rqt="SELECT count(1) FROM exemplaires, docs_statut WHERE expl_statut=idstatut and pret_flag=1 and statut_visible_opac=1 AND expl_notice=".$data['num_notice_mono']." AND expl_bulletin=".$data['bulletin_id']." "; 
						if($pmb_location_reservation) {
							$rqt.=" and expl_location in (select resa_loc from resa_loc where resa_emprloc=".$data['empr_location'].") ";
						}
						$res= pmb_mysql_query($rqt);
						$nbexpl = pmb_mysql_result($res, 0, 0);
						$rqt="SELECT count(1) FROM pret,exemplaires WHERE pret_idexpl=expl_id AND expl_notice=".$data['num_notice_mono']." AND expl_bulletin=".$data['bulletin_id']." "; 
						if($pmb_location_reservation) {
							$rqt.=" and expl_location in (select resa_loc from resa_loc where resa_emprloc=".$data['empr_location'].") ";
						}
						$res= pmb_mysql_query($rqt);
						$nbexpl_en_pret = pmb_mysql_result($res, 0, 0);
						if(($nbexpl-$nbexpl_en_pret) < $nbresa){
							$prolongation=FALSE;
						}
					}					
				}				
	
				echo "<td><center>".$nb_prolongation."/".$pret_nombre_prolongation."</center></td>";
				
				//Blocage des prolongations si relance sur pret, selon param�tre
				if ($opac_pret_prolongation_blocage) {
					if ($data_expl['niveau_relance']!='0') {
						$prolongation=false;
					}
				}
				
				// Proposer le bouton prolongation
				if ($prolongation==TRUE) {	
					// Mettre au format affichable
					$rqt_date = "select date_format('$date_prolongation', '".$msg["format_date_sql"]."') as aff_date_prolongation ";
					$resultatdate = pmb_mysql_query($rqt_date);
					$res = pmb_mysql_fetch_object($resultatdate) ;
					$aff_date_prolongation= $res->aff_date_prolongation;
					// Bouton de prolongation
					if (sql_value("SELECT DATEDIFF('$date_retour','$date_prolongation')") == 0) {
						$prolongation=false;
					}
				}
				
				$js="onmousedown=\"if (event) e=event; else e=window.event; if (e.target) elt=e.target; else elt=e.srcElement; e.cancelBubble = true; if (e.stopPropagation) e.stopPropagation(); return false;\" ";
				if ($prolongation) {
					echo "<td><center><a href='./empr.php?prolongation=$aff_date_prolongation&prolonge_id=$expl_id&tab=loan_reza&lvl=$lvl#empr-loan' $js >$aff_date_prolongation</a></center></td>";
				} else {
					echo "<td style='cursor: default' $js ><center>&nbsp;</center></td>";
				}
		
			} // fin if prolongeable	
			echo "</tr>\n";
	
		} // fin du while
		
		echo "</table>";
		
		
		echo"</form>";
	} elseif ($dest=="TABLEAU") {
		//Titre
		if ($lvl!="late"){
			$worksheet->write(0,0,$msg["empr_loans"],$heading_blue);
		} else {
			$worksheet->write(0,0,$msg["empr_late"],$heading_blue);
		}
		$worksheet->merge_cells(0,0,0,6);
		//Ent�tes
		$line = 2;
		$x=0;
		if ($lvl!="late") {
			$worksheet->write($line,$x,$msg["empr_late"],$heading_10);
			$x++;
		}
		$worksheet->write($line,$x,$msg["title"],$heading_10);
		$worksheet->write($line,$x+1,$msg["authors"],$heading_10);
		$worksheet->write($line,$x+2,$msg["typdoc_support"],$heading_10);
		$worksheet->write($line,$x+3,$msg["date_loan"],$heading_10);
		$worksheet->write($line,$x+4,$msg["date_back"],$heading_10);
		if ($opac_pret_prolongation==1 && $allow_prol) {
			$worksheet->write($line,$x+5,$msg["opac_titre_champ_nb_prolongation"],$heading_10);
		}
		//Valeurs
		$loc_cours="";
		while ($data = pmb_mysql_fetch_array($req)) {
			$line++;
			$x=0;
			if ($loc_cours != $data['location_libelle']) {
				$loc_cours = $data['location_libelle'];
				$worksheet->write($line,$x,$msg["expl_header_location_libelle"]." : ".$loc_cours,$heading_12);
				$line++;
			}
			$id_expl =$data['expl_cb'];
			$titre = $data['tit'];
			$responsab = array("responsabilites" => array(),"auteurs" => array());  // les auteurs
			$responsab = get_notice_authors($data['not_id']) ;
			$as = array_search ("0", $responsab["responsabilites"]) ;
			if ($as!== FALSE && $as!== NULL) {
				$auteur_0 = $responsab["auteurs"][$as] ;
				$auteur = new auteur($auteur_0["id"]);
				$mention_resp = $auteur->isbd_entry;
			} else {
				$as = array_keys ($responsab["responsabilites"], "1" ) ;
				$aut1_libelle = array();
				for ($i = 0 ; $i < count($as) ; $i++) {
					$indice = $as[$i] ;
					$auteur_1 = $responsab["auteurs"][$indice] ;
					$auteur = new auteur($auteur_1["id"]);
					$aut1_libelle[]= $auteur->isbd_entry;
				}
				$mention_resp = implode (", ",$aut1_libelle) ;
			}
			$mention_resp ? $auteur = $mention_resp : $auteur="";
			if ($lvl!="late") {
				if ($data['retard']) {
					$worksheet->write($line,$x,"x");
				}
				$x++;
			}
			$worksheet->write($line,$x,$titre);
			$worksheet->write($line,$x+1,$auteur);
			$worksheet->write($line,$x+2,$data["tdoc_libelle"]);
			$worksheet->write($line,$x+3,$data['aff_pret_date']);
			$worksheet->write($line,$x+4,$data['aff_pret_retour']);
			
			if ($opac_pret_prolongation==1 && $allow_prol) {
				$prolongation=TRUE;
				$expl_id = $data['expl_id'] ;
				$query = "select cpt_prolongation, pret_date,pret_retour, expl_location from pret, exemplaires where expl_id=pret_idexpl and pret_idexpl='".$data['expl_id']."'";
				$result = pmb_mysql_query($query, $dbh);
				$data_expl = pmb_mysql_fetch_array($result);
				$nb_prolongation = $cpt_prolongation = $data_expl['cpt_prolongation'];
				$cpt_prolongation++;
			
				$duree_prolongation=$opac_pret_duree_prolongation;
				$today=sql_value("SELECT CURRENT_DATE()");
				if ($pmb_pret_restriction_prolongation>0) {
					$pret_nombre_prolongation=$pmb_pret_nombre_prolongation;
					if($pmb_pret_restriction_prolongation==2) {
						// Limitation du pret par les quotas
						//Initialisation des quotas pour nombre de prolongations
						$qt = new quota("PROLONG_NMBR_QUOTA");
						//Tableau de passage des param�tres
						$struct["READER"] = $id_empr;
						$struct["EXPL"] = $expl_id;
						$pret_nombre_prolongation=$qt -> get_quota_value($struct);
					}
				}
				$worksheet->write($line,$x+5,$nb_prolongation."/".$pret_nombre_prolongation);
			}
		}
	} 
	
} else { // fin du if nb_elements
	switch($lvl) {
		case 'all':	
			if(!$dest){
				print '<br><br><span class="noLoan">'.$msg["empr_no_loan"].'</span>' ;
			}elseif ($dest=="TABLEAU") {
				$worksheet->write(0,0,$msg["empr_no_loan"],$heading_blue);
			}
			break;
		case 'late':
			if(!$dest){
				print '<br><br>'.$msg["empr_no_late"] ;
			}elseif ($dest=="TABLEAU") {
				$worksheet->write(0,0,$msg["empr_no_late"],$heading_blue);
			}
			break;
	}
}
if($opac_show_group_checkout) aff_pret_groupes();

if(file_exists($base_path."/empr/all_extended.inc.php"))require_once($base_path."/empr/all_extended.inc.php");

if ($dest=="TABLEAU") {
	$workbook->close();
	header("Content-Type: application/x-msexcel; name=\"empr.xls"."\"");
	header("Content-Disposition: inline; filename=\"empr.xls"."\"");
	$fh=fopen($fname, "rb");
	fpassthru($fh);
	unlink($fname);
	die();
}

function aff_pret_groupes(){
	global $msg,$id_empr,$lvl,$dbh;
	global $opac_pret_prolongation,$opac_pret_duree_prolongation, $allow_prol;
	global $dest,$worksheet,$line;
	global $heading_blue,$heading_10,$heading_12;
	
	$req_groupes="SELECT * from groupe where resp_groupe=$id_empr order by libelle_groupe";
	$res = pmb_mysql_query($req_groupes);		

	while ($r_goupe = pmb_mysql_fetch_object($res)) { 	
		if ($lvl=="late"){
			$titre_goup=sprintf($msg['empr_group_late'],$r_goupe->libelle_groupe);
			$class_aff_expl="class='liste-expl-empr-late'" ;
			$critere_requete=" AND pret_retour < '".date('Y-m-d')."' ORDER BY location_libelle, empr_nom, empr_prenom, pret_retour";
		}else{
			$titre_goup=sprintf($msg['empr_group_loans'],$r_goupe->libelle_groupe);		
			$class_aff_expl="class='liste-expl-empr-all'" ;
			$critere_requete=" ORDER BY location_libelle, empr_nom, empr_prenom, pret_retour";
		}
		
		$sql = "SELECT notices_m.notice_id as num_notice_mono, bulletin_id, IF(pret_retour>sysdate(),0,1) as retard, expl_id, empr.id_empr as emprunteur, " ;
		$sql.= "date_format(pret_retour, '".$msg["format_date_sql"]."') as aff_pret_retour, pret_retour, "; 
		$sql.= "date_format(pret_date, '".$msg["format_date_sql"]."') as aff_pret_date, " ;
		$sql.= "trim(concat(ifnull(notices_m.tit1,''),ifnull(notices_s.tit1,''),' ',ifnull(bulletin_numero,''), if(mention_date, concat(' (',mention_date,')') ,if (date_date, concat(' (',date_format(date_date, '".$msg["format_date_sql"]."'),')') ,'')))) as tit, if(notices_m.notice_id, notices_m.notice_id, notices_s.notice_id) as not_id, tdoc_libelle, location_libelle ";
		$sql.= "FROM (((exemplaires LEFT JOIN notices AS notices_m ON expl_notice = notices_m.notice_id ) ";
		$sql.= "        LEFT JOIN bulletins ON expl_bulletin = bulletins.bulletin_id) ";
		$sql.= "        LEFT JOIN notices AS notices_s ON bulletin_notice = notices_s.notice_id), ";
		$sql.= "        docs_type, docs_location , pret, empr,empr_groupe  ";
		$sql.= "WHERE expl_typdoc = idtyp_doc and pret_idexpl = expl_id  and empr.id_empr = pret.pret_idempr and empr_groupe.empr_id = empr.id_empr and expl_location = idlocation and groupe_id=". $r_goupe->id_groupe;
		$sql.= $critere_requete;
	
		$req = pmb_mysql_query($sql) or die("Erreur SQL !<br />".$sql."<br />".pmb_mysql_error()); 
		$nb_elements = pmb_mysql_num_rows($req) ;
		
		if ($nb_elements) {	
			if (!$dest) {
				echo "<br>";	
				echo"<h3><span>".$titre_goup."</span></h3>";
				echo"<table $class_aff_expl width='100%'>";
				echo "<tr>" ;
				if ($lvl!="late") echo "<th><center>".$msg["empr_late"]."</center></th>" ;
				echo " <th>".$msg["extexpl_emprunteur"]."</th>
					  <th>".$msg["title"]."</th>					
					  <th>".$msg["typdoc_support"]."</th>					 
					  <th><center>".$msg["date_loan"]."</center></th>
					  <th><center>".$msg["date_back"]."</center></th>";
				echo "</tr>" ;
				$odd_even=1;
				$loc_cours="";
				while ($data = pmb_mysql_fetch_array($req)) {
					if ($loc_cours != $data['location_libelle']) {
						$loc_cours = $data['location_libelle'];
						echo "<tr class='tb_pret_location_row'>
								<td colspan='".($lvl!="late"?"6":"5")."'>".$msg["expl_header_location_libelle"]." : ".$loc_cours."</td>
							</tr>";
					} 
					$id_expl =$data['expl_cb'];
	
					// on affiche les r�sultats 
					if ($odd_even==0) {
						$pair_impair="odd";
						$odd_even=1;
					} else if ($odd_even==1) {
						$pair_impair="even";
						$odd_even=0;
					}
					
					if ($data['num_notice_mono']) $tr_javascript=" class='$pair_impair' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./index.php?lvl=notice_display&id=".$data['num_notice_mono']."&seule=1';\" style='cursor: pointer' ";
						else $tr_javascript=" class='$pair_impair' onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\" onmousedown=\"document.location='./index.php?lvl=bulletin_display&id=".$data['bulletin_id']."';\" style='cursor: pointer' ";
					$deb_ligne = "<tr $tr_javascript>";
					echo $deb_ligne ;
					/* test de date de retour d�pass�e */
					if ($lvl!="late") 
						if ($data['retard']) echo "<td class='expl-empr-retard'><center><b>&times;</b></center></td>";
						else echo "<td>&nbsp;</td>";
					$empr=get_info_empr($data['emprunteur']);
					echo "<td>".$empr['nom']." ".$empr['prenom']."</td>";
					echo "<td>".$data['tit']."</td>";    
					echo "<td>".$data["tdoc_libelle"]."</td>";
					echo "<td><center>".$data['aff_pret_date']."</center></td>"; 
						
					if ($data['retard']) echo "<td class='expl-empr-retard'><center>".$data['aff_pret_retour']."</center></td>";
						else echo "<td><center>".$data['aff_pret_retour']."</center></td>";
	
					echo "</tr>\n";
			
				} // fin du while
				
				echo "</table>";
			} else {
				$line+=2;
				$x=0;
				//Titre
				$worksheet->write($line,$x,$titre_goup,$heading_blue);
				$worksheet->merge_cells($line,$x,$line,$x+6);
				//Ent�tes
				$line+=2;
				$x=0;
				if ($lvl!="late") {
					$worksheet->write($line,$x,$msg["empr_late"],$heading_10);
					$x++;
				}
				$worksheet->write($line,$x,$msg["extexpl_emprunteur"],$heading_10);
				$worksheet->write($line,$x+1,$msg["title"],$heading_10);
				$worksheet->write($line,$x+2,$msg["typdoc_support"],$heading_10);
				$worksheet->write($line,$x+3,$msg["date_loan"],$heading_10);
				$worksheet->write($line,$x+4,$msg["date_back"],$heading_10);
				//Valeurs
				$loc_cours="";
				while ($data = pmb_mysql_fetch_array($req)) {
					$x=0;
					$line++;
					if ($loc_cours != $data['location_libelle']) {
						$loc_cours = $data['location_libelle'];
						$worksheet->write($line,$x,$msg["expl_header_location_libelle"]." : ".$loc_cours,$heading_12);
						$line++;
					}
					if ($lvl!="late") {
						if ($data['retard']) {
							$worksheet->write($line,$x,"x");
						}
						$x++;
					}
					$empr=get_info_empr($data['emprunteur']);
					$worksheet->write($line,$x,$empr['nom']." ".$empr['prenom']);
					$worksheet->write($line,$x+1,$data['tit']);
					$worksheet->write($line,$x+2,$data["tdoc_libelle"]);
					$worksheet->write($line,$x+3,$data['aff_pret_date']);
					$worksheet->write($line,$x+4,$data['aff_pret_retour']);
				}
			}
		}
	}
	
}

function get_info_empr($id){
	$req="SELECT * FROM empr, docs_location 
	where id_empr=$id and empr_location=idlocation ";
	
	$info_eleve=array();
	$resultat=pmb_mysql_query($req);
	if($r=pmb_mysql_fetch_object($resultat)) {
		$info_eleve['id']=$id;
		$info_eleve['nom']=$r->empr_nom;
		$info_eleve['prenom']=$r->empr_prenom;
		$info_eleve['location_libelle']=$r->location_libelle;
		
	}
	return $info_eleve;
}

function sql_value($rqt) {
	$result=pmb_mysql_query($rqt);
	$row = pmb_mysql_fetch_row($result);
	return $row[0];
}

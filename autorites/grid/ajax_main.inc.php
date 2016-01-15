<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ajax_main.inc.php,v 1.4 2015-12-10 10:04:11 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once($class_path."/encoding_normalize.class.php");

switch ($action) {
	case "save":
		$datas = json_decode(encoding_normalize::utf8_normalize(stripslashes($datas)));
		if(is_object($datas)){
			if(!$datas->authType){
				print encoding_normalize::json_encode(array('status'=>false));
				return false;
			}
			if($datas->all_backbones){
				$flag = true;
				$backbones_values = permute_backbone($datas->backbone_table);
				foreach($backbones_values[0] as $permutation){
					$flag = save_grid($datas->authType, $permutation, $datas->zones);
				}
			}else{
				$flag = save_grid($datas->authType, $datas->authSign, $datas->zones);
			}
			if($flag){
				print encoding_normalize::json_encode(array('status'=>true));
				return true;
			}
			return false;
		}else{
			print encoding_normalize::json_encode(array('status'=>false));
		}
		break;
	case "get_datas":
		$datas = json_decode(stripslashes($datas));
		$query = 'select grille_auth_descr_format from grilles_auth
					where grille_auth_type="'.addslashes($datas->authType).'"
					and grille_auth_filter="'.addslashes($datas->authSign).'"';
		$result = pmb_mysql_query($query);
		if($result && pmb_mysql_num_rows($result)) {
			$datas = pmb_mysql_result($result,0);
			print encoding_normalize::json_encode(array('status'=>true, 'datas'=> $datas));
		} else {
			print encoding_normalize::json_encode(array('status'=>false));
		}
		break;
	default:
		ajax_http_send_error("404 Not Found","Invalid command : ".$action);
		break;
}	

function save_grid($auth_type, $auth_sign, $zones){
	global $dbh;
	$query = 'select grille_auth_type from grilles_auth
					where grille_auth_type="'.addslashes($auth_type).'"
					and grille_auth_filter="'.addslashes($auth_sign).'"';
	$result = pmb_mysql_query($query);
	if($result && pmb_mysql_num_rows($result)) {
		$requete = 'update grilles_auth set
					grille_auth_type="'.addslashes($auth_type).'",
					grille_auth_filter="'.addslashes($auth_sign).'",
					grille_auth_descr_format="'.addslashes(json_encode($zones)).'"
					where grille_auth_type="'.addslashes($auth_type).'"
					and	grille_auth_filter="'.addslashes($auth_sign).'"';
	} else {
		$requete = 'insert into grilles_auth set
					grille_auth_type="'.addslashes($auth_type).'",
					grille_auth_filter="'.addslashes($auth_sign).'",
					grille_auth_descr_format="'.addslashes(json_encode($zones)).'"';
	}
	return pmb_mysql_query($requete);
}

function permute_backbone($backbone_values){
	if(count($backbone_values) > 1){
		$newFirstLevel = array();
		for($i=0 ; $i<count($backbone_values[0]) ; $i++){
			for($j=0 ; $j<count($backbone_values[1]) ; $j++){
				 $newFirstLevel[] = $backbone_values[0][$i].'_'.$backbone_values[1][$j];
			}
		}
		array_splice($backbone_values, 0, 2, array($newFirstLevel));
		return permute_backbone($backbone_values);
	}
	return $backbone_values;
}

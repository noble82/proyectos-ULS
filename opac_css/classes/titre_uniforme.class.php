<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: titre_uniforme.class.php,v 1.26 2015-11-06 15:13:22 dgoron Exp $
if (stristr ( $_SERVER ['REQUEST_URI'], ".class.php" ))
	die ( "no access" );

require_once($class_path."/notice.class.php");
require_once($class_path."/authorities_collection.class.php");
require_once ("$class_path/marc_table.class.php");

global $oeuvre_link;
if(!isset($oeuvre_link)){
	$oeuvre_link= new marc_list('oeuvre_link');
}

global $mc_oeuvre_type;
if (! isset ( $mc_oeuvre_type )) {
	$mc_oeuvre_type = new marc_list ( 'oeuvre_type' );
}

global $mc_oeuvre_nature;
if (! isset ( $mc_oeuvre_nature )) {
	$mc_oeuvre_nature = new marc_list ( 'oeuvre_nature' );
}

/*
 * Classe recopiée de la gestion, allégée des méthodes inutiles en OPAC
 */
class titre_uniforme {
	
	// ---------------------------------------------------------------
	// propriétés de la classe
	// ---------------------------------------------------------------
	public $id; // MySQL id in table 'titres_uniformes'
	public $name; // titre_uniforme name
	public $tonalite; // tonalite de l'oeuvre musicale
	public $tonalite_marclist; // tonalite de l'oeuvre musicale (valeur issue de la liste music_key.xml)
	public $comment; // Commentaire, peut contenir du HTML
	public $import_denied = 0; // booléen pour interdire les modification depuis un import d'autorités
	public $form; // catégorie à laquelle appartient l'oeuvre (roman, pièce de théatre, poeme, ...)
	public $form_marclist; // catégorie à laquelle appartient l'oeuvre (roman, pièce de théatre, poeme, ...) (valeur issue de la liste music_form.xml)
	public $date; // date de création originelle de l'oeuvre (telle que saisie)
	public $date_date; // date formatée yyyy-mm-dd
	public $characteristic; // caractéristique permettant de distinguer une oeuvre d'une autre peuvre portant le même titre
	public $intended_termination; // complétude d'une oeuvre est finie ou se poursuit indéfiniment
	public $intended_audience; // categorie de personnes à laquelle l'oeuvre s'adresse
	public $context; // contexte historique, social, intellectuel, artistique ou autre au sein duquel l'oeuvre a été conçue
	public $coordinates; // coordonnees d'une oeuvre géographique (degrés, minutes et secondes de longitude et latitude ou angles de déclinaison et d'ascension des limiets de la zone représentée)
	public $equinox; // année de référence pour une carte ou un modèle céleste
	public $subject; // contenu de l'oeuvre et sujets qu'elle aborde
	public $place; // pays ou juridiction territoriale dont l'oeuvre est originaire
	public $history; // informations concernant l'histoire de l'oeuvre
	public $num_author; // identifiant de l'auteur principal de l'oeuvre
	public $display; // usable form for displaying ( _name_ (_date_) / _author_name_ _author_rejete_ )
	public $tu_isbd; // affichage isbd du titre uniforme AFNOR Z 44-061 (1986),
	public $responsabilites = array (); // Auteurs répétables
	public $enrichment = null; // Enrichissements
	public static $marc_key;
	public static $marc_form;
	public $oeuvre_nature; // Nature de l'oeuvre
	public $oeuvre_nature_name; // Label de la Nature de l'oeuvre
	public $oeuvre_nature_nature; // Nature de la nature de l'oeuvre	
	public $oeuvre_type; // Type de l'oeuvre
	public $oeuvre_type_name; // Label du Type de l'oeuvre
	public $oeuvre_expressions; // tableau Expression de l'oeuvre
	public $other_links; // tableau des oeuvres liées
	public $oeuvre_events; // Evènements de l'oeuvre
	/**
	 * @var titre_uniforme
	 */
	private $oeuvre_expressions_datas; // Tableau des données des oeuvres dont le titre uniforme est l'expression
	/**
	 * @var titre_uniforme
	 */
	private $oeuvre_events_datas; // Tableau des données des évenement de l'oeuvre
	/**
	 * @var titre_uniforme
	 */
	private $other_links_datas; // Tableau des données des oeuvres liées
	                     // ---------------------------------------------------------------
	                     // titre_uniforme($id) : constructeur
	                     // ---------------------------------------------------------------
	function titre_uniforme($id = 0, $recursif = 0) {
		if ($id) {
			// on cherche à atteindre une notice existante
			$this->recursif = $recursif;
			$this->id = $id;
			$this->getData ();
		} else {
			// la notice n'existe pas
			$this->id = 0;
			$this->getData ();
		}
	}
	
	// ---------------------------------------------------------------
	// getData() : récupération infos titre_uniforme
	// ---------------------------------------------------------------
	function getData() {
		global $dbh, $msg;
		global $mc_oeuvre_type, $mc_oeuvre_nature;
		
		$this->name = '';
		$this->tonalite = '';
		$this->tonalite_marclist = '';
		$this->comment = '';
		$this->distrib = array ();
		$this->ref = array ();
		$this->subdiv = array ();
		$this->import_denied = 0;
		$this->form = '';
		$this->form_marclist = '';
		$this->date = '';
		$this->date_date = '';
		$this->characteristic = '';
		$this->intended_termination = '';
		$this->intended_audience = '';
		$this->context = '';
		$this->coordinates = '';
		$this->equinox = '';
		$this->subject = '';
		$this->place = '';
		$this->history = '';
		$this->num_author = '';
		$this->display = '';
		$this->oeuvre_nature = '';
		$this->oeuvre_nature_nature = '';
		$this->oeuvre_type = '';
		$this->responsabilites ["responsabilites"] = array ();
		if ($this->id) {
			$requete = "SELECT * FROM titres_uniformes WHERE tu_id='" . addslashes ( $this->id ) . "' LIMIT 1 ";
			$result = @pmb_mysql_query ( $requete, $dbh );
			if (pmb_mysql_num_rows ( $result )) {
				$temp = pmb_mysql_fetch_object ( $result );
				$this->id = $temp->tu_id;
				$this->name = $temp->tu_name;
				$this->tonalite = $temp->tu_tonalite;
				$this->tonalite_marclist = $temp->tu_tonalite_marclist;
				$this->comment = $temp->tu_comment;
				$this->import_denied = $temp->tu_import_denied;
				$this->form = $temp->tu_forme;
				$this->form_marclist = $temp->tu_forme_marclist;
				$this->date = $temp->tu_date;
				$this->date_date = $temp->tu_date_date;
				$this->characteristic = $temp->tu_caracteristique;
				$this->intended_termination = $temp->tu_completude;
				$this->intended_audience = $temp->tu_public;
				$this->context = $temp->tu_contexte;
				$this->coordinates = $temp->tu_coordonnees;
				$this->equinox = $temp->tu_equinoxe;
				$this->subject = $temp->tu_sujet;
				$this->place = $temp->tu_lieu;
				$this->history = $temp->tu_histoire;
				$this->num_author = $temp->tu_num_author;
				$this->oeuvre_nature = $temp->tu_oeuvre_nature;
				$this->oeuvre_nature_nature = $temp->tu_oeuvre_nature_nature;
				$this->oeuvre_type = $temp->tu_oeuvre_type;
				
				$this->oeuvre_type_name = $mc_oeuvre_type->table [$this->oeuvre_type];
				$this->oeuvre_nature_name = $mc_oeuvre_nature->table [$this->oeuvre_nature];
				
				$this->get_oeuvre_links();
				$this->get_oeuvre_events();
				
				$requete = "SELECT * FROM tu_distrib WHERE distrib_num_tu='$this->id' order by distrib_ordre";
				$result = pmb_mysql_query ( $requete, $dbh );
				if (pmb_mysql_num_rows ( $result )) {
					while ( ($param = pmb_mysql_fetch_object ( $result )) ) {
						$this->distrib [] ["label"] = $param->distrib_name;
					}
				}
				$requete = "SELECT *  FROM tu_ref WHERE ref_num_tu='$this->id' order by ref_ordre";
				$result = pmb_mysql_query ( $requete, $dbh );
				if (pmb_mysql_num_rows ( $result )) {
					while ( ($param = pmb_mysql_fetch_object ( $result )) ) {
						$this->ref [] ["label"] = $param->ref_name;
					}
				}
				$requete = "SELECT *  FROM tu_subdiv WHERE subdiv_num_tu='$this->id' order by subdiv_ordre";
				$result = pmb_mysql_query ( $requete, $dbh );
				if (pmb_mysql_num_rows ( $result )) {
					while ( ($param = pmb_mysql_fetch_object ( $result )) ) {
						$this->subdiv [] ["label"] = $param->subdiv_name;
					}
				}
				
				$this->display = $this->name;
				if ($this->date) {
					$this->display .= " (" . $this->date . ")";
				}
				
				// recuperation des responsabilites pour l'affichage
				$this->responsabilites = $this->get_authors ( $this->id );
				
				// $as = array_keys ($this->responsabilites["responsabilites"], "0" ) ;
				// if(count($as))$this->display.= ", ";
				// $libelle = array();
				// for ($i = 0 ; $i < count($as) ; $i++) {
				// $indice = $as[$i] ;
				// $auteur_0 = $this->responsabilites["auteurs"][$indice] ;
				// $auteur = new auteur($auteur_0["id"]);
				
				// if($i>0)$this->display.= " / "; // entre auteurs
				
				// $libelle[] = $auteur->display;
				// $this->display.= $auteur->rejete." ".$auteur->name;
				// }
				
				if (count ( $this->responsabilites ["auteurs"] )) {
					$this->display .= ", ";
					$libelle = array ();
					foreach ( $this->responsabilites ["auteurs"] as $id => $responsable ) {
						if (is_object ( $responsable ["objet"] )) {
							if ($id > 0)
								$this->display .= " / "; // entre auteurs
							$libelle [] = $auteur->display;
							$this->display .= $responsable ["objet"]->rejete . " " . $responsable ["objet"]->name;
						}
					}
					
					$this->libelle = implode ( "; ", $libelle );
				}
			} else {
				// pas trouvé avec cette clé
				$this->id = 0;
			}
		}
	}
	function get_authors($id = 0) {
		global $dbh;
		
		$responsabilites = array ();
		$auteurs = array ();
		$res ["responsabilites"] = array ();
		$res ["auteurs"] = array ();
		
		$rqt = "select author_id, responsability_tu_fonction, responsability_tu_type ";
		$rqt .= "from responsability_tu, authors where responsability_tu_num='$id' and responsability_tu_author_num=author_id order by responsability_tu_type, responsability_tu_ordre ";
		
		$res_sql = pmb_mysql_query ( $rqt, $dbh );
		while ( $resp_tu = pmb_mysql_fetch_object ( $res_sql ) ) {
			$responsabilites [] = $resp_tu->responsability_tu_type;
			$auteurs [] = array (
					'id' => $resp_tu->author_id,
					'fonction' => $resp_tu->responsability_tu_fonction,
					'responsability' => $resp_tu->responsability_tu_type,
					'objet' => authorities_collection::get_authority('author', $resp_tu->author_id)
			);
		}
		$res ["responsabilites"] = $responsabilites;
		$res ["auteurs"] = $auteurs;
		
		return $res;
	}
	
	// ---------------------------------------------------------------
	// print_resume($level) : affichage d'informations sur le titre uniforme
	// ---------------------------------------------------------------
	function print_resume($level = 2) {
		global $msg, $charset;
		if (! $this->id)
			return;
			
			// adaptation par rapport au niveau de détail souhaité
		switch ($level) {
			// case x :
			case 2 :
			default :
				global $titre_uniforme_level2_display;
				$titre_uniforme_display = $titre_uniforme_level2_display;
				break;
		}
		$print = $titre_uniforme_display;
		
		$print_distrib = $print_ref = $print_subdiv = '';
		foreach ( $this->distrib as $field ) {
			if ($print_distrib)
				$print_distrib .= "; ";
			$print_distrib .= $field ["label"];
		}
		foreach ( $this->ref as $field ) {
			if ($print_ref)
				$print_ref .= "; ";
			$print_ref .= $field ["label"];
		}
		foreach ( $this->subdiv as $field ) {
			if ($print_subdiv)
				$print_subdiv .= "; ";
			$print_subdiv .= $field ["label"];
		}
		
		// remplacement des champs
		$print = str_replace ( "!!id!!", $this->id, $print );
		$print = str_replace ( "!!name!!", $this->name, $print );
		
		$auteurs = "";
		if (isset ( $this->responsabilites ["auteurs"] ) && count ( $this->responsabilites ["auteurs"] )) {
			foreach ( $this->responsabilites ["auteurs"] as $id => $responsable ) {
				if (is_object ( $responsable ["objet"] )) {
					
					if ($id > 0)
						$auteurs .= " / "; // entre auteurs
					$auteurs .= "<a href='index.php?lvl=author_see&id=" . $responsable ["objet"]->id . "'>" . htmlentities ( $responsable ["objet"]->display, ENT_QUOTES, $charset ) . "</a>";
				}
			}
		}
		
		$print = str_replace ( "!!auteur!!", ($auteurs ? "<p>" . $msg ["aut_oeuvre_form_auteur"] . " : " . $auteurs . "</p>" : ""), $print );
		$print = str_replace ( "!!forme!!", ($this->form ? "<p>" . $msg ["aut_oeuvre_form_forme"] . " : " . htmlentities ( $this->form, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!forme_list!!", ($this->get_form_label () ? "<p>" . $msg ["aut_oeuvre_form_forme_list"] . " : " . htmlentities ( $this->get_form_label (), ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!date!!", ($this->date ? "<p>" . $msg ["aut_oeuvre_form_date"] . " : " . htmlentities ( $this->date, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!sujet!!", ($this->subject ? "<p>" . $msg ["aut_oeuvre_form_sujet"] . " : " . htmlentities ( $this->subject, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!lieu!!", ($this->place ? "<p>" . $msg ["aut_oeuvre_form_lieu"] . " : " . htmlentities ( $this->place, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$completude = '';
		if ($this->intended_termination == 1) {
			$completude = $msg['aut_oeuvre_form_completude_finished'];
		} elseif ($this->intended_termination == 2) {
			$completude = $msg['aut_oeuvre_form_completude_infinite'];
		}
		$print = str_replace ( "!!completude!!", ($completude ? "<p>" . $msg ["aut_oeuvre_form_completude"] . " : " . htmlentities ( $completude, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!public!!", ($this->intended_audience ? "<p>" . $msg ["aut_oeuvre_form_public"] . " : " . htmlentities ( $this->intended_audience, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!histoire!!", ($this->history ? "<p>" . $msg ["aut_oeuvre_form_histoire"] . " : " . htmlentities ( $this->history, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!contexte!!", ($this->context ? "<p>" . $msg ["aut_oeuvre_form_contexte"] . " : " . htmlentities ( $this->context, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!distribution!!", ($print_distrib ? "<p>" . $msg ["aut_oeuvre_form_distribution"] . " : " . htmlentities ( $print_distrib, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!reference!!", ($print_ref ? "<p>" . $msg ["aut_oeuvre_form_reference"] . " : " . htmlentities ( $print_ref, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!tonalite!!", ($this->tonalite ? "<p>" . $msg ["aut_oeuvre_form_tonalite"] . " : " . htmlentities ( $this->tonalite, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!tonalite_list!!", ($this->get_key_label () ? "<p>" . $msg ["aut_oeuvre_form_tonalite_list"] . " : " . htmlentities ( $this->get_key_label (), ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!subdivision!!", ($print_subdiv ? "<p>" . $msg ["aut_oeuvre_form_subdivision"] . " : " . htmlentities ( $print_subdiv, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!coordonnees!!", ($this->coordinates ? "<p>" . $msg ["aut_oeuvre_form_coordonnees"] . " : " . htmlentities ( $this->coordinates, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!equinoxe!!", ($this->equinox ? "<p>" . $msg ["aut_oeuvre_form_equinoxe"] . " : " . htmlentities ( $this->equinox, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!caracteristique!!", ($this->characteristic ? "<p>" . $msg ["aut_oeuvre_form_caracteristique"] . " : " . htmlentities ( $this->characteristic, ENT_QUOTES, $charset ) . "</p>" : ""), $print );
		$print = str_replace ( "!!aut_comment!!", $this->comment, $print );
		
		return $print;
	}
	function gen_input_selection($label, $form_name, $item, $values, $what_sel, $class = 'saisie-80em') {
		global $msg;
		$select_prop = "scrollbars=yes, toolbar=no, dependent=yes, resizable=yes";
		$link = "'./select.php?what=$what_sel&caller=$form_name&p1=f_" . $item . "_code!!num!!&p2=f_" . $item . "!!num!!&deb_rech='+" . pmb_escape () . "(this.form.f_" . $item . "!!num!!.value), '$what_sel', 400, 400, -2, -2, '$select_prop'";
		$size_item = strlen ( $item ) + 2;
		$script_js = "
		<script>
		function fonction_selecteur_" . $item . "() {
			var nom='f_" . $item . "';
	        name=this.getAttribute('id').substring(4);  
			name_id = name.substr(0,nom.length)+'_code'+name.substr(nom.length);
			openPopUp('./select.php?what=$what_sel&caller=$form_name&p1='+name_id+'&p2='+name, '$what_sel', 400, 400, -2, -2, '$select_prop');
	        
	    }
	    function fonction_raz_" . $item . "() {
	        name=this.getAttribute('id').substring(4);
			name_id = name.substr(0,$size_item)+'_code'+name.substr($size_item);
	        document.getElementById(name).value='';
			document.getElementById(name_id).value='';
	    }
	    function add_" . $item . "() {
	        template = document.getElementById('add" . $item . "');
	        " . $item . "=document.createElement('div');
	        " . $item . ".className='row';
	
	        suffixe = eval('document." . $form_name . ".max_" . $item . ".value')
	        nom_id = 'f_" . $item . "'+suffixe
	        f_" . $item . " = document.createElement('input');
	        f_" . $item . ".setAttribute('name',nom_id);
	        f_" . $item . ".setAttribute('id',nom_id);
	        f_" . $item . ".setAttribute('type','text');
	        f_" . $item . ".className='$class';
	        f_" . $item . ".setAttribute('value','');
			f_" . $item . ".setAttribute('completion','" . $item . "');
	        
			id = 'f_" . $item . "_code'+suffixe
			f_" . $item . "_code = document.createElement('input');
			f_" . $item . "_code.setAttribute('name',id);
	        f_" . $item . "_code.setAttribute('id',id);
	        f_" . $item . "_code.setAttribute('type','hidden');
			f_" . $item . "_code.setAttribute('value','');
	 
	        del_f_" . $item . " = document.createElement('input');
	        del_f_" . $item . ".setAttribute('id','del_f_" . $item . "'+suffixe);
	        del_f_" . $item . ".onclick=fonction_raz_" . $item . ";
	        del_f_" . $item . ".setAttribute('type','button');
	        del_f_" . $item . ".className='bouton';
	        del_f_" . $item . ".setAttribute('readonly','');
	        del_f_" . $item . ".setAttribute('value','" . $msg ["raz"] . "');
	
	        sel_f_" . $item . " = document.createElement('input');
	        sel_f_" . $item . ".setAttribute('id','sel_f_" . $item . "'+suffixe);
	        sel_f_" . $item . ".setAttribute('type','button');
	        sel_f_" . $item . ".className='bouton';
	        sel_f_" . $item . ".setAttribute('readonly','');
	        sel_f_" . $item . ".setAttribute('value','" . $msg ["parcourir"] . "');
	        sel_f_" . $item . ".onclick=fonction_selecteur_" . $item . ";
	
	        " . $item . ".appendChild(f_" . $item . ");
			" . $item . ".appendChild(f_" . $item . "_code);
	        space=document.createTextNode(' ');
	        " . $item . ".appendChild(space);
	        " . $item . ".appendChild(del_f_" . $item . ");
	        " . $item . ".appendChild(space.cloneNode(false));
	        if('$what_sel')" . $item . ".appendChild(sel_f_" . $item . ");
	        
	        template.appendChild(" . $item . ");
	
	        document." . $form_name . ".max_" . $item . ".value=suffixe*1+1*1 ;
	        ajax_pack_element(f_" . $item . ");
	    }
		</script>";
		
		// template de zone de texte pour chaque valeur
		$aff = "
		<div class='row'>
		<input type='text' class='$class' id='f_" . $item . "!!num!!' name='f_" . $item . "!!num!!' value=\"!!label_element!!\" autfield='f_" . $item . "_code!!num!!' completion=\"" . $item . "\" />
		<input type='hidden' id='f_" . $item . "_code!!num!!' name='f_" . $item . "_code!!num!!' value='!!id_element!!'>
		<input type='button' class='bouton' value='" . $msg ["raz"] . "' onclick=\"this.form.f_" . $item . "!!num!!.value='';this.form.f_" . $item . "_code!!num!!.value=''; \" />
		!!bouton_parcourir!!
		!!bouton_ajouter!!
		</div>\n";
		if ($what_sel)
			$bouton_parcourir = "<input type='button' class='bouton' value='" . $msg ["parcourir"] . "' onclick=\"openPopUp(" . $link . ")\" />";
		else
			$bouton_parcourir = "";
		$aff = str_replace ( '!!bouton_parcourir!!', $bouton_parcourir, $aff );
		
		$template = $script_js . "<div id=add" . $item . "' class='row'>";
		$template .= "<div class='row'><label for='f_" . $item . "' class='etiquette'>" . $label . "</label></div>";
		$num = 0;
		if (! $values [0])
			$values [0] = array (
					"id" => "",
					"label" => "" 
			);
		foreach ( $values as $value ) {
			
			$label_element = $value ["label"];
			$id_element = $value ["id"];
			
			$temp = str_replace ( '!!id_element!!', $id_element, $aff );
			$temp = str_replace ( '!!label_element!!', $label_element, $temp );
			$temp = str_replace ( '!!num!!', $num, $temp );
			
			if (! $num)
				$temp = str_replace ( '!!bouton_ajouter!!', " <input class='bouton' value='" . $msg ["req_bt_add_line"] . "' onclick='add_" . $item . "();' type='button'>", $temp );
			else
				$temp = str_replace ( '!!bouton_ajouter!!', "", $temp );
			$template .= $temp;
			$num ++;
		}
		$template .= "<input type='hidden' name='max_" . $item . "' value='$num'>";
		
		$template .= "</div><div id='add" . $item . "'/>
		</div>";
		return $template;
	}
	
	// ---------------------------------------------------------------
	// search_form() : affichage du form de recherche
	// ---------------------------------------------------------------
	function search_form() {
		global $user_query;
		global $msg;
		$user_query = str_replace ( '!!user_query_title!!', $msg [357] . " : " . $msg ["aut_menu_titre_uniforme"], $user_query );
		$user_query = str_replace ( '!!action!!', './autorites.php?categ=titres_uniformes&sub=reach&id=', $user_query );
		$user_query = str_replace ( '!!add_auth_msg!!', $msg ["aut_titre_uniforme_ajouter"], $user_query );
		$user_query = str_replace ( '!!add_auth_act!!', './autorites.php?categ=titres_uniformes&sub=titre_uniforme_form', $user_query );
		$user_query = str_replace ( '<!-- lien_derniers -->', "<a href='./autorites.php?categ=titres_uniformes&sub=titre_uniforme_last'>" . $msg ["aut_titre_uniforme_derniers_crees"] . "</a>", $user_query );
		print pmb_bidi ( $user_query );
	}
	
	// ---------------------------------------------------------------
	// do_isbd() : génération de l'isbd du titre uniforme (AFNOR Z 44-061 de 1986)
	// ---------------------------------------------------------------
	function do_isbd_old() {
		global $msg;
		
		$this->tu_isbd = "";
		if (! $this->id)
			return;
		
		$as = array_keys ( $this->responsabilites ["responsabilites"], "0" );
		for($i = 0; $i < count ( $as ); $i ++) {
			$indice = $as [$i];
			$auteur_0 = $this->responsabilites ["auteurs"] [$indice];
			$auteur = authorities_collection::get_authority('author', $auteur_0["id"]);
			if($i>0)$this->tu_isbd.= " / ";
			$this->tu_isbd .= $auteur->display . ". ";
		}
		if ($i)
			$this->tu_isbd .= ". ";
			/*
		 * if($this->num_author){
		 * $tu_auteur = new auteur ($this->num_author);
		 * $this->tu_isbd. = $tu_auteur->display.". ";
		 * }
		 */
		if ($this->name) {
			$this->tu_isbd .= $this->name;
		}
		
		return $this->tu_isbd;
	}
	function get_enrichment() {
		global $dbh;
		global $charset;
		
		if ($this->enrichment === null) {
			$enrichment = "";
			$requete = "select tu_enrichment from titres_uniformes where tu_id=" . $this->id;
			$resultat = pmb_mysql_query ( $requete, $dbh );
			$enrichment = pmb_mysql_result ( $resultat, 0, 0, $dbh );
			if ($enrichment) {
				$enrichment = unserialize ( $enrichment );
			}
			$this->enrichment = $enrichment;
		}
		return $this->enrichment;
	}
	static function get_marc_key() {
		if (! count ( titre_uniforme::$marc_key )) {
			titre_uniforme::$marc_key = new marc_list ( "music_key" );
		}
		return titre_uniforme::$marc_key;
	}
	static function get_marc_form() {
		if (! count ( titre_uniforme::$marc_form )) {
			titre_uniforme::$marc_form = new marc_list ( "music_form" );
		}
		return titre_uniforme::$marc_form;
	}
	function get_key_label() {
		if ($this->tonalite_marclist) {
			return titre_uniforme::get_marc_key ()->table [$this->tonalite_marclist];
		}
	}
	function get_form_label() {
		if ($this->form_marclist) {
			return titre_uniforme::get_marc_form ()->table [$this->form_marclist];
		}
	}
	
	/**
	 * Renvoie les données des oeuvres dont le titre uniforme est l'expression
	 * @return titre_uniforme Tableau de titre uniformes
	 */
	public function get_oeuvre_expressions_datas() {
		if (!count($this->oeuvre_expressions_datas)) {
			foreach ($this->oeuvre_expressions as $oeuvre_expression) {
				$this->oeuvre_expressions_datas[] = authorities_collection::get_authority('titre_uniforme', $oeuvre_expression['to_id']);
			}
		}
		return $this->oeuvre_expressions_datas;
	}
	
	/**
	 * Renvoie les données des évenements de l'oeuvre
	 * @return titre_uniforme Tableau de titre uniformes
	 */
	public function get_oeuvre_events_datas() {
		if (!count($this->oeuvre_events_datas)) {
			foreach ($this->oeuvre_events as $oeuvre_event) {
				$this->oeuvre_events_datas[] = authorities_collection::get_authority('authperso', $oeuvre_event['id']);
			}
		}
		return $this->oeuvre_events_datas;
	}	
	
	/**
	 * Renvoie les oeuvres liées
	 * @return titre_uniforme Tableau de titres uniformes
	 */
	public function get_other_links_datas() {
		if (!count($this->other_links_datas)) {
			foreach ($this->other_links as $other_link) {
				if (!isset($this->other_links_datas[$other_link['type']]['label'])) $this->other_links_datas[$other_link['type']]['label'] = $other_link['type_name'];
				$this->other_links_datas[$other_link['type']]['elements'][] = authorities_collection::get_authority('titre_uniforme', $other_link['to_id']);
			}
			ksort($this->other_links_datas);
		}
		return $this->other_links_datas;
	}
	
	public function get_oeuvre_links() {
		global $dbh, $oeuvre_link;
	
		$query = 'select oeuvre_link_to, tu_name, oeuvre_link_type, oeuvre_link_expression, oeuvre_link_other_link
				from tu_oeuvres_links join titres_uniformes on tu_id = oeuvre_link_to where oeuvre_link_from = "'.$this->id.'"
				order by oeuvre_link_order';
		$result = pmb_mysql_query($query, $dbh);
		if ($result && pmb_mysql_num_rows($result)) {
			while ($link = pmb_mysql_fetch_object($result)) {
				foreach ($oeuvre_link->table as $link_type) {
					if (isset($link_type[$link->oeuvre_link_type])) {
						$type_name = $link_type[$link->oeuvre_link_type];
						break;
					}
				}
				if ($link->oeuvre_link_expression) {
					$this->oeuvre_expressions[] = array(
							'to_id' => $link->oeuvre_link_to,
							'to_name' => $link->tu_name,
							'type' => $link->oeuvre_link_type,
							'type_name' => $type_name
					);
				}
				if ($link->oeuvre_link_other_link) {
					$this->other_links[] = array(
							'to_id' => $link->oeuvre_link_to,
							'to_name' => $link->tu_name,
							'type' => $link->oeuvre_link_type,
							'type_name' => $type_name
					);
				}
			}
		}
	}
	
	public function get_oeuvre_events() {
		global $dbh;
			
		$query = 'select oeuvre_event_authperso_authority_num
				from tu_oeuvres_events where oeuvre_event_tu_num = "'.$this->id.'"
				order by oeuvre_event_order';
		$result = pmb_mysql_query($query, $dbh);
		if ($result && pmb_mysql_num_rows($result)) {
			while ($auth = pmb_mysql_fetch_object($result)) {
				$this->oeuvre_events[]=array(
						'id' => $auth->oeuvre_event_authperso_authority_num,
						'isbd'=> authperso::get_isbd($auth->oeuvre_event_authperso_authority_num)
				);
			}
		}
	
	}
	
	// ---------------------------------------------------------------
	// do_isbd() : génération de l'isbd complete de l'oeuvre
	// ---------------------------------------------------------------
	function get_isbd() {
 		global $msg;
		
 		$isbd_simple = $this->get_isbd_simple ();
		
 		$isbd_oeuvre_expressions = "";
  		if (count ( $this->oeuvre_expressions )) {
 			$isbd_oeuvre_expressions .= " .- " . $msg ["aut_oeuvre_form_oeuvre_expression"];
 			foreach ( $this->oeuvre_expressions as $expression ) {
 				$oeuvre_to = authorities_collection::get_authority('titre_uniforme', $expression['to_id']);
 				$isbd_oeuvre_expressions .= " " . $oeuvre_to->get_isbd_simple ();
 			}
 		}
 		$isbd_other_links = "";
 		if (count ( $this->other_links )) {
 			for($i = 0; $i < count ( $this->other_links ); $i ++) {
 				$memo_links [$this->other_links [$i] ['type']] [] = $i;
 			}
 			foreach ( $memo_links as $link => $index_list ) {
 				$isbd_other_links .= " - " . $this->other_links [$index_list [0]] ['type_name'];
 				foreach ( $index_list as $index ) 
 				{
 					$oeuvre_link = authorities_collection::get_authority('titre_uniforme', $this->other_links[$index]['to_id']);
  					$isbd_other_links .= " " . $oeuvre_link->get_isbd_simple ();
 				}
 			}
		}
 		$this->tu_isbd = $isbd_simple . $isbd_oeuvre_expressions . $isbd_other_links;
 		return $this->tu_isbd;
	}
	
	// ---------------------------------------------------------------
	// get_isbd_simple() : génération de l'isbd minimaliste du titre uniforme (AFNOR Z 44-061 de 1986)
	// ---------------------------------------------------------------
	function get_isbd_simple() {
		global $msg;
		$isbd_simple = "";
		if ($this->name) {
			$isbd_simple .= $this->name;
		}
		if ($this->oeuvre_type) {
			$isbd_simple .= " [" . $this->oeuvre_type_name . "]";
		}
		$as = array_keys ( $this->responsabilites ["responsabilites"], "0" );
		for($i = 0; $i < count ( $as ); $i ++) {
			$indice = $as [$i];
			$auteur_0 = $this->responsabilites ["auteurs"] [$indice];
			$auteur = authorities_collection::get_authority('author', $auteur_0["id"]);
			$isbd_simple .= " / ";
			$isbd_simple .= $auteur->display;
		}
		if ($this->date) {
			$isbd_simple .= ", ".$this->date;
		}
		return $isbd_simple;
	}
} // class auteur



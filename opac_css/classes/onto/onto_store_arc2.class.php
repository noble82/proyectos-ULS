<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_store_arc2.class.php,v 1.2 2015-04-03 11:16:28 jpermanne Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");


require_once($class_path."/onto/onto_store.class.php");
require_once($class_path."/rdf/arc2/ARC2.php");


/**
 * class onto_store_arc2
 * 
 */
class onto_store_arc2 extends onto_store {
	
	/**
	 * @param array() config
	
	 * @return void
	 * @access public
	 */
	public function __construct($config) {
		parent::__construct($config);
	} // end of member function __construct
	
	/**
	 * Se connecter au store
	 *
	 * @return bool
	 * @access public
	 */
	public function connect() {
	
		$this->store = ARC2::getStore($this->config);
		
		if(!@$this->store->getDBCon()){
			//On regarde si l'on peut se connecter avec les informations fournies
			$this->errors[]=$this->store->getErrors();
			return false;
		}else{
			if(!$this->store->isSetUp()) {
				//Si les tables du store n'existent pas
				//On cr�e les tables
				$this->store->setUp();
				if($erreurs=$this->store->getErrors()){
					//Si la cr�ation � �chou�e
					foreach ($erreurs as $value) {
						$this->errors[]=$value;
					}
					
					$this->close();
					return false;
				}else{
					//Si on viens de faire la cr�ation pour pouvoir faire autre chose on doit se d�connecter et se reconnecter
					$this->close();
					$this->store = ARC2::getStore($this->config);
				}
			}
			return true;
		}
	} // end of member function connect

	/**
	 * D�connexion du store
	 *
	 * @return bool
	 * @access public
	 */
	public function close() {
		$this->store->closeDBCon();
	} // end of member function close
	
	
	/**
	 * Charge un fichier RDF dans le store
	 *
	 * @param string onto_filepath Chemin du fichier RDF � charger dans le store
	
	 * @return bool
	 * @access public
	 */
	public function load($onto_filepath){
		global $dbh,$thesaurus_ontology_filemtime;
	
		//on charge l'ontologie seulement si la date de modification du fichier est > � la date de derni�re lecture
		if(filemtime($onto_filepath)>$thesaurus_ontology_filemtime){
			// le load ne fait qu'ajouter les nouveaux triplets sans supprimer les anciens, donc on purge avant...
			$this->store->reset();
			
			//LOAD n'accepte qu'un chemin absolu
			$res=$this->query('LOAD <file://'.realpath($onto_filepath).'>');
	
			if($res){
				$query='UPDATE parametres SET valeur_param="'.filemtime($onto_filepath).'" WHERE type_param="pmb" AND sstype_param="ontology_filemtime"';
				pmb_mysql_query($query,$dbh);
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	} // end of member function load
	
	/**
	 * Ex�cute une requ�te SPARQL dans le store
	 * Rempli le result de l'instance, sous forme de tableau de class std
	 *
	 * @param string query Requ�te sparql � ex�cuter dans le store
	
	 * @return bool
	 * @access public
	 */
	public function query($query,$prefix=array()){
	
		$query=$this->format_namespaces($prefix).$this->utf8_normalize($query);
		
		$result=array();
		$tabResult=array();
	
		if(!count($this->errors)){
			//Si je n'ai pas d�j� des erreurs
			//J'execute la requete
			$result = $this->store->query($query);
				
			if($erreurs=$this->store->getErrors()){
				//Si l'execution de la requete a �chou�
				foreach($erreurs as $value){
					$this->errors[]=$value;
				}
				return false;
			}elseif(!$result){
				//et que je n'ai pas de r�sultat
				$this->errors[]='Query '.$query.' failed.';
				return false;
			}else{
				//on transforme le r�sultat et on l'ins�re dans la variable $this->result
				if(sizeof($result["result"]["rows"])){
					foreach($result["result"]["rows"] as $keyLine=>$valueLine) {
						//on construit l'objet
						$stdClass=new stdClass();
						foreach($valueLine as $property=>$value){
							$stdClass->{str_replace(" ","_",trim($property))}=$this->charset_normalize(trim($value));
						}
						//et on ins�re l'objet dans le tablea de result
						$tabResult[$keyLine]=$stdClass;
					}
				}
				
				//on ins�re le tableau d'objet dans la variable $this->result
				$this->result=$tabResult;
				return true;
			}
		}

		return false;
	} // end of member function query
	
} // end of onto_store_arc2
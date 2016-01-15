// +-------------------------------------------------+
// + 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FormEdit.js,v 1.13 2015-12-18 16:35:43 vtouchard Exp $


define(['dojo/_base/declare', 
        'dojo/request/xhr', 
        'dojo/_base/lang', 
        'dojo/topic', 
        'dojo/on', 
        'dojo/dom', 
        'dojo/dom-geometry', 
        'dojo/dom-style', 
        'dojo/dom-attr', 
        'dojo/query',
        'dojo/dom-construct', 
        'apps/pmb/gridform/Zone',
        'dijit/registry',
        'dojo/dom-class'], 
        function(declare, xhr, lang, topic, on, dom, domGeom, domStyle, domAttr, query, domConstruct, Zone, registry, domClass){

	  return declare(null, {
		  signalEditFormat: null,
		  signalOriginFormat: null,
		  state:null,		  
		  btnEdit: null,
		  btnOrigin: null,
		  zonesClickedSignals: null,
		  eltsClickedSignals: null,
		  nbZones: null,
		  zones: null,
		  paramsForSign:null,
		  savedScheme: null,
		  originalFormat:null,
		  flagOriginalFormat:null,
		  constructor:function(){
			  this.paramsForSign = new Array();
			  this.buildParamsForSign();
			  this.state = 'std';
			  this.btnEdit = dom.byId('bt_inedit');
			  this.btnOrigin = dom.byId('bt_origin_format');
			  this.zones = new Array();
			  this.signalEditFormat = on(this.btnEdit,'click', lang.hitch(this, this.btnEditCallback));
			  this.signalOriginFormat = on(this.btnOrigin,'click', lang.hitch(this, this.btnOriginFormatCallback));
			  topic.subscribe('PopupZone', lang.hitch(this, this.handleEvents, 'PopupZone'));
			  topic.subscribe('ContextMenu', lang.hitch(this, this.handleEvents, 'ContextMenu'));
			  topic.subscribe('DnDVirtualLine', lang.hitch(this, this.handleEvents, 'DnDVirtualLine'));
			  topic.subscribe('DnDElement', lang.hitch(this, this.handleEvents, 'DnDElement'));
			  this.getDefaultPos();
			  this.flagOriginalFormat = false;
			  this.getDatas();
		  },
		  handleEvents: function(evtClass, evtType, evtArgs){
			  switch(evtClass){
			  	case 'PopupZone':
			  		switch(evtType){
				  		case 'createZone':
				  			this.addZone(evtArgs);
				  			break;
				  		case 'editZone':
				  			this.editZone(evtArgs);
				  			break;
			  		}
			  		break;
			  	case 'ContextMenu':
			  		switch(evtType){
				  		case 'deleteZone':
				  			this.deleteZone(evtArgs);
				  			break;
				  		case 'upZone':
				  			this.upZone(evtArgs);
				  			break;
				  		case 'downZone':
				  			this.downZone(evtArgs);
				  			break;
				  		case 'makeInvisibleZone':
				  			this.makeInvisibleZone(evtArgs);
				  			break;
				  		case 'makeVisibleZone':
				  			this.makeVisibleZone(evtArgs);
				  			break;
				  		case 'changeZone':
				  			this.changeZone(evtArgs);
				  			break;
				  		case 'saveAll':
				  			this.saveAll(evtArgs);
				  			break;
				  		case 'saveAllBackbones':
				  			this.saveAllBackbones(evtArgs);
				  			break;
			  		}
			  		break;
			  	case 'DnDElement':
			  		switch(evtType){
				  		case 'onDrop':
				  			this.dropElement(evtArgs);
				  			break;
				  		}
			  		break;
			  }
		  },
		  btnEditCallback: function(evt){
			  switch(this.state){
				  case 'std':
					  this.state = 'inedit';
					  this.parseDom();
					  domAttr.set(this.btnEdit, 'value', pmbDojo.messages.getMessage('grid', 'grid_js_move_back'));
//					  var disableButtonsForm = query('form > input[type=button]');
//					  if(disableButtonsForm.length){
//						  for(var i=0; i<disableButtonsForm.length; i++){
//							  domAttr.set(disableButtonsForm[i],'disabled','disabled');
//							  domStyle.set(disableButtonsForm[i],'color','#aaa');
//						  }
//					  }
					  break;
				  case 'inedit':
					  this.state = 'std';
					  window.location.reload();
					  break;
			  }
		  },
		  btnOriginFormatCallback: function(evt){
			  this.savedScheme = null;
			  this.unparseDom();
			  this.buildGrid();
			  if(this.state != 'std'){
			  	this.parseDom();
			  }
		  },
		  parseDom: function(){
//			  var zones = query('div[etirable="yes"]');
			  var currentElts = query('div[movable="yes"]');
//			  if(zones.length) {
			  if(this.savedScheme){
				  for(var i=0; i<this.savedScheme.length ; i++){
					  var params = {
						  isExpandable:this.savedScheme[i].isExpandable, 
						  showLabel:this.savedScheme[i].showLabel, 
						  visible: this.savedScheme[i].visible,
						  label: this.savedScheme[i].label,
						  nodeId: this.savedScheme[i].nodeId
					  };
					  var nodeId = this.savedScheme[i].nodeId;
					  var newerZone = new Zone(params, nodeId, this);
					  newerZone.setVisible(this.savedScheme[i].visible);
					  if(this.savedScheme[i].visible){
						  newerZone.addConnectStyle();
					  }
					  if(this.savedScheme[i].elements.length) {
						  for(var j=0 ; j<this.savedScheme[i].elements.length ; j++){
							  var domElt = dom.byId(this.savedScheme[i].elements[j].nodeId);
							  if(domElt != null) {
								  newerZone.addField(domElt, this.savedScheme[i].elements[j].visible);
								  var indexElt = currentElts.indexOf(domElt);
								  currentElts.splice(indexElt, 1);
							  }
						  }
					  }
					  this.zones.push(newerZone);
					  this.nbZones++;
				  }
			  }  
			  if(currentElts.length) {
				  var objectZone = this.getZoneFromId('el0');
				  if(!objectZone){
					  objectZone = new Zone({label:pmbDojo.messages.getMessage('grid', 'grid_js_move_default_zone')}, 'el0', this);
					  objectZone.addConnectStyle();
					  this.zones.push(objectZone);
					  this.nbZones++;
				  }
				  for(var i=0 ; i<currentElts.length ; i++){
					  objectZone.addField(currentElts[i], true);
				  }
			  }
			  this.callZoneRefresher();
		  },
		  unparseDom: function(){
			  var cleanElts = query('#zone-container > div');
			  for(var i=0; i<this.originalFormat.length ; i++){
				  domConstruct.place(dom.byId(this.originalFormat[i].id), dom.byId('zone-container'), 'last');
				  dom.byId(this.originalFormat[i].id).className = this.originalFormat[i].class;
			  }

			  for(var i=0; i<cleanElts.length ; i++){
			  	if(cleanElts[i].getAttribute('movable') == null){
			  		domConstruct.destroy(cleanElts[i]);	
			  	}
			  }
			  this.zones = new Array();
		  },
		  addZone: function(params){
			  var nodeId = 'zone'+this.nbZones;
			  var newerZone = new Zone(params, nodeId, this);
			  newerZone.createNodes();
			  newerZone.addConnectStyle();
			  this.zones.push(newerZone);
			  this.nbZones++;
		  },
		  editZone: function(params){
			  var zoneToEdit = this.getZoneFromId(params.zoneId);
			  zoneToEdit.edit(params);
		  },
		  deleteZone: function(params){
			  var zoneToDelete = this.getZoneFromId(params.nodeId);
			  if(zoneToDelete.destroy()){
				  var indexZone = this.zones.indexOf(zoneToDelete);
				  this.zones.splice(indexZone, 1);
				  zoneToDelete = null;
				  this.nbZones--;
				  return true;
			  }
			  return false;
		  },
		  getZoneFromId: function(zoneId){
			  for(var i=0 ; i<this.zones.length ; i++){
				  if(this.zones[i].nodeId == zoneId){
					  return this.zones[i];
				  }
			  }
			  return false;
		  },
		  upZone: function(params){
			  var zoneToUp = this.getZoneFromId(params.nodeId);
			  if(this.zones.length > 1){
				  var indexZone = this.zones.indexOf(zoneToUp);
				  if(indexZone){
					  var tempZone = this.zones[indexZone-1];
					  this.zones[indexZone-1] = zoneToUp;
					  this.zones[indexZone] = tempZone;
					  domConstruct.place(this.zones[indexZone-1].nodeId+'Parent',this.zones[indexZone].nodeId+'Parent','before');
					  domConstruct.place(this.zones[indexZone-1].nodeId+'Child',this.zones[indexZone].nodeId+'Parent','before');
				  }
			  }
		  },
		  downZone: function(params){
			  var zoneToDown = this.getZoneFromId(params.nodeId);
			  if(this.zones.length > 1){
				  var indexZone = this.zones.indexOf(zoneToDown);
				  if(indexZone < this.zones.length-1){
					  var tempZone = this.zones[indexZone+1];
					  this.zones[indexZone+1] = zoneToDown;
					  this.zones[indexZone] = tempZone;
					  domConstruct.place(this.zones[indexZone+1].nodeId+'Child',this.zones[indexZone].nodeId+'Child','after');
					  domConstruct.place(this.zones[indexZone+1].nodeId+'Parent',this.zones[indexZone].nodeId+'Child','after');
				  }
			  }
		  },
		  makeInvisibleZone: function(params){
			  var zoneToMakeInvisible = this.getZoneFromId(params.nodeId);
			  zoneToMakeInvisible.setVisible(0);
		  },
		  makeVisibleZone: function(params){
			  var zoneToMakeVisible = this.getZoneFromId(params.nodeId);
			  zoneToMakeVisible.setVisible(1);
			  zoneToMakeVisible.addConnectStyle();
		  },
		  changeZone: function(params){
			  var originZone = this.getZoneFromId(params.zoneId);
			  var destZone = this.getZoneFromId(params.moveToZoneId);
			  var fieldNode = originZone.removeField(params.id);
			  var parentDiv = domConstruct.create('div', {class:'container-div row'}, destZone.domNode, 'last');
			  domConstruct.place(fieldNode, parentDiv, 'last');
			  destZone.addField(fieldNode,true);
		  },
		  getSign: function(){
			  var sign = '';
			  if(this.paramsForSign.length) {
				  for(var i=0 ; i<this.paramsForSign.length ; i++){
					  if(sign != '') {
						  sign += '_'+dom.byId(this.paramsForSign[i]).value;
					  } else {
						  sign = dom.byId(this.paramsForSign[i]).value;
					  }
				  }
			  }
			  return sign;
		  },
		  saveAll: function(){
			  var returnedInfos = this.getStruct();
			  returnedInfos['authSign'] = this.getSign();
			  this.launchXhrSave(returnedInfos);
		  },
		  saveAllBackbones: function(evtArgs){
			  var returnedInfos = this.getStruct();
			  returnedInfos['all_backbones'] = true;
			  var backboneTable = new Array();
			  for(var i=0 ; i<this.paramsForSign.length ; i++){
				  var backboneOptions = dom.byId(this.paramsForSign[i]).options;
				  var backboneValues = new Array();
				  for(var j=0 ; j<backboneOptions.length ; j++){
					  backboneValues.push(backboneOptions[j].value);
				  }
				  backboneTable.push(backboneValues);
			  }
			  returnedInfos['backbone_table'] = backboneTable;
			  this.launchXhrSave(returnedInfos);
		  },
		  getHiddenZones: function(){
			var hiddenZones = new Array();
			for(var i=0 ; i<this.zones.length ; i++){
				if(!this.zones[i].visible){
					hiddenZones.push(this.zones[i]);	
				}
			}
			return hiddenZones;
		  },
		  saveCallback: function(response){
			  if(response.status == true){
				  alert(pmbDojo.messages.getMessage('grid', 'grid_js_move_saved_ok'));
			  }else{
				  alert(pmbDojo.messages.getMessage('grid', 'grid_js_move_saved_error'));
			  }
		  },
		  getDefaultPos: function(){
			  var defaultElts = query('div[movable="yes"]');
			  this.originalFormat = new Array();
			  for(var i=0; i<defaultElts.length; i++){
				  this.originalFormat.push({id:defaultElts[i].id, class: defaultElts[i].className});  
			  }
		  },
		  getDatas: function(){
			  var currentUrl = window.location;
			  var authType = /categ=(\w+)&/g.exec(currentUrl)[1];
			  if(authType == 'authperso'){
				  var authPerso = /id_authperso=(\w+)&/g.exec(currentUrl)[1];
				  authType += '_'+authPerso;
			  }
			  var returnedInfos = {authType: authType, authSign: this.getSign()};
			  xhr("./ajax.php?module=autorites&categ=grid&action=get_datas",{
					 handleAs: "json",
					 method:'post',
					 data:'datas='+JSON.stringify(returnedInfos)
			  }).then(lang.hitch(this, this.getDatasCallback));
		  },
		  getDatasCallback: function(response){
			  if(response.status == true){
				  this.buildGrid(response.datas);
			  } else {
				  if(this.flagOriginalFormat){
					  this.unparseDom();  
				  }
				  this.buildGrid();
			  }
		  },
		  buildGrid: function(datas){
			  //On stocke le premier niveau des enfants de zone-container des div non movable (nettoyé apres traitement)
			  //var cleanElts = query('#zone-container > div:not(div[movable="yes"])');
			  var cleanElts = query('#zone-container > div:not([movable="yes"])');

			  var currentElts = query('div[movable="yes"]');
			  if(typeof datas != 'undefined'){
				  this.savedScheme =  JSON.parse(datas);
				  for(var i=0 ; i<this.savedScheme.length ; i++){		
					  var params = {
						  isExpandable:this.savedScheme[i].isExpandable, 
						  showLabel:this.savedScheme[i].showLabel, 
						  visible: this.savedScheme[i].visible,
						  label: this.savedScheme[i].label,
						  nodeId: this.savedScheme[i].nodeId
					  };
					  if(params.isExpandable) {
						  var parentNode = domConstruct.create('div', {id:params.nodeId+'Parent', class:'parent'}, dom.byId('zone-container'), 'last');
						  var labelNode = domConstruct.create('h3', {innerHTML:params.label, style:{'display':'inline'}}, parentNode, 'last');
						  domConstruct.create('img', {
							  src:'./images/plus.gif',
							  class:'img_plus',
							  align:'bottom',
							  name:'imEx',
							  id:params.nodeId+'Img',
							  title:'titre',
							  border:'0',
							  onClick:'expandBase("'+params.nodeId+'", true); return false;'
							  }, labelNode , 'before');
						  var domNode = domConstruct.create('div', {id:params.nodeId+'Child', label: params.label, class:'child'}, dom.byId('zone-container'), 'last');
					  } else {
						  if(params.showLabel){
							  var parentNode = domConstruct.create('div', {id:params.nodeId+'Parent', class:'parent'}, dom.byId('zone-container'), 'last');
							  var labelNode  = domConstruct.create('h3', {innerHTML:params.label}, parentNode, 'last');
						  }else{
							  var parentNode = domConstruct.create('div', {id:params.nodeId+'Parent', class:'parent', innerHTML:'&nbsp;'}, dom.byId('zone-container'), 'last');
						  }
						  var domNode = domConstruct.create('div', {id:params.nodeId+'Child', label: params.label}, dom.byId('zone-container'), 'last');
					  }
					  if(!params.visible){
						  domStyle.set(parentNode, 'display', 'none');
						  domStyle.set(domNode, 'display', 'none');
					  }
					  domAttr.set(domNode,'etirable', 'yes');
					  if(params.visible) {
						  domStyle.set(params.nodeId+'Parent', 'display', 'block');
						  domStyle.set(params.nodeId+'Child', 'display', 'inline-block');
						  domStyle.set(params.nodeId+'Child', 'width', '100%');
					  } else {
						  domStyle.set(params.nodeId+'Parent', 'display', 'none');
						  domStyle.set(params.nodeId+'Child', 'display', 'none');
					  }
					  
					  var nbColumn=1;
					  var columnInProgress = 0;
					  for(var j=0 ; j<this.savedScheme[i].elements.length ; j++){
						  if(columnInProgress == 0) {
							  var parentDiv = domConstruct.create('div', {class:'container-div row'}, domNode, 'last');
						  }
						  
						  var node = dom.byId(this.savedScheme[i].elements[j].nodeId);
						  if(node != null){
							  node.className = this.savedScheme[i].elements[j].className;
							  var result = /colonne([2-5])/.exec(node.className);
							  if(result){
								  nbColumn = result[1];
							  }
							  domConstruct.place(node, parentDiv, 'last');
							  if(!this.savedScheme[i].elements[j].visible){
								  domStyle.set(node, 'display','none');
							  } else {
								  domStyle.set(node, 'display','block');
							  }
							  columnInProgress++;
							  if(nbColumn == columnInProgress){
								  columnInProgress = 0;
								  nbColumn = 1;
							  }
							  var indexElt = currentElts.indexOf(node);
							  currentElts.splice(indexElt, 1);
						  }
					  }
				  }
			  }
			  //console.log(currentElts);
			  if(currentElts.length) {
				  var defaultZone = query('#el0Child');
				  if(!defaultZone.length) {
					  var parentNode = domConstruct.create('div', {id:'el0Parent', class:'parent', innerHTML:'&nbsp;'}, dom.byId('zone-container'), 'last');
					  var domNode = domConstruct.create('div', {id:'el0Child', label: pmbDojo.messages.getMessage('grid', 'grid_js_move_default_zone')}, dom.byId('zone-container'), 'last');
				  } else {
					  var domNode = dom.byId('el0Child');
				  }
				  domAttr.set(domNode, 'etirable', 'yes');
				  var nbColumn=1;
				  var columnInProgress = 0;
				  for(var i=0 ; i<currentElts.length ; i++){
					  if(columnInProgress == 0) {
						  var parentDiv = domConstruct.create('div', {class:'container-div row'}, domNode, 'last');
					  }
					  var result = /colonne([2-5])/.exec(currentElts[i].className);
					  if(result){
						  nbColumn = result[1];
					  }
//					  domClass.replace(currentElts[i], 'colonne1', 'row');
					  domConstruct.place(currentElts[i], parentDiv, 'last');
					  domStyle.set(currentElts[i], 'display', 'block');
					  
					  columnInProgress++;
					  if(nbColumn == columnInProgress){
						  columnInProgress = 0;
						  nbColumn = 1;
					  }
				  }
			  }
			  //Traitement terminé, on nettoye
			  for(var i=0 ; i<cleanElts.length ; i++){
				  domConstruct.destroy(cleanElts[i]);  
			  }
		  },
		  getZones: function(){
				return this.zones;
		  },
		  getElementFromId:function(id){
			  for(var i=0 ; i<this.zones.length ; i++){
				  var elt = this.zones[i].getElementFromId(id);
				  if(elt){
					  return elt;
				  }
			  }
			  return false;
		  },
		  dropElement: function(params){
			  var droppedElt = this.getElementFromId(params.id);
			  var oldWidgetSource = droppedElt.dnd;
			  droppedElt.dnd = registry.byId(droppedElt.domNode.parentNode.id);
			  var currentZone = droppedElt.zone;
			  var newZone = this.getZoneFromId(params.newZone);
			  if(newZone.domNode.id == currentZone.domNode.id){ //élément déplacé dans sa zone d'origine
				  currentZone.removeField(droppedElt.nodeId);
				  var elements = query('div[movable="yes"]', currentZone.domNode);
				  var indexElt = 0;
				  for(var i=0 ; i<elements.length ; i++){
					  if(elements[i].id == droppedElt.nodeId) {
						  newZone.elements.splice(i,0,droppedElt);
						  var indexElt = i;
					  }
				  }
			  }else{//Element déplacé dans une nouvelle zone
				  currentZone.removeField(droppedElt.nodeId);
				  var elements = query('div[movable="yes"]', newZone.domNode);
				  var indexElt = 0;
				  for(var i=0 ; i<elements.length ; i++){
					  if(elements[i].id == droppedElt.nodeId) {
						  newZone.elements.splice(i,0,droppedElt);
						  var indexElt = i;
					  }
				  }
				  droppedElt.zone = newZone;
			  }
			  droppedElt.dnd.resize();
			  if(droppedElt.dnd.id != oldWidgetSource.id){
				  oldWidgetSource.resize();
			  }
		  },
		  buildParamsForSign: function(){
			  var backbones = query('select[backbone="yes"]');
			  if(backbones.length){
				  for(var i=0; i<backbones.length; i++){
					  on(backbones[i], 'change', lang.hitch(this, this.switchGrid));
					  this.paramsForSign.push(backbones[i].id);
				  }  
			  }
		  },
		  switchGrid: function(evt){
			  this.flagOriginalFormat = true;
			  this.getDatas();
		  },
		  callZoneRefresher: function(){
			  for(var i=0 ; i<this.zones.length ; i++){
				  this.zones[i].refreshZoneLines();
			  }
		  },
		  getStruct: function(){
			  var JSONInformations = new Array();
			  if(this.zones.length) {
				  for(var i=0; i<this.zones.length ; i++){
					  JSONInformations.push(this.zones[i].getJSONInformations());
				  }
			  }
			  var currentUrl = window.location;
			  var authType = /categ=(\w+)&/g.exec(currentUrl)[1];
			  if(authType == 'authperso'){
				  var authPerso = /id_authperso=(\w+)&/g.exec(currentUrl)[1];
				  authType += '_'+authPerso;
			  }
			  var returnedInfos = {zones: JSONInformations, authType: authType};
			  return returnedInfos;
		  },
		  launchXhrSave: function(datas){
			  xhr("./ajax.php?module=autorites&categ=grid&action=save",{
					 handleAs: "json",
					 method:'post',
					 data:'datas='+JSON.stringify(datas)
			  }).then(lang.hitch(this, this.saveCallback));
		  }
	  });
});
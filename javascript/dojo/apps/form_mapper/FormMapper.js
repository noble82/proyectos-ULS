// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: FormMapper.js,v 1.6 2015-12-24 13:36:11 vtouchard Exp $

define(['dojo/_base/declare','dojo/io-query', 'dojo/request/xhr', 'dojo/_base/lang', 'dojo/query', 'dojo/dom-attr', 'dojo/dom'],function(declare, ioQuery, xhr, lang, query, domAttr, dom) {
	return declare(null, {
		idOeuvre: 0,
		source: '',
		dest: '',
		url: '',
		constructor: function(dest) {
			
			this.dest = dest;
		    var params = ioQuery.queryToObject(decodeURIComponent(dojo.doc.location.search.slice(1)));
			if(params.source_id){
				this.source = params.source_type;
				this.idOeuvre = params.source_id;
				this.getMapping();
			//	console.log('work',params.oeuvre_parent);
			}
			//console.log(this);
		},
		getMapping:function(){
			//TODO: Xhr request on pt d'entree
			switch(this.dest){
			case 'notice':
				var url = './ajax.php?module=catalog&categ=fill_form&sub=notice&quoi='+this.source+'&id='+this.idOeuvre;
				break;
			default:
				var url = './ajax.php?module=autorites&categ=fill_form&sub='+this.dest+'&quoi='+this.source+'&id='+this.idOeuvre;
				break;
			}
			if(url != ''){
				xhr(url, {
					handleAs: "json"
				}).then(lang.hitch(this, this.treatDatas));
			}
		},
		treatDatas: function(datas){
			if(datas){
				this.datas = datas;
				this.mapForm();
			}else{
				
			}
		},
		mapForm: function(){
			for(var i=0 ; i<this.datas.length ; i++){
			//	console.log('this.datas[i]', this.datas[i])
				switch(this.datas[i].mainType){
				case 'concept':
					this.treatConcept(this.datas[i]);
					break;
				default: 
					this.treatFields(this.datas[i]);
					break;
				}
			}
		},

		purgeConcept: function(baseFieldId, baseFieldIdValue,baseFieldIdType){
			var i = 0;
			while(dom.byId(baseFieldId[0]+i+baseFieldId[1])){
				domAttr.set(dom.byId(baseFieldId[0]+i+baseFieldId[1]), 'value', '');
				domAttr.set(dom.byId(baseFieldIdValue[0]+i+baseFieldIdValue[1]), 'value', '');
				domAttr.set(dom.byId(baseFieldIdType[0]+i+baseFieldIdType[1]), 'value', '');
				i++;
			}			
		},			
		purgeCheckbox:function(id, multiple){
			if(multiple == 'true'){
				var i = 0;
				while(dom.byId(id+i)){
					domAttr.set(dom.byId(id+i), 'checked', false);
					i++;
				}
			}else{
				domAttr.set(dom.byId(id), 'checked', false);
			}
		},
		expressionFromCallback:function(oeuvreId){
			if(confirm(pmbDojo.messages.getMessage('catalog','form_mapper_confirm_load_tu'))){
				this.idOeuvre = oeuvreId;
				this.getMapping();	
			}
		},
		manifFromCallback:function(oeuvreId){
			if(confirm(pmbDojo.messages.getMessage('catalog','form_mapper_confirm_load_tu'))){
				this.idOeuvre = oeuvreId;
				this.getMapping();	
			}
		},
		treatFields: function(fieldData){
			var jsCallback = fieldData.jscallback;
			for(var i=0 ; i<fieldData.fields.length ; i++){
				var currentFieldName = fieldData.fields[i].name;
				var domEltField = query(fieldData.fields[i].type+'[data-form-name="'+currentFieldName+'"]')[0];
				var fieldId = domAttr.get(domEltField, 'id');
				if(fieldData.multiple != 'true'){
					this.setValue(fieldData.fields[i].type, fieldData.fields[i].values[0], fieldId, fieldData.fields[i].subtype);
					//this[fieldData.fields[i].type+'Purge'](fieldId, false);
				}else{
					fieldId = fieldId.substr(0, fieldId.length-1);
					if(typeof this[fieldData.fields[i].type+'Purge'] == 'function')
						this[fieldData.fields[i].type+'Purge'](fieldId, true);
					var params=new Array();
					if(fieldData.callbackParams){
						params=fieldData.callbackParams;
					}
					for(var j=0 ; j<fieldData.fields[i].values.length ; j++){
						if(!dom.byId(fieldId+j)){
							window[fieldData.jscallback].apply(window,params);
						}
						this.setValue(fieldData.fields[i].type, fieldData.fields[i].values[j], fieldId+j, fieldData.fields[i].subtype);
					}	
				}
			}
		},
		treatConcept: function(fieldData){
			var jsCallback = fieldData.jscallback;
			for(var i=0 ; i<fieldData.fields.length ; i++){
				var currentFieldName = fieldData.fields[i].name;
				var domEltField = query(fieldData.fields[i].type+'[data-form-name="'+currentFieldName+'"]')[0];
				var fieldId = domAttr.get(domEltField, 'id');
				fieldId = fieldId.split('0');
				var params=new Array();
				if(fieldData.callbackParams){
					params=fieldData.callbackParams;
				}
				if(typeof this[fieldData.fields[i].type+'Purge'] == 'function')
					this[fieldData.fields[i].type+'Purge'](fieldId, true);
				for(var j=0 ; j<fieldData.fields[i].values.length ; j++){
					if(!dom.byId(fieldId[0]+j+fieldId[1])){
						window[fieldData.jscallback].apply(window,params);
					}
					this.setValue(fieldData.fields[i].type, fieldData.fields[i].values[j], fieldId[0]+j+fieldId[1]);
				}	
			}
		},
		setValue: function(type, value, id, subtype){
			switch(type){
				case 'input':
				case 'textarea':
					this.setInputValue(value, id, subtype);
					break;
				case 'select':
					this.setSelectorValue(value, id);
					break;
				case 'checkbox':
					this.setCheckboxValue(value, id);
					break;
			}
		},
		setInputValue: function(value, id, subtype){
			var eltToEdit = dom.byId(id);
			if(!subtype){
				domAttr.set(eltToEdit, 'value', value);	
			}else{
				switch(subtype){ //A voir pour ajouter radiobutton ?
					case 'checkbox':
						if(value==1){
							domAttr.set(eltToEdit, 'checked', true);
						}else{
							domAttr.set(eltToEdit, 'checked', false);
						}
						break;
				}
			}
			
		},
		setSelectorValue: function(value, id){
			var selectToEdit = dom.byId(id);
			var selectOptions = selectToEdit.options;
			for(var i=0; i<selectOptions.length; i++){
				for(var j=0; j<value.length; j++){
					if(selectOptions[i].value == value[j]){
						domAttr.set(selectOptions[i],'selected',true);
					}
				}
			}
		},
		inputPurge: function(id, multiple){
			this.commonInputPurge(id, multiple);
		},
		textareaPurge: function(id, multiple){
			this.commonInputPurge(id, multiple);
		},
		commonInputPurge: function(id, multiple){
			if(multiple == true){
				var i = 0;
				if(typeof id == 'object'){ // Cas d'un array composant les deux parties de l'id (surtout pour les concepts)
					while(dom.byId(id[0]+i+id[1])){
						domAttr.set(dom.byId(id[0]+i+id[1]), 'value', '');
						i++;
					}
				}else{
					while(dom.byId(id+i)){
						domAttr.set(dom.byId(id+i), 'value', '');
						i++;
					}
				}
			}else{
				domAttr.set(dom.byId(id), 'value', '');
			}
		},
		selectPurge: function(id, multiple){
			if(multiple == true){
				var k = 0;
				while(dom.byId(id+k)){
					var selectToEdit = dom.byId(id+k);
					var selectOptions = selectToEdit.options;
					for(var i=0; i<selectOptions.length; i++){
						domAttr.set(selectOptions[i],'selected',false);
					}
					k++;
				}
			}else{
				var selectToEdit = dom.byId(id);
				var selectOptions = selectToEdit.options;
				for(var i=0; i<selectOptions.length; i++){
					domAttr.set(selectOptions[i],'selected',false);
				}
			}
		},
		
	});
});
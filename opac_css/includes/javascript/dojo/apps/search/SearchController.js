// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SearchController.js,v 1.3 2015-12-29 10:32:41 ngantier Exp $

define(['dojo/_base/declare', 'dijit/layout/ContentPane', 'dojo/store/Memory', 'dojo/_base/lang', 'apps/search/SearchFieldsTree', 'dojo/query!css3', 'dojo/dom-construct', 'apps/search/SearchDnd', 'dojo/dom-class', 'dojo/dom-attr', 'dijit/tree/ObjectStoreModel', 'dojo/dom-style', 'dojo/on'], function(declare, ContentPane, Memory, lang, SearchFieldsTree, query, domConstruct, SearchDnd, domClass, domAttr, ObjectStoreModel, domStyle, on) {
	return declare(null, {
		contentTree: null,
		contentForm: null,
		store: null,
		searchFieldsList: null,
		
		constructor: function() {
			this.generateDom();
			this.parseSelector();
			this.buildTree();
			this.buildForm();
		},
		
		generateDom: function() {
			this.contentTree = new ContentPane({
				splitter: true,
				region: 'left',
				style: 'height:100%;width:250px;'
			}).placeAt('extended_search_dnd_container');
			this.contentForm = new ContentPane({
				id: 'extended_search_dnd_content_form',
				splitter: true,
				region: 'center',
				style: 'height:100%;'
			}).placeAt('extended_search_dnd_container');
		},
		
		parseSelector: function() {
			this.store = new Memory({data:[{id: 'root'}]});
			var children = dojo.byId('add_field').children;
			for (var i in children) {
				if ((children[i].nodeName == 'OPTGROUP') && (children[i].children.length)) {
					this.store.put({
						id: 'parent_' + i,
						label: children[i].label,
						parent: 'root'
					});
					for (var j in children[i].children) {
						if (children[i].children[j].nodeName == 'OPTION') {
							this.store.put({
								id: children[i].children[j].value,
								label: children[i].children[j].label,
								parent: 'parent_' + i,
								leaf: true
							});
						}
					}
				}
			}
			this.store.getChildren = function(object) {
				return lang.hitch(this.store, this.query({parent: object.id}));
			};
			domStyle.set(dojo.byId('choose_criteria'), 'display', 'none');
			domStyle.set(dojo.byId('add_field'), 'display', 'none');
		},
		
		buildTree: function() {
			// Un titre pour l'arbre
			domConstruct.place('<h3>' + dojo.byId('choose_criteria').innerHTML + '</h3>', this.contentTree.id);
			
			// Expand/Collapse all
			domConstruct.place('<span id="search_fields_tree_expandall" class="liLike"><img class="dijitTreeExpando dijitTreeExpandoClosed" data-dojo-attach-point="expandoNode" src="./images/expand_all.gif"></span><span id="search_fields_tree_collapseall" class="liLike"><img class="dijitTreeExpando dijitTreeExpandoOpened" data-dojo-attach-point="expandoNode" src="./images/collapse_all.gif"></span><div class="row"></div>', this.contentTree.id);
			var model = new ObjectStoreModel({
				store: this.store,
				query: { id: 'root'},
				mayHaveChildren: function(item) {
					return !item.leaf;
				}
			});
			var tree = new SearchFieldsTree({model: model});
			tree.placeAt(this.contentTree);
			on(dojo.byId('search_fields_tree_expandall'), 'click', function() {tree.expandAll();});
			on(dojo.byId('search_fields_tree_collapseall'), 'click', function() {tree.collapseAll();});
			
			var search_perso = dojo.byId('search_perso');
			if(search_perso){
				domConstruct.place('<hr><h3>' + pmbDojo.messages.getMessage('search', 'search_perso_title') + '</h3>', this.contentTree.id);
				domConstruct.place(search_perso,this.contentTree.id);
				domStyle.set(search_perso, 'display', 'block');
			}
		},
		
		buildForm: function() {
			var form = query('form[name="search_form"]')[0];
			this.searchFieldsList = query('table tbody tr', form);
			// On enl�ve la ligne du tableau qui contient le bouton rechercher
			this.searchFieldsList.pop();
			if (this.searchFieldsList.length) {
				var test = domConstruct.place(form,this.contentForm.id);
				this.initDnd();
			} else {
				domStyle.set(form, 'display', 'none');
				domConstruct.place('<span class="saisie-contenu">' + pmbDojo.messages.getMessage('search', 'search_fields_no_selected_fields') + '</span>',this.contentForm.id);
			}
		},
		
		initDnd: function() {
			if (this.searchFieldsList.length) {
				var dndForm = new SearchDnd(this.searchFieldsList[0].parentNode, {type: ['searchField']});
				this.searchFieldsList.forEach(this.declareItems, this);
				dndForm.sync();
			}
		},
		
		declareItems: function(node, index, nodeList) {
			domClass.add(node, 'dojoDndItem');
			// On met une poign�e !
			domConstruct.place('<i class="fa fa-arrows"></i>', node.childNodes[0]);
			domStyle.set(node.childNodes[0], 'cursor', 'move');
			domAttr.set(node, 'search_field_index', index);
			domClass.add(node.childNodes[0], 'dojoDndHandle');
		}
	});
});
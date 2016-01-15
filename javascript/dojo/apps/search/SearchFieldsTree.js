// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SearchFieldsTree.js,v 1.3 2015-10-27 08:42:06 apetithomme Exp $

define(['dojo/_base/declare', 'dijit/Tree', 'dijit/tree/ObjectStoreModel', 'dojo/query!css3', 'dojox/widget/Standby'], function(declare, Tree, ObjectStoreModel, query, Standby) {
	return declare([Tree], {
		
		id: 'searchFieldsTree',

		showRoot: false,
		
		persist: true,
		
		openOnClick: true,
		
		getLabel: function(item) {
			return item.label;
		},
		
		onDblClick: function(item, node, evt) {
			if (item.leaf) {
				var stand = new Standby({target: 'extended_search_dnd_content_form', imageText: 'Chargement...', image: './images/patience.gif'});
				document.body.appendChild(stand.domNode);
				stand.startup();
				stand.show();
				dojo.byId('add_field').value = item.id;
				enable_operators();
				query('form[name="search_form"]')[0].submit();
			}
		},
	});
});
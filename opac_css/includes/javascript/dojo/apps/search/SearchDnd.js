// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SearchDnd.js,v 1.2 2015-10-27 08:42:06 apetithomme Exp $

define(['dojo/_base/declare', 'dojo/dnd/Source', 'dojo/_base/array', 'dojo/query!css3', 'dojo/dom-attr', 'dojox/widget/Standby'], function(declare, Source, array, query, domAttr, Standby) {
	return declare([Source], {
		
		withHandles: true,
		
		onDrop: function(source, nodes, copy) {
			var stand = new Standby({target: 'extended_search_dnd_content_form', imageText: 'Chargement...', image: './images/patience.gif'});
			document.body.appendChild(stand.domNode);
			stand.startup();
			stand.show();
			this.inherited(arguments);
			var elements = source.node.children;
			
			if (elements.length) {
				array.forEach(elements, this.renameSearchFields, this);
			}
			query('form[name="search_form"]')[0].submit();
		},
		
		renameSearchFields: function(item, i, list) {
			var oldIndex = domAttr.get(item, 'search_field_index');
			query('input, select', item).forEach(function(node, id, nodesList) {
				if (node.name.indexOf('inter_' + oldIndex + '_') != -1) {
					node.name = node.name.replace('inter_' + oldIndex + '_', 'inter_' + i + '_');
				}
				if (node.name.indexOf('op_' + oldIndex + '_') != -1) {
					node.name = node.name.replace('op_' + oldIndex + '_', 'op_' + i + '_');
				}
				if (node.name.indexOf('field_' + oldIndex + '_') != -1) {
					node.name = node.name.replace('field_' + oldIndex + '_', 'field_' + i + '_');
				}
				if (node.name.indexOf('fieldvar_' + oldIndex + '_') != -1) {
					node.name = node.name.replace('fieldvar_' + oldIndex + '_', 'fieldvar_' + i + '_');
				}
			});
		},
		
		checkAcceptance: function(source, nodes) {
			return true;
		}
	});
});
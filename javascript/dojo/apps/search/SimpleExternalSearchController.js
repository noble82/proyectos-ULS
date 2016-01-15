// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SimpleExternalSearchController.js,v 1.1 2015-12-18 16:20:32 apetithomme Exp $

define(['dojo/_base/declare', 'apps/search/SearchController', 'dojo/query!css3', 'dojo/dom-construct', 'dojo/dom-style'], function(declare, SearchController, query, domConstruct, domStyle) {
	return declare([SearchController], {
		
		buildForm: function() {
			var form = query('form[name="search_form"]')[0];
			this.searchFieldsList = query('table tbody tr', form);
			domConstruct.place(form,this.contentForm.id);
			this.initDnd();

			if (!this.searchFieldsList.length) {
				query('div.form-contenu', form)[0];
				domConstruct.place('<span class="saisie-contenu">' + pmbDojo.messages.getMessage('search', 'search_fields_no_selected_fields') + '</span>', query('div.form-contenu', form)[0]);
				domStyle.set(query('input[type="submit"]', form)[0], 'display', 'none');
			}
		},
		
		getTreeTitle: function() {
			return query('label', dojo.byId('add_field').parentNode)[0].innerHTML;
		}
	});
});
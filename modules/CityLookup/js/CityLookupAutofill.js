jQuery(function() {

	// Pre-configure list of cities.
	var cities = [
		"New York", "Los Angeles", "Chevinay", "Houston", "Philadelphia",
		"Phoenix", "San Diego", "San Antonio", "Dallas", "Detroit", "Other"
	]
	
	// Enable auto-fill editview / detailview (ajaxedit)
	var activeModule = app.getModuleName(), activeView = app.getViewName();
	if (activeView == 'Edit' || activeView == 'Detail') {
		// For target module
		if (activeModule == 'Leads' || activeModule == 'Contacts') {
			// For target field.
			var fieldNames = ['mailingcity', 'othercity', 'city'];
			var selector = '';
			for(var i = 0; i < fieldNames.length; i++)
				selector += ",#"+activeModule+"_editView_fieldName_"+fieldNames[i]
			var field = jQuery(selector.substr(1));
			field.autocomplete({
				source: cities
			});					
		}
	}
	
});

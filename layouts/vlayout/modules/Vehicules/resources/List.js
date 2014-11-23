/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
//SG1411

Vtiger_List_Js("Vehicules_List_Js",{
},
{
	setTextColorForColorTag : function(){
		var listviewEntriesTable = jQuery('table.listViewEntriesTable');
		
		listviewEntriesTable.find('tr.listViewEntries div.colortag').each(function(index,element) {
			var colordiv = jQuery(element);
			if ((colordiv.data('color'))) {
				var thiscolor = colordiv.data('color');
				var colorContrast = app.getColorContrast(thiscolor.slice(1));
				if(colorContrast == 'light') {
					var textColor = 'black'
					}
				else {
					var textColor = 'white'
				}
				colordiv.css({'background-color':thiscolor,'color':textColor});
				colordiv.find('a').css({'background-color':thiscolor,'color':textColor});
			}
		}
		);	
	},
	
	fixLastThColspan : function() {
		var listviewEntriesTable = jQuery('table.listViewEntriesTable');
		listviewEntriesTable.find('tr.listViewHeaders th:last').attr('colspan',2);
		
	},
	
	
	registerEvents: function(){
		this._super();
		this.setTextColorForColorTag();
		this.fixLastThColspan();
	}
	

});
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("MGTransports_Detail_Js",{},{
	
	/* choisit la couleur du texte en fonction de la couleur du fond */
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
        
        loadContents : function(url,data) {
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();

		var detailContentsHolder = this.getContentHolder();
		var params = url;
		if(typeof data != 'undefined'){
			params = {};
			params.url = url;
			params.data = data;
		}
		AppConnector.requestPjax(params).then(
			function(responseData){
				detailContentsHolder.html(responseData);
				responseData = detailContentsHolder.html();
				//thisInstance.triggerDisplayTypeEvent();
				thisInstance.registerBlockStatusCheckOnLoad();
				//Make select box more usability
				app.changeSelectElementView(detailContentsHolder);
				
				//SG1412
				thisInstance.setTextColorForColorTag();
				//thisInstance.fixLastThColspan();
				//Attach date picker event to date fields
				app.registerEventForDatePickerFields(detailContentsHolder);
				app.registerEventForTextAreaFields(jQuery(".commentcontent"));
				jQuery('.commentcontent').autosize();
				thisInstance.getForm().validationEngine();
				jQuery('.pageNumbers',detailContentsHolder).tooltip();
				aDeferred.resolve(responseData);
			},
			function(){

			}
		);

		return aDeferred.promise();
	},
        loadRelatedList : function(pageNumber){
                var thisInstance = this;
		var relatedListInstance = new Vtiger_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		var params = {'page':pageNumber};
		relatedListInstance.loadRelatedList(params);
                //SG1412
				thisInstance.setTextColorForColorTag();
				//thisInstance.fixLastThColspan();
                
	},
        
        registerEvents: function(){
		this._super();
		this.setTextColorForColorTag();
               // this.fixLastThColspan();
	}
	
       
})
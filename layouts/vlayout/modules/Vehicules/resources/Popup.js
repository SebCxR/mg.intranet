/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
//SG1411

Vtiger_Popup_Js("Vehicules_Popup_Js",{
},
{
	setTextColorForColorTag : function(){
		var popupPageContainer = jQuery('#popupPageContainer');
		
		popupPageContainer.find('tr.listViewEntries div.colortag').each(function(index,element) {
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
	
	disableBusyVehicules : function(){
		var srcrcrd = (this.sourceRecord == false) ? this.getSourceRecord() : this.sourceRecord;
		var popupPageContainer = jQuery('#popupPageContainer');
		popupPageContainer.find('tr.listViewEntries').each(function(index,rowelement) {
			var row = jQuery(rowelement);
			if (row.data('busyin')) {
				row.find('td input.entryCheckBox').hide();
				if (row.data('busyin')==srcrcrd) {
					row.addClass('alreadyIn highlightBackgroundColor');
				}
				else {
					row.addClass('alreadyBusy');	
				}			
			}
			else row.addClass('freeVehicule');
			});
		
	},
	selectAllHandler : function(e){
		var currentElement = jQuery(e.currentTarget);
		var isMainCheckBoxChecked = currentElement.is(':checked');
		var tableElement = currentElement.closest('table');
		if(isMainCheckBoxChecked) {
			jQuery('tr.freeVehicule input.entryCheckBox', tableElement).attr('checked','checked').closest('tr').addClass('highlightBackgroundColor');
		}else {
			jQuery('tr.freeVehicule input.entryCheckBox', tableElement).removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');
		}
	},
	registerEventForListEntryValueLink : function(){		
		var thisInstance = this;
		var srcmod = thisInstance.getSourceModule();
		if (srcmod && srcmod == 'MGTransports') {	
			var popupPageContentsContainer = this.getPopupPageContainer();
			popupPageContentsContainer.on('click','td.listViewEntryValue a',function(e){
				thisInstance.clickLinkForParentWindow(e);
				});

		}	
	},
	clickLinkForParentWindow : function(e){
		
		if(typeof window == 'undefined'){
					window = self;
					};
		var thisanc  = jQuery(e.currentTarget);
		var newhref = thisanc.attr('href');
		if (newhref) {
				window.opener.location = newhref;
				
		}
		window.close();
		jQuery.progressIndicator();
		e.stopPropagation();
	},
	
	//SG1411 zapper de getListViewEntries à clickListViewEntries pour le popup de construction d'un mgtransport
	registerEventForListViewEntries : function(){
		
		var thisInstance = this;
		var srcmod = thisInstance.getSourceModule();
		if (srcmod && srcmod == 'MGTransports') {
			var popupPageContentsContainer = this.getPopupPageContainer();
			popupPageContentsContainer.on('click','.listViewEntries',function(e){
				thisInstance.clickListViewEntries(e);
				});
		}
		else this._super();		
	},
	//SG1411 jouer seulement sur la chexkbox si le vehicule est dispo; ignorer si vehicule busy
	clickListViewEntries: function(e){
		var thisInstance = this;
		var row  = jQuery(e.currentTarget);

		var dataIsBusyElsewhere = row.hasClass('alreadyBusy');
		var dataIsBusy = (dataIsBusyElsewhere || row.hasClass('alreadyIn'));
		
		if (dataIsBusy) {
			if (dataIsBusyElsewhere ) {
				alert (app.vtranslate('JS_VEHICULE_IS_BUSY_ELSEWHERE'));
				
				}
			else
				alert (app.vtranslate('JS_VEHICULE_IS_ALREADY_SELECTED'));
				e.preventDefault();
		}
		else {
			var thisCheckBox = row.find('td input.entryCheckBox');
			var isChecked = thisCheckBox.is(':checked');
			if(!isChecked) {
			thisCheckBox.attr('checked','checked').closest('tr').addClass('highlightBackgroundColor');
			}else {
			thisCheckBox.removeAttr('checked').closest('tr').removeClass('highlightBackgroundColor');
			}
		}	
	},
	registerEvents: function(){
		this._super();
		this.disableBusyVehicules();
		this.setTextColorForColorTag();
		this.registerEventForListEntryValueLink();
	}
	

});
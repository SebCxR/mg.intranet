/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("MGTransports_Detail_Js",{},{
	
        /*
	loadWidget : function(widgetContainer) {
		var thisInstance = this;
		var contentHeader = jQuery('.widget_header',widgetContainer);
		var contentContainer = jQuery('.widget_contents',widgetContainer);
		var urlParams = widgetContainer.data('url');
		var relatedModuleName = contentHeader.find('[name="relatedModule"]').val();

		var params = {
			'type' : 'GET',
			'dataType': 'html',
			'data' : urlParams
		};
		contentContainer.progressIndicator({});
		AppConnector.request(params).then(
			function(data){
				contentContainer.progressIndicator({'mode': 'hide'});				
				contentContainer.html(data);
                                //SG1411 Marche po
				thisInstance.setColorForBusyRecords();
                                
				app.registerEventForTextAreaFields(jQuery(".commentcontent"))
				contentContainer.trigger(thisInstance.widgetPostLoad,{'widgetName' : relatedModuleName})
			},
			function(){

			}
		);
	},
	
	//SG1411 TODO On declenche ou ?
	
	setColorForBusyRecords : function(){

               
                var thisBusyTr = jQuery('div summaryWidgetContainer table.listViewEntriesTable tr.inBusyConflict');
                
                thisBusyTr.find('td.busyState a').css({'color': 'red'});
               // alert('iciciciccii' + thisBusyTr.find('td.busyState span, td.busyState a'));
	},
        
       */ 
        
        
        
       // registerSummaryViewContainerEvents : function(summaryViewContainer){
        //   this._super(summaryViewContainer);
	// this.setColorForBusyRecords();
	//},
	//
	///**
	// * Function to add module related record from summary widget
	// */
	//registerEventForSelectingModuleRelatedRecordFromSummaryWidget : function(){
	//	var thisInstance = this;
	//	jQuery('.summaryWidgetContainer .selectButton').on('click',function(e){
	//		var currentElement = jQuery(e.currentTarget);
	//		var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
	//		var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
	//		var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
	//		var selectUrl = currentElement.data('url');
	//		var parentId = thisInstance.getRecordId();
	//		var selectParams = {};
	//		var relatedField = currentElement.data('parentRelatedField');
	//		var moduleName = currentElement.closest('.widget_header').find('[name="relatedModule"]').val();
	//		var relatedParams = {};
	//		relatedParams[relatedField] = parentId;
	//		
	//		var postSelectSave = function(data) {
	//			thisInstance.postSummaryWidgetAddRecord(data,currentElement);
	//			thisInstance.loadModuleSummary();
	//		}
	//		
	//		if(typeof relatedField != "undefined"){
	//			selectParams['data'] = relatedParams;
	//		}
	//		selectParams['noCache'] = true;
	//		selectParams['callbackFunction'] = postSelectSave;
	//		var progress = jQuery.progressIndicator();
	//		var headerInstance = new Vtiger_Header_Js();
	//		headerInstance.getSelectForm(selectUrl, moduleName,selectParams).then(function(data){
	//			headerInstance.handleSelectData(data,selectParams);
	//			progress.progressIndicator({'mode':'hide'});
	//		});
	//	})
	//},
	//
	///**
	// * Function to add module related record from summary widget
	// */
	//registerEventForAddingModuleRelatedRecordFromSummaryWidget : function(){
	//	var thisInstance = this;
	//	jQuery('.summaryWidgetContainer .addButton').on('click',function(e){
	//		var currentElement = jQuery(e.currentTarget);
	//		var summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer');
	//		var widgetDataContainer = summaryWidgetContainer.find('.widget_contents');
	//		var referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val();
	//		var quickcreateUrl = currentElement.data('url');
	//		var parentId = thisInstance.getRecordId();
	//		var quickCreateParams = {};
	//		var relatedField = currentElement.data('parentRelatedField');
	//		var moduleName = currentElement.closest('.widget_header').find('[name="relatedModule"]').val();
	//		var relatedParams = {};
	//		relatedParams[relatedField] = parentId;
	//		
	//		var postQuickCreateSave = function(data) {
	//			thisInstance.postSummaryWidgetAddRecord(data,currentElement);
	//			thisInstance.loadModuleSummary();
	//		}
	//		
	//		if(typeof relatedField != "undefined"){
	//			quickCreateParams['data'] = relatedParams;
	//		}
	//		quickCreateParams['noCache'] = true;
	//		quickCreateParams['callbackFunction'] = postQuickCreateSave;
	//		var progress = jQuery.progressIndicator();
	//		var headerInstance = new Vtiger_Header_Js();
	//		headerInstance.getQuickCreateForm(quickcreateUrl, moduleName,quickCreateParams).then(function(data){
	//			headerInstance.handleQuickCreateData(data,quickCreateParams);
	//			progress.progressIndicator({'mode':'hide'});
	//		});
	//	})
	//},
	//
	///**
	// * Function to load module summary of Projects
	// */
	//loadModuleSummary : function(){
	//	var summaryParams = {};
	//	summaryParams['module'] = app.getModuleName();
	//	summaryParams['view'] = "Detail";
	//	summaryParams['mode'] = "showModuleSummaryView";
	//	summaryParams['record'] = jQuery('#recordId').val();
	//	
	//	AppConnector.request(summaryParams).then(
	//		function(data) {
	//			jQuery('.summaryView').html(data);
	//		}
	//	);
	//},
	//
	////registerEvents : function(){
	////	var detailContentsHolder = this.getContentHolder();
	////	var thisInstance = this;
	////	this._super();
	////	
	////	detailContentsHolder.on('click','.moreRecentMilestones', function(){
	////		var recentMilestonesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentMileStonesLabel);
	////		recentMilestonesTab.trigger('click');
	////	});
	////	
	////	detailContentsHolder.on('click','.moreRecentTickets', function(){
	////		var recentTicketsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTicketsTabLabel);
	////		recentTicketsTab.trigger('click');
	////	});
	////	
	////	detailContentsHolder.on('click','.moreRecentTasks', function(){
	////		var recentTasksTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTasksTabLabel);
	////		recentTasksTab.trigger('click');
	////	});
	////}
})
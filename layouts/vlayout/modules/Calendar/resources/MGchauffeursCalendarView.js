/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
//SG1411

Calendar_CalendarView_Js("MGChauffeursCalendar_MGChauffeursCalendarView_Js",{
		
	currentInstance : false,
	
	initiateCalendarFeeds : function() {
		Calendar_CalendarView_Js.currentInstance.performCalendarFeedIntiate();
	}
},{
	
	multipleEvents : {},
	
	getAllMGChauffeursColors : function() {
		var result = {};
		var calendarfeeds = jQuery('[data-calendar-feed]');
		
		calendarfeeds.each(function(index,element){
			var feedcheckbox = jQuery(element);
			var	disabledOnes = app.cacheGet('calendar.feeds.disabled',[]);
			if (disabledOnes.indexOf(feedcheckbox.data('calendar-sourcekey')) == -1) {
				feedcheckbox.attr('checked',true);
				var id = feedcheckbox.data('calendar-userid');
				var vcolor = feedcheckbox.data('calendar-usercolor');
				var colorContrast = app.getColorContrast(vcolor.slice(1));
				if(colorContrast == 'light') {
					var textColor = 'black'
					} else {
					var textColor = 'white'
					}
				
				result[id] = vcolor+ ',' + textColor;
			}
		});
		
		return result;
	},
	setColors : function() {
		var calendarfeeds = jQuery('[data-calendar-feed]');		
		calendarfeeds.each(function(index,element){
			var feedcheckbox = jQuery(element);
			var usercolor = feedcheckbox.data('calendar-usercolor');
			var colorContrast = app.getColorContrast(usercolor.slice(1));
			if(colorContrast == 'light') {
				var textColor = 'black'
			} else {
				var textColor = 'white'
			}
			
			feedcheckbox.closest('label').find('.label').css({'background-color':usercolor,'color':textColor});
		});

	},
	fetchAllCalendarFeeds : function() {
		var thisInstance = this;
		var calendarfeeds = jQuery('[data-calendar-feed]');
		
		calendarfeeds.each(function(index,element){
			var feedcheckbox = jQuery(element);
			thisInstance.fetchCalendarFeed(feedcheckbox);
		});
	thisInstance.multipleEvents = false;
	},
	
	toDateString : function(date) {
		var d = date.getDate();
		var m = date.getMonth() +1;
		var y = date.getFullYear();
		
		d = (d <= 9)? ("0"+d) : d;
		m = (m <= 9)? ("0"+m) : m;
		return y + "-" + m + "-" + d;
	},
	fetchCalendarFeed : function(feedcheckbox) {
		var thisInstance = this;	
		//var type = feedcheckbox.data('calendar-sourcekey');
		this.calendarfeedDS[feedcheckbox.data('calendar-sourcekey')] = function(start, end, callback) {
			if(typeof thisInstance.multipleEvents != 'undefined' && thisInstance.multipleEvents != false){
				var events = thisInstance.multipleEvents[feedcheckbox.data('calendar-userid')];				
				if(events !== false && events !== undefined) {
					callback(events);
					return;
				}
			}
			if(feedcheckbox.not(':checked').length > 0) {
				callback([]);
				return;
			}
			feedcheckbox.attr('disabled', true);
			
			
			var tempmap = {};
			var usrid = feedcheckbox.data('calendar-userid');			
			var usercolor = feedcheckbox.data('calendar-usercolor');
				var colorContrast = app.getColorContrast(usercolor.slice(1));
				if(colorContrast == 'light') {
					var textColor = 'black'
					} else {
					var textColor = 'white'
					}
				
				tempmap[usrid] = usercolor + "," + textColor;
			
			
			
						
			var params = {
				module: 'Calendar',
				action: 'Feed',
				start: thisInstance.toDateString(start),
				end: thisInstance.toDateString(end),
				type: feedcheckbox.data('calendar-feed'),
				mapping : tempmap,
				userid : feedcheckbox.data('calendar-userid'),
				//color : feedcheckbox.data('calendar-feed-color'),
				//textColor : feedcheckbox.data('calendar-feed-textcolor')
			}
			AppConnector.request(params).then(function(events){	
				thisInstance.multipleEvents[usrid] = events[usrid];			
				callback(events[usrid]);
				feedcheckbox.attr('disabled', false).attr('checked', true);
				},
				function(error){
				//To send empty events if error occurs
				 callback([]);
				}
				);
	}
		this.getCalendarView().fullCalendar('addEventSource', this.calendarfeedDS[feedcheckbox.data('calendar-sourcekey')]);
		
	},
	
	fetchAllEvents : function() {
		var thisInstance = this;
		var result = this.getAllMGChauffeursColors();
		var params = {
			module: 'Calendar',
			action: 'Feed',
			start: thisInstance.toDateString(thisInstance.getCalendarView().fullCalendar('getView').visStart),
			end: thisInstance.toDateString(thisInstance.getCalendarView().fullCalendar('getView').visEnd),
			type: 'MGChauffeurs',
			mapping : result
		}

		AppConnector.request(params).then(function(multipleEvents){
				thisInstance.multipleEvents = multipleEvents;
				thisInstance.fetchAllCalendarFeeds();
				},
			function(error){
			
			});
		
		
	},
	//SGTODONOW actuel pompage 
	isAllowedToAddCalendarEvent : function(calendarDetails){
		//revoir condition affichage
		var assignedUserId = calendarDetails.assigned_user_id.value;
		if(jQuery('[data-calendar-userid='+assignedUserId+']').is(':checked')){
			return true;
		} else {
			return false;
		}
		
	},
	addCalendarEvent : function(calendarDetails) {
		if(calendarDetails.activitytype.value == 'Task'){
			var msg = app.vtranslate('JS_TASK_IS_SUCCESSFULLY_ADDED_TO_YOUR_CALENDAR');
			var customParams = {
				text : msg,
				 type: 'info'
			}
			Vtiger_Helper_Js.showPnotify(customParams);
			return;
		} else {
			this._super(calendarDetails);
		}
	},
	
	performCalendarFeedIntiate : function() {
		
		this.setColors();
		
		this.fetchAllEvents();
		
		this.registerCalendarFeedChange();
	},
	
	registerEvents : function() {
		this._super();
		// Open the Calendar added by default (override previous widget close state)
		// This is required to display the event items on the view.
		jQuery('[data-widget-url="module=Calendar&view=ViewTypes&mode=getMGChauffeursListForCalendar"]').trigger('click');
		return this;
	}
});

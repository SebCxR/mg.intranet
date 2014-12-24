/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("MGChauffeurs_Edit_Js",{},{
	
duplicateCheckCache : {},
userQuickCreateCallBacks: [],
	
referenceCreateHandler : function(container) {
		var thisInstance = this;
		var postQuickCreateSave  = function(data) {
			var params = {};
			params.name = data.result._recordLabel;
			params.id = data.result._recordId;
			
			thisInstance.setReferenceFieldValue(container, params);
			
			var editViewForm = jQuery('#EditView');
			editViewForm.find('input[name="email"]').val(data.result.email1.value);
			
			//$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
		};
			
		var postQuickCreateShows = function(formQC) {			
			var editViewForm = jQuery('#EditView');
			var chauffName = editViewForm.find('input[name="name"]').val();
			var chauffMail = editViewForm.find('input[name="email"]').val();
			var chauffIsActive = editViewForm.find('input[name="actif"]').is(":checked");
			var chauffPhoneMobile = editViewForm.find('input[name="phone_mobile"]').val();
			
			formQC.find('td.fieldValue input').each(function(index,ele) {
			
				var thisQCFieldValueInput = jQuery(ele);
				
				switch (thisQCFieldValueInput.attr('name')) {
					case ('user_name'):
						thisQCFieldValueInput.val(chauffName);
						 break;
					case ('email1'):
						thisQCFieldValueInput.val(chauffMail);
						 break;
					case ('last_name'):
						thisQCFieldValueInput.val(chauffName);
					 break;
					case ('is_admin'):
						if (thisQCFieldValueInput.data('fieldinfo')) {
							var fieldInfo = thisQCFieldValueInput.data('fieldinfo');
							var fieldLabel = fieldInfo.label;
							thisQCFieldValueInput.parent().siblings('td.fieldLabel').find('label').each( function (){
								$this = jQuery(this);
								if ($this.html()== fieldInfo.label ) {
									$this.hide();
								};
							}
							);							
						}
						thisQCFieldValueInput.hide();
					break;
					default : break;
					};
			});
			formQC.find('td.fieldValue select').each(function(index,ele) {
			
				var thisQCFieldSelect = jQuery(ele);
				
				switch (thisQCFieldSelect.attr('name')) {
					 case ('roleid'):						
						thisQCFieldSelect.find("option").each(function (ind,elem) {
							var thisopt = jQuery(elem);
							if (thisopt.html() == app.vtranslate('JS_SINGLE_MGCHAUFFEURS')) {
								thisopt.attr('selected','selected');							
							}
							else thisopt.removeAttr('selected');
						});
						thisQCFieldSelect.trigger("liszt:updated");
						//thisQCFieldSelect.trigger("chosen:updated");
						 break;
					case ('status'):
						//SGTODO Marche pas ...
						if (chauffIsActive) {
							thisQCFieldSelect.data("selected-value","Active");
							thisQCFieldSelect.find('option[value="Active"]').attr('selected', 'selected');
							thisQCFieldSelect.find('option[value="Inactive"]').removeAttr('selected');							
						}
						else {
							thisQCFieldSelect.data("selected-value","Inactive");
							thisQCFieldSelect.find('option[value="Inactive"]').attr('selected', 'selected');
							thisQCFieldSelect.find('option[value="Active"]').removeAttr('selected');
						}
						//SGTODO Marche pas : je n'arrive pas actualiser le select et fixer l'option que je veux					
						thisQCFieldSelect.trigger("liszt:updated");
						thisQCFieldSelect.trigger("chosen:updated");
						 break;
					
					default : break;
					};
			});
			
			
			
		};
		
		
		var referenceModuleName = this.getReferencedModuleName(container);
		
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
		
		if(quickCreateNode.length <= 0) {
			if (referenceModuleName == 'Users') {
				var eventparams = {'url' : 'index.php?module=Users&view=QuickCreateAjax'
						,'callbackPostShown':postQuickCreateShows	
						,'callbackFunction':postQuickCreateSave
						,'chauffeurPhoneMobile' : jQuery('#EditView').find('input[name="phone_mobile"]').val()
						};
			container.trigger('Vtiger.Reference.UserQuickCreate',eventparams);
			}
			else Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'));
		}
		quickCreateNode.trigger('click',{'callbackFunction':postQuickCreateSave});
	},
//SGNOW
registerEditUserLinkEvent : function(container){
		var thisInstance = this;
		var $userEditButton = container.find('.editUserLink');
		var $userInput = $userEditButton.parent().siblings('input[name="userid"]');
		var selectUserPopupButton = $userEditButton.siblings('.relatedPopup');
		var createUserButton = $userEditButton.siblings('.createReferenceRecord');
		$userEditButton.on("click",function(e){
			$thisUserInput = jQuery(e.currentTarget).parent().siblings('input[name="userid"]');
			if ($thisUserInput.val() && $thisUserInput.val()!= '') {				
				var userUrl = "index.php?module=Users&parent=Settings&view=Edit&record=" + $thisUserInput.val();
				window.location.href = userUrl;
			}	
		});
		
		if ($userInput.val() && $userInput.val()!= '' && $userInput.val()!= '0') {
				$userEditButton.show();
				selectUserPopupButton.hide();
				createUserButton.hide();
			}
		else {			
				$userEditButton.hide();
				selectUserPopupButton.show();
				createUserButton.show();
				
				
			};
		
	},
	
setReferenceFieldValue : function(container, params) {
		var sourceField = container.find('input[class="sourceField"]').attr('name');
		var fieldElement = container.find('input[name="'+sourceField+'"]');
		var sourceFieldDisplay = sourceField+"_display";
		var fieldDisplayElement = container.find('input[name="'+sourceFieldDisplay+'"]');
		var popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();

		var selectedName = params.name;
		var id = params.id;

		fieldElement.val(id)
		fieldDisplayElement.val(selectedName).attr('readonly',true);
		
		fieldDisplayElement.siblings('.editUserLink').show();
		fieldDisplayElement.siblings('.clearReferenceSelection').show();
		fieldDisplayElement.siblings('.relatedPopup').hide();
		fieldDisplayElement.siblings('.createReferenceRecord').hide();
		fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});

		fieldDisplayElement.validationEngine('closePrompt',fieldDisplayElement);
	},
/**
 * Function which will register reference field clear event
 * @params - container <jQuery> - element in which auto complete fields needs to be searched
*/
registerClearReferenceSelectionEvent : function(container) {
	container.find('.clearReferenceSelection').on('click', function(e){
		var element = jQuery(e.currentTarget);
		var parentTdElement = element.closest('td');
		var fieldNameElement = parentTdElement.find('.sourceField');
		var fieldName = fieldNameElement.attr('name');
		fieldNameElement.val('');
		parentTdElement.find('#'+fieldName+'_display').removeAttr('readonly').val('');
			
		//SG1412
		parentTdElement.find('span.editUserLink').hide();
		parentTdElement.find('span.clearReferenceSelection').hide();
		parentTdElement.find('.relatedPopup').show();
		parentTdElement.find('.createReferenceRecord').show();
			
		element.trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
		e.preventDefault();
		})
	},	
	
	
			
registerUserQuickCreateEvent : function(){
	var thisInstance = this;	
	userfieldtd = jQuery('input[name="popupReferenceModule"][value="Users"]').closest('td');	
	userfieldtd
		.on('Vtiger.Reference.UserQuickCreate', function(e,params) {
			if (typeof params == 'undefined') {
				 params = {};
				}
			if (typeof params.callbackFunction == 'undefined') {
				params.callbackFunction = function() {
				 };
				}
			var quickCreateUrl = params.url;
			var quickCreateModuleName = 'Users';

			var progress = jQuery.progressIndicator();
			thisInstance.getQuickCreateForm(quickCreateUrl, quickCreateModuleName, params)
				.then(function(data) {
					thisInstance.handleQuickCreateData(data, params);
					progress.progressIndicator({
						'mode': 'hide'
						});
					}
				);
		});	
	},
	
	
getQuickCreateForm: function(url, moduleName, params) {
        var thisInstance = this;
        var aDeferred = jQuery.Deferred();
        var requestParams;
        if (typeof params == 'undefined') {
            params = {};
        }
        if ((!params.noCache) || (typeof (params.noCache) == "undefined")) {
            if (typeof Vtiger_Header_Js.quickCreateModuleCache[moduleName] != 'undefined') {
                aDeferred.resolve(Vtiger_Header_Js.quickCreateModuleCache[moduleName]);
                return aDeferred.promise();
            }
        }
        requestParams = url;
        if (typeof params.data != "undefined") {
            var requestParams = {};
            requestParams['data'] = params.data;
            requestParams['url'] = url;
        }
        AppConnector.request(requestParams).then(function(data) {
            if ((!params.noCache) || (typeof (params.noCache) == "undefined")) {
                Vtiger_Header_Js.quickCreateModuleCache[moduleName] = data;
            }
            aDeferred.resolve(data);
        });
        return aDeferred.promise();
    },
	
	  /**
     * Function to save the quickcreate module
     * @param accepts form element as parameter
     * @return returns deferred promise
     */
quickCreateSave: function(form,params) {
        var aDeferred = jQuery.Deferred();
        //Ajout du champ telephone mobile
	$("<input type='hidden'/>")
		.attr("id", "Users_fieldName_phone_mobile")
		.attr("name", "phone_mobile")
		.val(params.chauffeurPhoneMobile)
		.appendTo(form);
		
	var quickCreateSaveUrl = form.serializeFormData();
	        AppConnector.request(quickCreateSaveUrl).then(
			function(data) {
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_HAS_BEEN_CREATED'));
				aDeferred.resolve(data);
				},
			function(textStatus, errorThrown) {
				aDeferred.reject(textStatus, errorThrown);
			}
		);
        return aDeferred.promise();
    },
 /**
  * Function to navigate from quickcreate to editView Fullform
 * @param accepts form element as parameter
 */
quickCreateGoToFullForm: function(form, editViewUrl,params) {
        //Ajout du champ telephone mobile
	$("<input type='hidden'/>")
		.attr("id", "Users_fieldName_phone_mobile")
		.attr("name", "phone_mobile")
		.val(params.chauffeurPhoneMobile)
		.appendTo(form);
		
	var formData = form.serializeFormData();
        //As formData contains information about both view and action removed action and directed to view
        delete formData.module;
        delete formData.action;
        var formDataUrl = jQuery.param(formData);
        var completeUrl = editViewUrl + "&" + formDataUrl;
        window.location.href = completeUrl;
    },
	
 handleQuickCreateData: function(data, params) {
        if (typeof params == 'undefined') {
            params = {};
        }
        var thisInstance = this;
	
        app.showModalWindow(data, function(data) {
            var quickCreateForm = data.find('form[name="QuickCreate"]');
            var moduleName = quickCreateForm.find('[name="module"]').val();
            var editViewInstance = Vtiger_Edit_Js.getInstanceByModuleName(moduleName);
            editViewInstance.registerBasicEvents(quickCreateForm);
            quickCreateForm.validationEngine(app.validationEngineOptions);
            if (typeof params.callbackPostShown != "undefined") {
                params.callbackPostShown(quickCreateForm);
            }
	    
            thisInstance.registerQuickCreatePostLoadEvents(quickCreateForm, params);
            app.registerEventForDatePickerFields(quickCreateForm);
            var quickCreateContent = quickCreateForm.find('.quickCreateContent');
            var quickCreateContentHeight = quickCreateContent.height();
            var contentHeight = parseInt(quickCreateContentHeight);
            if (contentHeight > 300) {
                app.showScrollBar(jQuery('.quickCreateContent'), {
                    'height': '300px'
                });
            }
        });
    },
    
    registerQuickCreatePostLoadEvents: function(form, params) {
        var thisInstance = this;
        var submitSuccessCallbackFunction = params.callbackFunction;
        var goToFullFormCallBack = params.goToFullFormcallback;
	
	if (typeof submitSuccessCallbackFunction == 'undefined') {
            submitSuccessCallbackFunction = function() {
            };
        }
		
	 form.find('#goToFullForm').on('click', function(e) {
            var form = jQuery(e.currentTarget).closest('form');
            var editViewUrl = jQuery(e.currentTarget).data('editViewUrl');
            if (typeof goToFullFormCallBack != "undefined") {
                goToFullFormCallBack(form);
            }
            thisInstance.quickCreateGoToFullForm(form, editViewUrl,params);
        });
	
        form.on('submit', function(e) {
            var form = jQuery(e.currentTarget);
	    //e.preventDefault();
            var module = form.find('[name="module"]').val();
            //Form should submit only once for multiple clicks also
            if (typeof form.data('submit') != "undefined") {
                return false;
            }
	    else
	    {
                var invalidFields = form.data('jqv').InvalidFields;
		
                if (invalidFields.length > 0) {
                    //If validation fails, form should submit again
                    form.removeData('submit');
                    form.closest('#globalmodal').find('.modal-header h3').progressIndicator({
                        'mode': 'hide'
                    });
                    e.preventDefault();
                    return;
                }
		else {
                    //Once the form is submiting add data attribute to that form element
                    form.data('submit', 'true');
                    form.closest('#globalmodal').find('.modal-header h3').progressIndicator({
                        smallLoadingImage: true,
                        imageContainerCss: {
                            display: 'inline',
                            'margin-left': '18%',
                            position: 'absolute'
                        }
                    });
                }
		e.preventDefault();	
		thisInstance.preUserCreateCheck(form)
		             .then(function (data){
					thisInstance.quickCreateSave(form,params).then(
						function(data) {
							app.hideModalWindow();
							submitSuccessCallbackFunction(data);
							var registeredCallBackList = thisInstance.userQuickCreateCallBacks;
							for (var index = 0; index < registeredCallBackList.length; index++) {
								var callBack = registeredCallBackList[index];
								callBack({
									'data': data,
									'name': form.find('[name="module"]').val()
									});
								}
						},
						function(data) {
						}
						);
					}
				,
				function (data){
					form.removeData('submit');
					form.closest('#globalmodal').find('.modal-header h3').progressIndicator({
											'mode': 'hide'					
												});	
				}
				);	
            }
        });
       
    },
/*		
registerUserQuickCreateCallBack: function(callBackFunction) {
        if (typeof callBackFunction != 'function') {
            return false;
        }
        this.userQuickCreateCallBacks.push(callBackFunction);
        return true;
    },
*/
checkDuplicateUser: function(userName){
	var aDeferred = jQuery.Deferred();
	var params = {	'module': 'Users',
			'action' : "SaveAjax",
			'mode' : 'userExists',
			'user_name' : userName
			}
	AppConnector.request(params).then(
			function(data) {if(data.result){
						aDeferred.resolve(data);
					}else{
						aDeferred.reject(data);
					}
				}
			);	
	return aDeferred.promise();
	},
	
	preUserCreateCheck : function(form) {
		
	var thisInstance = this;
	var userName = jQuery('input[name="user_name"]',form).val();
	var newPassword = jQuery('input[name="user_password"]',form).val();
	var confirmPassword = jQuery('input[name="confirm_password"]',form).val();
			
	checkDeferred = jQuery.Deferred();
				
	if(newPassword != confirmPassword){
		Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_REENTER_PASSWORDS'));
		checkDeferred.reject({"result" : "pswdproblem"});
		return checkDeferred.promise();
	}	
	if(!(userName in thisInstance.duplicateCheckCache)) {
		thisInstance.checkDuplicateUser(userName).then(
			function(data){							
				if(data.result) {
					thisInstance.duplicateCheckCache[userName] = data.result;
					Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_EXISTS'));
					checkDeferred.reject(data);
					}
				}, 
			function (data, error){
				thisInstance.duplicateCheckCache[userName] = data.result;
				checkDeferred.resolve(data);														
			}
			);
	}
	else {
		if(thisInstance.duplicateCheckCache[userName] == true){
			Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_USER_EXISTS'));
			checkDeferred.reject({"success" : true, "result" : true});
		} else {
		delete thisInstance.duplicateCheckCache[userName];
		checkDeferred.resolve({"success" : true, "result" : false});
		}
	}
	return checkDeferred.promise();		
	},
	
registerEvents : function() {
	this._super();
	var editViewForm = this.getForm();
	this.registerUserQuickCreateEvent();
	this.registerEditUserLinkEvent(editViewForm);
	}
});

<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_Detail_View extends Vtiger_Detail_View {

	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if(!empty($recordId)){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$activityType = $recordModel->getType();
			if($activityType == 'Events')
				$moduleName = 'Events';
		}
		$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		$recordModel = $detailViewModel->getRecord();
		
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$summaryInfo = array();
		// Take first block information as summary information
		$stucturedValues = $recordStrucure->getStructure();
		foreach($stucturedValues as $blockLabel=>$fieldList) {
			$summaryInfo[$blockLabel] = $fieldList;
			break;
		}

		$detailViewLinkParams = array('MODULE'=>$moduleName,'RECORD'=>$recordId);
		$detailViewLinks = $detailViewModel->getDetailViewLinks($detailViewLinkParams);
		$navigationInfo = ListViewSession::getListViewNavigation($recordId);

		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('NAVIGATION', $navigationInfo);

		//Intially make the prev and next records as null
		$prevRecordId = null;
		$nextRecordId = null;
		$found = false;
		if ($navigationInfo) {
			foreach($navigationInfo as $page=>$pageInfo) {
				foreach($pageInfo as $index=>$record) {
					//If record found then next record in the interation
					//will be next record
					if($found) {
						$nextRecordId = $record;
						break;
					}
					if($record == $recordId) {
						$found = true;
					}
					//If record not found then we are assiging previousRecordId
					//assuming next record will get matched
					if(!$found) {
						$prevRecordId = $record;
					}
				}
				//if record is found and next record is not calculated we need to perform iteration
				if($found && !empty($nextRecordId)) {
					break;
				}
			}
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		if(!empty($prevRecordId)) {
			$viewer->assign('PREVIOUS_RECORD_URL', $moduleModel->getDetailViewUrl($prevRecordId));
		}
		if(!empty($nextRecordId)) {
			$viewer->assign('NEXT_RECORD_URL', $moduleModel->getDetailViewUrl($nextRecordId));
		}

		$viewer->assign('MODULE_MODEL', $detailViewModel->getModule());
		$viewer->assign('DETAILVIEW_LINKS', $detailViewLinks);

		$viewer->assign('IS_EDITABLE', $detailViewModel->getRecord()->isEditable($moduleName));
		$viewer->assign('IS_DELETABLE', $detailViewModel->getRecord()->isDeletable($moduleName));

        $linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
		$linkModels = $detailViewModel->getSideBarLinks($linkParams);

        $viewer->assign('QUICK_LINKS', $linkModels);
		$viewer->assign('NO_SUMMARY', true);

		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			echo $this->invokeExposedMethod($mode, $request);
			return;
		}

		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		if(!empty($recordId)){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
			$activityType = $recordModel->getType();
		}
		
		if ($currentUserModel->get('default_record_view') === 'Summary' && $activityType === 'Events') {
			echo $this->showModuleBasicView($request);
		} else {
			echo $this->showModuleDetailView($request);
		}
	}

	
	
	
	
	function showModuleSummaryView($request) {
		$recordId = $request->get('record');
		//$moduleName = $request->getModule();
		//SG1506 ce qui suit est un peu tordu mais permet d'avoir un $recordModel qui soit un Events et pas un Calendar (nom du module dans la requête)
		$recordAsCalendarModel = Vtiger_Record_Model::getInstanceById($recordId);
		$moduleName = $recordAsCalendarModel->getType();

		$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		
		
		$recordModel = $this->record->getRecord();
				
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);
		
		$moduleModel = $recordModel->getModule();
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORD', $recordModel);
		$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());

		$viewer->assign('MODULE_NAME', $moduleName);
		$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
		$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('RELATED_ACTIVITIES', $this->getActivities($request));

		
		
		return $viewer->view('ModuleSummaryView.tpl', $moduleName, true);
	}
	
	
	
	/**
	 * Function shows the entire detail for the record
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

        if(!empty($recordId)){
		$isCreation = false;
            $recordModel = Vtiger_Record_Model::getInstanceById($recordId);
            $activityType = $recordModel->getType();
            if($activityType == 'Events')
                $moduleName = 'Events';
        }
	else {
		$isCreation = true;
	}

		$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		$recordModel = $detailViewModel->getRecord();
		$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_DETAIL);
		$structuredValues = $recordStrucure->getStructure();
		$moduleModel = $recordModel->getModule();

        if ($moduleName == 'Events'){
		
		
        $relatedContacts = $recordModel->getRelatedContactInfo();
            foreach($relatedContacts as $index=>$contactInfo) {
                $contactRecordModel = Vtiger_Record_Model::getCleanInstance('Contacts');
                $contactRecordModel->setId($contactInfo['id']);
                $contactInfo['_model'] = $contactRecordModel;
                $relatedContacts[$index] = $contactInfo;
		}
	//SG1409	
	$relatedVehicules = $recordModel->getRelatedVehiculeInfo();
            foreach($relatedVehicules as $index=>$vehiculeInfo) {
                $vehiculeRecordModel = Vtiger_Record_Model::getCleanInstance('Vehicules');
                $vehiculeRecordModel->setId($vehiculeInfo['id']);
                $vehiculeInfo['_model'] = $vehiculeRecordModel;
                $relatedVehicules[$index] = $vehiculeInfo;
		}	
	//SG1409END
	
        }
	else{		
		
            $relatedContacts = array();
	    //SG1409
	    $relatedVehicules = array();
        }
	
	$viewer = $this->getViewer($request);
	

	
	
	$viewer->assign('CREATION', $isCreation);
	$viewer->assign('RECORD', $recordModel);
	$viewer->assign('RECORD_STRUCTURE', $structuredValues);
	$viewer->assign('BLOCK_LIST', $moduleModel->getBlocks());
	$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStrucure);
	$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
	$viewer->assign('MODULE_NAME', $moduleName);
		
	$viewer->assign('RELATED_CONTACTS', $relatedContacts);
	$viewer->assign('RELATED_VEHICULES', $relatedVehicules);	
		
	$viewer->assign('IS_AJAX_ENABLED', $this->isAjaxEnabled($recordModel));
	$viewer->assign('RECURRING_INFORMATION', $recordModel->getRecurringDetails());

        if($moduleName=='Events') {
            $currentUser = Users_Record_Model::getCurrentUserModel();
            $accessibleUsers = $currentUser->getAccessibleUsers();
            $viewer->assign('ACCESSIBLE_USERS', $accessibleUsers);
            $viewer->assign('INVITIES_SELECTED', $recordModel->getInvities());
        }

		return $viewer->view('DetailViewFullContents.tpl',$moduleName,true);
	}

	
	/**
	 *SGNOW copie de générique
	 * Function returns related records based on related moduleName
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showRelatedRecords(Vtiger_Request $request) {
		$parentId = $request->get('record');			
		$recordModel = Vtiger_Record_Model::getInstanceById($parentId);		
		$moduleName = $recordModel->getType();
				 
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$orderby = $request->get('orderby');
		$sortorder = $request->get('sortorder');
		
		$relatedModuleName = $request->get('relatedModule');
		
		
		//$detailViewModel = Vtiger_DetailView_Model::getInstance($moduleName, $parentId);
		//$recordModel = $detailViewModel->getRecord();
		
		
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}
		if(!empty($orderby)) {
			$pagingModel->set('orderby', $orderby);
		}
		if(!empty($sortorder)) {
			$pagingModel->set('sortorder', $sortorder);
		}
		
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		
		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();
		
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE' , $relatedModuleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}
	
	/**
	 * Function returns related records
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showRelatedList(Vtiger_Request $request) {
		
		$parentId = $request->get('record');			
		$recordModel = Vtiger_Record_Model::getInstanceById($parentId);		
		$moduleName = $recordModel->getType();	
		//$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$targetControllerClass = null;

		// Added to support related list view from the related module, rather than the base module.
		try {
			$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'In'.$moduleName.'Relation', $relatedModuleName);
		}catch(AppException $e) {
			try {
				// If any module wants to have same view for all the relation, then invoke this.
				$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'InRelation', $relatedModuleName);
			}catch(AppException $e) {
				// Default related list
				$targetControllerClass = Vtiger_Loader::getComponentClassName('View', 'RelatedList', $moduleName);
			}
		}
		if($targetControllerClass) {
			$targetController = new $targetControllerClass();
			return $targetController->process($request);
		}
	}
	
	
	
	/**
	 * Function shows basic detail for the record
	 * @param <type> $request
	*/
	
/* SGNOW	
	function showModuleBasicView($request) {
		return $this->showModuleDetailView($request);
	}
 */
	/**
	 * Function to get Ajax is enabled or not
	 * @param Vtiger_Record_Model record model
	 * @return <boolean> true/false
	 */
	function isAjaxEnabled($recordModel) {
		return false;
	}

}

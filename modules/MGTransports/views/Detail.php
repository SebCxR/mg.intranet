<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class MGTransports_Detail_View extends Vtiger_Detail_View {
	
	/**
	 * Function returns related records based on related moduleName
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	
	function showRelatedRecords(Vtiger_Request $request) {
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$relatedModuleName = $request->get('relatedModule');
		$moduleName = $request->getModule();
		if(empty($pageNumber)) {
			$pageNumber = 1;
		}
		
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}
		
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		$models = $relationListView->getEntries($pagingModel);
		$header = $relationListView->getHeaders();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('SOURCE_RECORD' , $parentId);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE' , $relatedModuleName);
		
		$viewer->assign('PAGING_MODEL', $pagingModel);
		
		//SG1411
		if ($relatedModuleName == 'Vehicules' || $relatedModuleName == 'MGChauffeurs') {
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);		
			$basebusyList = $relatedModuleModel->getBusylist($parentId);		
		
			foreach ($basebusyList as $vehiculeId=>$eventsarray) {
				foreach ($eventsarray as $eventid=>$eventinfo) {
					if ($eventid == $parentId) {
						unset($basebusyList[$vehiculeId][$eventid]);
					}		
				}
				if (empty($basebusyList[$vehiculeId])) {
					unset($basebusyList[$vehiculeId]);
				}				
			}
			
		$viewer->assign('BUSYLIST' , $basebusyList);
		
		}
		//SG1501
		if ($relatedModuleName == 'Contacts') {				
		$mainContactId = getSingleFieldValue('vtiger_mgtransports','contactid','mgtransportsid',$parentId);		
		$viewer->assign('MAINCONTACT',$mainContactId);	
		}
		
		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}
	
}

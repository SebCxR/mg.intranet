<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Events_DetailView_Model extends Calendar_DetailView_Model {

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */	
	public function getDetailViewRelatedLinks() {
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();

		//$recordModel = Vtiger_Record_Model::getInstanceById($recordId);
		$activityType = $recordModel->getType();
		
		$parentModuleModel = $this->getModule();
		$relatedLinks = array();
		
		if($activityType == 'Events') {
			$relatedLinks = array(array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('SINGLE_' . $activityType, $moduleName) . ' ' . vtranslate('LBL_SUMMARY', $moduleName),
				'linkKey' => 'LBL_RECORD_SUMMARY',
				'linkurl' => $recordModel->getDetailViewUrl() . '&mode=showDetailViewByMode&requestMode=summary',
				'linkicon' => ''
			));
		}
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => vtranslate('SINGLE_'.$activityType, $moduleName).' '. vtranslate('LBL_DETAILS', $moduleName),
				'linkurl' => $recordModel->getDetailViewUrl().'&mode=showDetailViewByMode&requestMode=full',
				'linkicon' => ''
		);
		
		$moduleModel = Vtiger_Module_Model::getInstance($activityType);
		$relationModels = $moduleModel->getRelations();

		
		
		foreach($relationModels as $relation) {
			
			//var_dump($relation);
			
			
			//TODO : Way to get limited information than getting all the information
			$link = array(
					'linktype' => 'DETAILVIEWRELATED',
					'linklabel' => vtranslate($relation->get('label'),$moduleName),
					'linkurl' => $relation->getListUrl($recordModel),
					'linkicon' => ''
			);
			$relatedLinks[] = $link;
		}

		return $relatedLinks;
	}
	
}

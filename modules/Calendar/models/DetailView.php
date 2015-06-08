<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_DetailView_Model extends Vtiger_DetailView_Model {
	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */	
	public function getDetailViewRelatedLinks() {
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
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
	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 *
	 * Ajout des blocks Widgets
	 * La table _Links ne semble pas être utilisée pour initialiser le tableau
	 * nécessite l'existence des fichiers Vtiger/%RelatedModule%SummaryWidgetContents.tpl (tout attaché)
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = array();
		$widgets = array();
		$usersInstance = Vtiger_Module_Model::getInstance('Users');
		if($userPrivilegesModel->hasModuleActionPermission($usersInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_INVITEES',
					'linkName'	=> $usersInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Users&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Select'),
					'actionlabel'	=>	array('LBL_Complete_List'),
					'actionURL' =>	$usersInstance->getListViewUrl()
			);
		}
		$documentsInstance = Vtiger_Module_Model::getInstance('Vehicules');
		if($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_VEHICULES',
					'linkName'	=> $documentsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Vehicules&mode=showRelatedRecords&page=1&limit=25',
					'action'	=>	array('Select'),
					'actionlabel'	=>	array('LBL_Complete_List'),
					'actionURL' =>	$documentsInstance->getListViewUrl()
			);
		}		
		$contactsInstance = Vtiger_Module_Model::getInstance('Contacts');
		if($userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_CONTACTS',
					'linkName'	=> $contactsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Contacts&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Select'),
					'actionlabel'	=>	array('LBL_Complete_List'),
					'actionURL' =>	$contactsInstance->getListViewUrl()
			);
		}
		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}
		return $widgetLinks;
	}
}

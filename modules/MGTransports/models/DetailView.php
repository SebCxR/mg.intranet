<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class MGTransports_DetailView_Model extends Vtiger_DetailView_Model {

	
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
		$widgetLinks = parent::getWidgets();
		$widgets = array();

		$documentsInstance = Vtiger_Module_Model::getInstance('Vehicules');
		if($userPrivilegesModel->hasModuleActionPermission($documentsInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Véhicules',
					'linkName'	=> $documentsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Vehicules&mode=showRelatedRecords&page=1&limit=25',
					'action'	=>	array('Select'),
					'actionURL' =>	$documentsInstance->getListViewUrl()
			);
		}

		$contactsInstance = Vtiger_Module_Model::getInstance('Contacts');
		if($userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Contacts',
					'linkName'	=> $contactsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Contacts&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Select'),
					'actionURL' =>	$contactsInstance->getListViewUrl()
			);
		}
		
		$productsInstance = Vtiger_Module_Model::getInstance('Products');
		if($userPrivilegesModel->hasModuleActionPermission($productsInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Articles',
					'linkName'	=> $productsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Products&mode=showRelatedRecords&page=1&limit=15',
					'action'	=>array('Select'),
					'actionURL' =>	$productsInstance->getListViewUrl()
			);
		}
		
		//TODO showRelatedRecords ne cherche pas au bon endroit pour Users
		$usersInstance = Vtiger_Module_Model::getInstance('MGChauffeurs');
		if($userPrivilegesModel->hasModuleActionPermission($usersInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Chauffeurs',
					'linkName'	=> $usersInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=MGChauffeurs&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Select'),
					'actionURL' =>	$usersInstance->getListViewUrl()
			);
		}

		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}
}

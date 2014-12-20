<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger ListView Model Class
 */
class Vehicules_ListView_Model extends Vtiger_ListView_Model {

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	//public function getListViewHeaders() {
	//	$headerFields = parent::getListViewHeaders();
	//	
	//	$module = $this->getModule();
	//	$headerFields['calcolor'] = Vtiger_Field_Model::getInstance('calcolor', $module);
	//	
	//	return $headerFields;
	//}

	//public function getQuery(){
	//	$query = parent::getQuery();
	//	// ajout de la colonne calcolor
	//	$query = preg_replace('/^SELECT\s/', 'SELECT vtiger_vehicules.calcolor, ', $query);
	//	return $query ;
	//}
	
	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		
		$queryGenerator = $this->get('query_generator');
		$listFields = $queryGenerator->getFields();
		$listFields[] = 'calcolor';
		$queryGenerator->setFields($listFields);		
		
		return parent::getListViewEntries($pagingModel);
	}
	
	/**
	 * SG copie du generique de Vtiger_ListView_Model
	 * SGTODO 
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $value - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstanceForPopup($value) {
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($value);

		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);

		$listFields = $moduleModel->getPopupFields();
		$listFields[] = 'id';
		$queryGenerator->setFields($listFields);

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}


}

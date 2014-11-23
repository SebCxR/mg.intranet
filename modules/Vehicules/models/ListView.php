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
	public function getListViewHeaders() {
		$headerFieldModels = parent::getListViewHeaders();

		$temp = array();
		$field1 = new Vtiger_Field_Model();
		
		$field1->set('name', 'full_vehicule_name');
		$field1->set('column', 'full_vehicule_name');
		$field1->set('label', 'LBL_VEHIC_POPUP_NAME_HEADER');

		$temp['full_vehicule_name'] = $field1;
		
		$headerFieldModels = array_merge($temp,$headerFieldModels);
		
		return $headerFieldModels;
	}



	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		
		$listViewRecordModels = parent::getListViewEntries($pagingModel);		
		
		return $listViewRecordModels;
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
	}

}

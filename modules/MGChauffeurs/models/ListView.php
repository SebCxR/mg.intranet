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
class MGChauffeurs_ListView_Model extends Vtiger_ListView_Model {

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
		$headerFieldModels = parent::getListViewHeaders();

		$temp = array();
		$field1 = new Vtiger_Field_Model();
		
		$field1->set('name', 'colored_name');
		$field1->set('column', 'colored_name');
		$field1->set('label', 'LBL_CHAUFFEUR_COLORED_NAME_HEADER');

		$temp['colored_name'] = $field1;
		
		$headerFieldModels = array_merge($temp,$headerFieldModels);
		
		return $headerFieldModels;
	}


}

<?php
/*+***********************************************************************************
 * 
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

		$module = $this->getModule();
		$headerFields['calcolor'] = Vtiger_Field_Model::getInstance('calcolor', $module);
	
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
		$queryGenerator = $this->get('query_generator');
		$listFields = $queryGenerator->getFields();
		$listFields[] = 'calcolor';
		$queryGenerator->setFields($listFields);		
		
		return parent::getListViewEntries($pagingModel);
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		// ajout de la colonne calcolor
		$listQuery = preg_replace('/^SELECT\s/', 'SELECT vtiger_vehicules.calcolor, ', $listQuery);
		return $listQuery;
	}

}
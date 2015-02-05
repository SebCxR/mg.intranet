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
		
		
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		//List view will be displayed on recently created/modified records
		if(empty($orderBy) && empty($sortOrder) && $moduleName != "Users"){
			$orderBy = 'modifiedtime';
			$sortOrder = 'DESC';
		}

		if(!empty($orderBy)){
			//SG1502 manage full_vehicule_name
			if ($orderBy == 'full_vehicule_name') {
				$orderBy = 'vehicule_name';
			}
			
		    $columnFieldMapping = $moduleModel->getColumnFieldMapping();
		    $orderByFieldName = $columnFieldMapping[$orderBy];		    
		    $orderByFieldModel = $moduleModel->getField($orderByFieldName);
		    if($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE){
			//IF it is reference add it in the where fields so that from clause will be having join of the table
			$queryGenerator = $this->get('query_generator');
			$queryGenerator->addWhereField($orderByFieldName);
			//$queryGenerator->whereFields[] = $orderByFieldName;
		    }
		}
		$listQuery = $this->getQuery();

		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		if(!empty($orderBy)) {
			if($orderByFieldModel && $orderByFieldModel->isReferenceField()){
			    $referenceModules = $orderByFieldModel->getReferenceList();
			    $referenceNameFieldOrderBy = array();
			    foreach($referenceModules as $referenceModuleName) {
				$referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
				$referenceNameFields = $referenceModuleModel->getNameFields();
				$columnList = array();
				foreach($referenceNameFields as $nameField) {
				    $fieldModel = $referenceModuleModel->getField($nameField);
				    $columnList[] = $fieldModel->get('table').$orderByFieldModel->getName().'.'.$fieldModel->get('column');
				}
				if(count($columnList) > 1) {
				    $referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users').' '.$sortOrder;
				} else {
				    $referenceNameFieldOrderBy[] = implode('', $columnList).' '.$sortOrder ;
				}
			    }
			    $listQuery .= ' ORDER BY '. implode(',',$referenceNameFieldOrderBy);
			}else{
			    $listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;
			}
		}

		$viewid = ListViewSession::getCurrentView($moduleName);
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);

		$listResult = $db->pquery($listQuery, array());

		$listViewRecordModels = array();
		$listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus,$moduleName, $listResult);

		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit){
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}
		return $listViewRecordModels;
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		// ajout de la colonne calcolor
		$listQuery = preg_replace('/^SELECT\s/', 'SELECT vtiger_vehicules.calcolor, ', $listQuery);
		return $listQuery;
	}

}
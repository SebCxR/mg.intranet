<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Events_RelationListView_Model extends Calendar_RelationListView_Model {
	
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * */
	public function getHeaders() {
		$headerFields = array();		
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
						
		switch($relatedModuleModel->name){
		  case "Vehicules":
			$summaryFieldsList = $relatedModuleModel->getSummaryViewFieldsList();
			$headerFields = array();
			
			//SGNOW tout ce qui suit remplace parent::getHeaders();
			if(count($summaryFieldsList) > 0) {
				$vehicRelatedListHeaders = $relatedModuleModel->getRelatedListFields();
				foreach($vehicRelatedListHeaders as $fieldName) {
					if (array_key_exists($fieldName,$summaryFieldsList)||$fieldName=='full_vehicule_name')
						$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
					
				}
			} else {
				$headerFieldNames = $relatedModuleModel->getRelatedListFields();
				foreach($headerFieldNames as $fieldName) {
					$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
			}		
			
		     // $headerFields = parent::getHeaders();
		      
			unset($headerFields['calcolor']);
			unset($headerFields['isrented']);
			unset($headerFields['vehicule_name']);
			unset($headerFields['vehicule_owner']);
		    break;
		case "Users":
			$headerFields = parent::getHeaders();
		    break;
		  default:
		    return parent::getHeaders();
		}

		return $headerFields;
	}
	
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$relationModule = $relationModel->getRelationModuleModel();
		
		if ($relationModule->name == 'Users') {
			
			$db = PearDatabase::getInstance();
			$parentRecordModel = $this->getParentRecordModel();
			$parentModule = $parentRecordModel->getModule();
			$relatedColumnFields = $relationModule->getConfigureRelatedListFields();
			
			if(count($relatedColumnFields) <= 0){
				$relatedColumnFields = $relationModule->getRelatedListFields();
			}
			$query = $this->getRelationQuery();
			
			$startIndex = $pagingModel->getStartIndex();
			$pageLimit = $pagingModel->getPageLimit();

			$limitQuery = $query .' LIMIT '.$startIndex.','.$pageLimit;
			$result = $db->pquery($limitQuery, array());
		
			$relatedRecordList = array();
			
			for($i=0; $i< $db->num_rows($result); $i++ ) {
				$row = $db->fetch_row($result,$i);
				$newRow = array();			
				foreach($row as $col=>$val){
					if(array_key_exists($col,$relatedColumnFields)){
					$newRow[$relatedColumnFields[$col]] = $val;
					}
				}
				$record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
				$record->setData($newRow)->setModuleFromInstance($relationModule);
				$record->setId($row['id']);			
				$relatedRecordList[$row['id']] = $record;
			}
			$pagingModel->calculatePageRange($relatedRecordList);

			$nextLimitQuery = $query. ' LIMIT '.($startIndex+$pageLimit).' , 1';
			$nextPageLimitResult = $db->pquery($nextLimitQuery, array());
			if($db->num_rows($nextPageLimitResult) > 0){
				$pagingModel->set('nextPageExists', true);
			}else{
			$pagingModel->set('nextPageExists', false);
			}		
		}
		else $relatedRecordList = parent::getEntries($pagingModel);
		
		return $relatedRecordList;
	}
	
	
	
	
}

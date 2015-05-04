<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class MGTransports_RelationListView_Model extends Vtiger_RelationListView_Model {
	
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * */
	public function getHeaders() {
		$headerFields = array();
		
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		switch($relatedModuleModel->name){
		  case "MGChauffeurs": 	    
		      $headerFields = parent::getHeaders();	      
			unset($headerFields['uicolor']);
		    break;
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
		  default:
		    return parent::getHeaders();
		}

		return $headerFields;
	}
}

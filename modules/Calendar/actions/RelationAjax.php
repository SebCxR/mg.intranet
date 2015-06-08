<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_RelationAjax_Action extends Vtiger_RelationAjax_Action {
	
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function addRelation($request) {
		$sourceRecordId = $request->get('src_record');
		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');
		
		$srcRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId);
		$activityType = $srcRecordModel->getType();
		
		
		$sourceModuleModel = Vtiger_Module_Model::getInstance($activityType);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId,$relatedRecordId);
		}
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function deleteRelation($request) {
		
		$sourceRecordId = $request->get('src_record');

		$srcRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId);
		$activityType = $srcRecordModel->getType();
		
		$relatedModule = $request->get('related_module');
		if(!$relatedModule)/* ED141025 */
			throw new Exception('RelationAjax.php->deleteRelation() : Parameter "related_module" is missing or empty');
		$relatedRecordIdList = $request->get('related_record_list');
		
		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($activityType);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$response = $relationModel->deleteRelation($sourceRecordId,$relatedRecordId);
		}
		echo $response;
	}
	
	
}

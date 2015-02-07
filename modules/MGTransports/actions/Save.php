<?php
/* +***********************************************************************************
 * ED150207
 * *********************************************************************************** */

class MGTransports_Save_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {

		$recordModel = $this->saveRecord($request);
		if($request->get('relationOperation')) {
			$parentModuleName = $request->get('sourceModule');
			$parentRecordId = $request->get('sourceRecord');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentRecordId, $parentModuleName);
			//TODO : Url should load the related list instead of detail view of record
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('returnToList')) {
			$loadUrl = $recordModel->getModule()->getListViewUrl();
		} elseif( ! $request->get('record') ){	// duplicate
			if( $request->get('isDuplicateFrom') ) // modules\Vtiger\views\Edit.php and vlayout\modules\Vtiger\EditViewBlocks.tpl add this <input/>
				$duplicateFrom = $request->get('isDuplicateFrom');
			else
				$duplicateFrom = preg_replace('/^.*&record=(\d+).*$/','$1',$_SERVER['HTTP_REFERER']);
			if($duplicateFrom){
				$moduleName = $request->get('sourceModule');
				$recordModel->duplicateRelatedRecords($duplicateFrom, $recordModel, array('Vehicules', 'Products', 'MGChauffeurs', 'Contacts'));
				//die();
			}
			$loadUrl = $recordModel->getDetailViewUrl();
		} else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}
		header("Location: $loadUrl");
	}
}

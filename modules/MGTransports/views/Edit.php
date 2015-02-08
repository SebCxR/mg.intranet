<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class MGTransports_Edit_View extends Vtiger_Edit_View {
    	function __construct() {
		parent::__construct();
	}

	/* ED150207
	 * modifie la date du record modèle de la duplication en ajoutant un jour
	 */
	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		if(!empty($record) && $request->get('isDuplicate') == true) {
			if(!$this->record)
				$this->record = Vtiger_Record_Model::getInstanceById($record, $moduleName);
			$date = new DateTime($this->record->get('datetransport'));
			$date->modify( '+1 day' );
			$this->record->set('datetransport', $date->format('Y-m-d'));
		}
		return parent::process($request);
	}
}
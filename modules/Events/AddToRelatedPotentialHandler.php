<?php
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/

require_once('include/utils/utils.php');
//SGNOW TODO copie 

class AddToRelatedPotentialHandler extends VTEventHandler {
	
	public function handleEvent($handlerType, $entityData){
		global $adb;
		$moduleName = $entityData->getModuleName();
		if ($moduleName == 'Events' || $moduleName=='Calendar') {
			$activityId = $entityData->getId();
                        
			$data = $entityData->getData();
			if(($data['parent_id']) && ($data['parent_id'] != '')) {
				
				$parentid = $data['parent_id'];
				if (VTigerCRMObject::getSEType($parentid)=='Potentials' && $data('cf_766')!='')
				{
				
				//TODO SGNOW get new ammount for parent potential and update value in vtiger_potentialscf column cf_768
					
				}
				else {};
				/*
				$frequency = $data['recurring_frequency'];
				$startPeriod = getValidDBInsertDateValue($data['start_period']);
				$endPeriod = getValidDBInsertDateValue($data['end_period']);
				$paymentDuration = $data['payment_duration'];
				$invoiceStatus = $data['invoicestatus'];
				if (isset($frequency) && $frequency != '' && $frequency != '--None--') {
					$check_query = "SELECT * FROM vtiger_invoice_recurring_info WHERE salesorderid=?";
					$check_res = $adb->pquery($check_query, array($soId));
					$noofrows = $adb->num_rows($check_res);
					if ($noofrows > 0) {
						$row = $adb->query_result_rowdata($check_res, 0);
						$query = "UPDATE vtiger_invoice_recurring_info SET recurring_frequency=?, start_period=?, end_period=?, payment_duration=?, invoice_status=? WHERE salesorderid=?";
						$params = array($frequency,$startPeriod,$endPeriod,$paymentDuration,$invoiceStatus,$soId);
					} else {
						$query = "INSERT INTO vtiger_invoice_recurring_info VALUES (?,?,?,?,?,?,?)";
						$params = array($soId,$frequency,$startPeriod,$endPeriod,$startPeriod,$paymentDuration,$invoiceStatus);
					}
					$adb->pquery($query, $params);
				}
			} else {
				$query = "DELETE FROM vtiger_invoice_recurring_info WHERE salesorderid = ?";
				$adb->pquery($query, array($soId));	
			*/
		}
	}
    } 
}

?>
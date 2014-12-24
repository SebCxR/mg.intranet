<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_UpdateFromCalendar_Action extends Calendar_Save_Action {

	public function process(Vtiger_Request $request) {
			
		$vtigertype = $request->get('vtigertype');
		$activityid = $request->get('activityid');
		$dstart = $request->get('startdate');
		$tstart = $request->get('starttime');
		$dend = $request->get('enddate');
		$tend = $request->get('endtime');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		
		if ($tstart &&  $tstart) {
			$dateTimeFieldInstance = new DateTimeField($dstart . ' ' . $tstart);
		}
		else {
			$dateTimeFieldInstance = new DateTimeField($dstart . ' ' . $tstart);
			
		}
		$dbinsertDateTimeString = $dateTimeFieldInstance->getDBInsertDateTimeValue($currentUser);
		
		$dateTimeComponents = explode(' ',$dbinsertDateTimeString);
		
		$dstart = $dateTimeComponents[0];
		$tstart = $dateTimeComponents[1];
		
		
		$dateTimeFieldInstance = new DateTimeField($dend . ' ' . $tend);
		$dbinsertDTString = $dateTimeFieldInstance->getDBInsertDateTimeValue($currentUser);		
		$dTComponents = explode(' ',$dbinsertDTString);
		
		$dend = $dTComponents[0];
		$tend = $dTComponents[1];	
			
		$db = PearDatabase::getInstance();
		
		switch ($vtigertype) {
			
			case 'MGTransports' :
				$updatequery = "UPDATE vtiger_mgtransports SET datetransport=? WHERE mgtransportsid=?";
				$updtparams = array($dstart,$activityid);				
				
				break;			
			default :
				$updatequery = "UPDATE vtiger_activity SET date_start=?,  due_date=?, time_start=?, time_end=?  WHERE activityid=?";
				$updtparams = array($dstart, $dend, $tstart,$tend,$activityid);
			break;
						
		}
		// update start time and date, end time and date for the activity's record in db
		//$updatequery = "UPDATE vtiger_activity SET date_start=?,  due_date=?, time_start=?, time_end=?  WHERE activityid=?";
		//$updtparams = array($dstart, $dend, $tstart,$tend,$activityid);
		
		$result = $db->pquery($updatequery, $updtparams);
		
		
		
				
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
			
			}
}



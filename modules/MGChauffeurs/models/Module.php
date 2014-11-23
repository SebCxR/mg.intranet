<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class MGChauffeurs_Module_Model extends Vtiger_Module_Model {
	
	
	// Function returns the Busylist on the date of the mgevent
	// @param <int> $mgeventId : id of the event or transport considered, needed to get the date
	// @return <Array>
	public function getBusylist($mgevent) {
		
		$busylist = $this->getBusylistOnTransports($mgevent);
		
		return $busylist;
	}
	
	
	
	
	// Function returns the Busylist on the date of the mgtransport
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// @return <Array>
	public function getBusylistOnTransports($mgtransportId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $currentUser->getId();
		
		$db = PearDatabase::getInstance();
		
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_mgtransports.subject, vtiger_crmentityrel.relcrmid as chauffeurid, vtiger_crmentityrel.relmodule  FROM vtiger_mgtransports"
					." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_mgtransports.mgtransportsid"
					." INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = vtiger_mgtransports.mgtransportsid";

		$query .= " WHERE vtiger_crmentity.deleted=0"
			." AND (vtiger_mgtransports.datetransport = (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			." AND vtiger_crmentityrel.relmodule = ?";	

		$params = array($mgtransportId,$this->getName());



		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		//$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$transporthref = "index.php?module=MGTransports".
					"&view=Detail&record=".$row['crmid'] ;
					
			$temparray = array('modulename'=>'MGTransports',
					'label'=>$row['subject'],
					'href'=>$transporthref
					);
			
			if (!$busyList[$row['chauffeurid']]) {	
			$busyList[$row['chauffeurid']]=array($row['crmid']=> $temparray);
			}
			
			else {				
			$busyList[$row['chauffeurid']][$row['crmid']] = $temparray ;
			}		
					
		}
		
		return $busyList;	
	}
	

}
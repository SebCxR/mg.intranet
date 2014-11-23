<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Vehicules_Module_Model extends Vtiger_Module_Model {
	
	
	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		
		$relatedModuleName = $relatedModule->getName();
		// this gets only activity, no tasks
		if ($functionName === 'get_activities' && ($relatedModuleName == 'Events' || $relatedModuleName == '')) {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_vehiculeactivityrel.vehiculeid,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_cntactivityrel.contactid,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS eventstatus
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						
						LEFT JOIN vtiger_vehiculeactivityrel ON vtiger_vehiculeactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid

						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						
							WHERE vtiger_vehiculeactivityrel.vehiculeid = ".$recordId." AND vtiger_crmentity.deleted = 0
								AND vtiger_activity.activitytype <> 'Emails'
								AND vtiger_activity.activitytype <> 'Tasks'";

			$relatedModuleName = $relatedModule->getName();
			
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		}
		else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}
		
		return $query;
	}
	
	// Function returns the Busylist on the date of the mgevent
	// @param <int> $mgeventId : id of the transport being built, needed to get the date considered when checking engagement
	// @return <Array>
	public function getBusylist($mgeventId) {
		
		$busylist = $this->getBusylistOnTransports($mgeventId);
		
		return $busylist;
	}

	
	// Function returns the Busylist on the date of the mgtransport
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// @return <Array>
	public function getBusylistOnTransports($mgtransportId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $currentUser->getId();
		$busyList = array();
		$db = PearDatabase::getInstance();
		
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_mgtransports.subject, vtiger_crmentityrel.relcrmid as vehiculeid, vtiger_crmentityrel.relmodule  FROM vtiger_mgtransports"
					." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_mgtransports.mgtransportsid"
					." LEFT JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = vtiger_mgtransports.mgtransportsid";

		$query .= " WHERE vtiger_crmentity.deleted=0"
			." AND (vtiger_mgtransports.datetransport = (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			." AND vtiger_crmentityrel.relmodule = ?";	

		$params = array($mgtransportId,$this->getName());


		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$transporthref = "index.php?module=MGTransports".
					"&view=Detail&record=".$row['crmid'] ;
					
			$temparray = array('modulename'=>'MGTransports',
					'label'=>$row['subject'],
					'href'=>$transporthref
					);
			
			if (!$busyList[$row['vehiculeid']]) {	
			$busyList[$row['vehiculeid']]=array($row['crmid']=> $temparray);
			}
			
			else {				
			$busyList[$row['vehiculeid']][$row['crmid']] = $temparray ;
			}
			
			
		}
		
		return $busyList;	
	}
	
	
	
	
	/*
	//SG copy from Contacts
	// Function returns the Calendar Events for the module
	// @param <Vtiger_Paging_Model> $pagingModel
	// @return <Array>
	
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.setype, vtiger_activity.* FROM vtiger_activity"
					." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid"
					." INNER JOIN vtiger_vehiculeactivityrel ON vtiger_vehiculeactivityrel.activityid = vtiger_activity.activityid"
	//				INNER JOIN vtiger_crmentity AS crmentity2 ON vtiger_cntactivityrel.contactid = crmentity2.crmid AND crmentity2.deleted = 0 AND crmentity2.setype = ?
					." LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE vtiger_crmentity.deleted=0
					AND (vtiger_activity.activitytype NOT IN ('Emails'))
					AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held'))";

		if ($recordId) {
			$query .= " AND vtiger_cntactivityrel.contactid = ?";
		} elseif ($mode === 'upcoming') {
			$query .= " AND due_date >= '$currentDate'";
		} elseif ($mode === 'overdue') {
			$query .= " AND due_date < '$currentDate'";
		}

		$params = array($this->getName());
		if ($recordId) {
			array_push($params, $recordId);
		}

		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND vtiger_crmentity.smownerid = ?";
				array_push($params, $user);
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
			$model->setId($row['crmid']);
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	*/
	
	

}
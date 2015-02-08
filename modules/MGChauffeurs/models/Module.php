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
		
		$busyontransportslist = $this->getBusylistOnTransports($mgevent);
		$busyoneventslist = $this->getBusylistOnEvents($mgevent);
		$busylist = array();
		
		if (empty($busyoneventslist)) {
			$busylist = $busyontransportslist;
		}
		else {
		foreach($busyontransportslist as $chauffid => $transportsarray) {
			 if (array_key_exists($chauffid,$busyoneventslist)) {				
				$busylist[$chauffid] = $transportsarray + $busyoneventslist[$chauffid];
			 }
			 else{
				$busylist[$chauffid] = $transportsarray;
			 }
		}
		
		foreach($busyoneventslist as $chauffid => $eventsarray) {			
			 if (!array_key_exists($chauffid,$busylist)) {
				$busylist[$chauffid] = $eventsarray;
			 }
		}
		}
		
		//var_dump($busylist);
		
		return $busylist;
	}
	
	// Function returns the Busylist on the date of the mgtransport
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// @return <Array> (chauffeurid1 => array (eventidx=>arrayofinfo,eventidy=>arrayofinfo,...), chauffeurid2 => array (eventidx=>arrayofinfo,eventidz=>arrayofinfo,...))
	public function getBusylistOnEvents($mgtransportId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $currentUser->getId();
		
		$busyList = array();
		$db = PearDatabase::getInstance();
		
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_activity.subject,vtiger_activity.activitytype, vtiger_mgchauffeurs.mgchauffeursid as chauffeurid FROM vtiger_activity"
					." INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid"
					." INNER JOIN vtiger_invitees ON vtiger_invitees.activityid = vtiger_activity.activityid"
					." INNER JOIN vtiger_mgchauffeurs ON vtiger_mgchauffeurs.userid = vtiger_invitees.inviteeid"
					;

		$query .= " WHERE vtiger_crmentity.deleted=0"
			." AND (vtiger_activity.date_start <= (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			." AND (vtiger_activity.due_date >= (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))"
			;	

		$params = array($mgtransportId,$mgtransportId);

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$eventhref = "index.php?module=Calendar".
					"&view=Detail&record=".$row['crmid'] ;
					
			$temparray = array('modulename'=>'Calendar',
					'label'=>$row['subject'],
					'type'=>$row['activitytype'],
					'href'=>$eventhref
					);
			
			if (!$busyList[$row['chauffeurid']]) {	
			$busyList[$row['chauffeurid']]=array($row['crmid']=> $temparray);
			}
			
			else {				
			$busyList[$row['chauffeurid']][$row['crmid']] = $temparray ;
			}		
					
		}
		//echo $query;
		return $busyList;	
	}
	
	
	
	// Function returns the Busylist on the date of the mgtransport
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// @return <Array> (chauffeurid1 => array (transportidx=>arrayofinfo,transportidy=>arrayofinfo,...), chauffeurid2 => array (transportidx=>arrayofinfo,transportidz=>arrayofinfo,...))
	public function getBusylistOnTransports($mgtransportId) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$user = $currentUser->getId();
		
		$busyList = array();
		
		$db = PearDatabase::getInstance();
		
		$query = "SELECT vtiger_crmentity.crmid as trsprtid, vtiger_mgtransports.subject,vtiger_mgtransports.mgtypetransport, vtiger_crmentityrel.relcrmid, vtiger_crmentityrel.relmodule, vtiger_crmentityrel.module, vtiger_crmentityrel.crmid as crmidbis
			FROM vtiger_mgtransports
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_mgtransports.mgtransportsid
			INNER JOIN vtiger_crmentityrel ON ((vtiger_crmentityrel.crmid = vtiger_mgtransports.mgtransportsid AND vtiger_crmentityrel.relmodule = ?)
							OR (vtiger_crmentityrel.relcrmid = vtiger_mgtransports.mgtransportsid AND vtiger_crmentityrel.module = ?))				
			INNER JOIN vtiger_mgchauffeurs ON (vtiger_mgchauffeurs.mgchauffeursid = vtiger_crmentityrel.relcrmid OR vtiger_mgchauffeurs.mgchauffeursid = vtiger_crmentityrel.relcrmid)
			INNER JOIN vtiger_users ON vtiger_users.id = vtiger_mgchauffeurs.userid
			WHERE vtiger_crmentity.deleted=0
			AND (vtiger_mgtransports.datetransport = (SELECT vtiger_mgtransports.datetransport FROM vtiger_mgtransports WHERE vtiger_mgtransports.mgtransportsid = ?))
			AND vtiger_users.status = 'Active'";
		
		$params = array($this->getName(),$this->getName(),$mgtransportId);

		
		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		//$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$transporthref = "index.php?module=MGTransports".
					"&view=Detail&record=".$row['trsprtid'] ;
					
			$temparray = array('modulename'=>'MGTransports',
					'label'=>$row['subject'],
					'type'=>$row['mgtypetransport'],
					'href'=>$transporthref
					);
			$chauffeurid = '';
			
			if (($row['relmodule']==$this->getName()) && ($row['module']=='MGTransports')) {
				$chauffeurid = $row['relcrmid'];
				}
			if ($row['module']==$this->getName() && $row['relmodule']=='MGTransports') {
			$chauffeurid = $row['crmidbis'];
			}
			
			if (!$busyList[$chauffeurid]) {	
			$busyList[$chauffeurid]=array($row['trsprtid']=> $temparray);
			}
			
			else {				
			$busyList[$chauffeurid][$row['trsprtid']] = $temparray ;
			}
					
		}
		
		return $busyList;	
	}
	
	
	// @param <int> $mgtransportId : id of the transport considered, needed to get the date
	// a skiplist is defined in the function to skip activitytypes we don't want to appear in the return array
	// returns array ("activitytype1"=>array(chauffeurid1=>chauffeurname1,chauffeurid2=>chauffeurname2,...),"activitytype2"=>array(chauffeurid1=>chauffeurname1,chauffeurid2=>chauffeurname2,...))

	function getBusyInActivityTypeArray($mgtid) {
		$moduleName = $this->getName();
		
		$busylist = $this->getBusyListOnEvents($mgtid);
		
		$activitytypes = Vtiger_Util_Helper::getPickListValues('activitytype');
		
		$skiplist = array('Call','Meeting');	    
		
		$output = array();
				   
		foreach ($activitytypes as $activitytype) {
			if(!in_array($activitytype, $skiplist)) {
					$output[$activitytype]=array();				    
					foreach ($busylist as $mgcid => $activities) {						
						$mgcInstance = VTiger_Record_Model::getInstanceById($mgcid,$moduleName);						
						$mgcName = $mgcInstance->get('name');
						
						foreach ($activities as $activityid => $activityinfo) {
							//var_dump('', $mgcName, html_entity_decode( $activityinfo['type'] ),  $activitytype, html_entity_decode( $activityinfo['type'] ) ==  $activitytype );
							if (html_entity_decode( $activityinfo['type'] ) == $activitytype) {
								    if (!$output[$activitytype][$mgcid])
									$output[$activitytype][$mgcid] = $mgcName;
							}						
						}	    
				    }	    				    
			}			
	    }
	    //var_dump($output);
	    return $output;
	}

	
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		
		$relatedModuleName = $relatedModule->getName();
		// this gets only activity, no tasks
		if ($functionName === 'get_related_list' && ($relatedModuleName == 'Events')) {
			
			$chauffeuruserId = getSingleFieldValue('vtiger_mgchauffeurs','userid','mgchauffeursid',$recordId);
			
			$query = "SELECT vtiger_activity.*, vtiger_crmentity.smownerid, vtiger_contactdetails.lastname,
			vtiger_seactivityrel.crmid as parent_id, vtiger_invitees.inviteeid, vtiger_crmentity.crmid
			FROM vtiger_activity
			INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_recurringevents ON vtiger_activity.activityid = vtiger_recurringevents.activityid
			LEFT JOIN vtiger_invitees ON vtiger_activity.activityid = vtiger_invitees.activityid
			LEFT JOIN vtiger_cntactivityrel ON vtiger_activity.activityid = vtiger_cntactivityrel.activityid
			LEFT JOIN vtiger_seactivityrel ON vtiger_activity.activityid = vtiger_seactivityrel.activityid
			LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid
			WHERE vtiger_crmentity.deleted=0 AND vtiger_activity.activityid > 0
			AND vtiger_activity.activitytype NOT IN ('Emails','Task')
			AND vtiger_invitees.inviteeid = ".$chauffeuruserId;

			//vtiger_vehiculeactivityrel.vehiculeid,
			//LEFT JOIN vtiger_vehiculeactivityrel ON vtiger_activity.activityid = vtiger_vehiculeactivityrel.activityid
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

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if ($sourceModule == 'MGTransports' && $record) {	
				$joinusers = " INNER JOIN vtiger_users ON vtiger_users.id = vtiger_mgchauffeurs.userid";
				$condition = "vtiger_users.status = 'Active'";
				//$condition = " vtiger_account.accountid != '$record'";			
			$position = stripos($listQuery, 'where');
			if($position) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . $joinusers . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. $joinusers . ' WHERE ' . $condition;
			}
			return $overRideQuery;	
		}
	}
}
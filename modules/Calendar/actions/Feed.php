<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

vimport ('~~/include/Webservices/Query.php');

class Calendar_Feed_Action extends Vtiger_BasicAjax_Action {

	public function process(Vtiger_Request $request) {
		

		try {
			$result = array();
						
			$start = $request->get('start');
			$end   = $request->get('end');
			$type = $request->get('type');
			$userid = $request->get('userid');
			$mapping = $request->get('mapping');
			//$vehiculeid = $request->get('vehiculeid');
			//$vehiculecolor = $request->get('color');
			//$vehiculetextcolor = $request->get('textColor');
			switch ($type) {
				case 'Events': $this->pullEvents($start, $end, $result, $request->get('cssClass'),$userid,$request->get('color'),$request->get('textColor'));				
						break;
				case 'Tasks': $this->pullTasks($start, $end, $result, $request->get('cssClass')); break;
				case 'Potentials': $this->pullPotentials($start, $end, $result, $request->get('cssClass')); break;
				case 'Contacts':
							if($request->get('fieldname') == 'support_end_date') {
								$this->pullContactsBySupportEndDate($start, $end, $result, $request->get('cssClass'));
							}else{
								$this->pullContactsByBirthday($start, $end, $result, $request->get('cssClass'));
							}
							break;

				case 'Invoice': $this->pullInvoice($start, $end, $result, $request->get('cssClass')); break;
				case 'MultipleEvents' : $this->pullMultipleEvents($start,$end, $result,$request->get('mapping'));break;
				case 'Project': $this->pullProjects($start, $end, $result, $request->get('cssClass')); break;
				case 'ProjectTask': $this->pullProjectTasks($start, $end, $result, $request->get('cssClass')); break;
				case 'Vehicule': $this->pullVehiculeEvents($start, $end, $result,$mapping);						
						break;
				case 'MGChauffeurs': $this->pullMGChauffeurAllActivities($start, $end, $result,$mapping);
						//$this->pullMGChauffeurEvents($start, $end, $result,$mapping);						
						break;
				case 'MGTransports': $this->pullMGTransports($start, $end, $result, $request->get('cssClass')); break;
				case 'Invited' : $this->pullInvitedEvents($start, $end, $result,$mapping);						
						break;
			}
			echo json_encode($result);
		} catch (Exception $ex) {
			echo $ex->getMessage();
		}
	}
    
    protected function getGroupsIdsForUsers($userId) {
        vimport('~~/include/utils/GetUserGroups.php');
        
        $userGroupInstance = new GetUserGroups();
        $userGroupInstance->getAllUserGroups($userId);
        return $userGroupInstance->user_groups;
    }

	protected function queryForRecords($query, $onlymine=true) {
		$user = Users_Record_Model::getCurrentUserModel();
		if ($onlymine) {
            $groupIds = $this->getGroupsIdsForUsers($user->getId());
            $groupWsIds = array();
            foreach($groupIds as $groupId) {
                $groupWsIds[] = vtws_getWebserviceEntityId('Groups', $groupId);
            }
			$userwsid = vtws_getWebserviceEntityId('Users', $user->getId());
            $userAndGroupIds = array_merge(array($userwsid),$groupWsIds);
			$query .= " AND assigned_user_id IN ('".implode("','",$userAndGroupIds)."')";
		}
		// TODO take care of pulling 100+ records
		return vtws_query($query.';', $user);
	}
	
	protected function pullEvents($start, $end, &$result, $cssClass,$userid = false,$color = null,$textColor = 'white') {
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Events');
		$hasCustomField = false;
		if($userid){
			$focus = new Users();
			$focus->id = $userid;
			$focus->retrieve_entity_info($userid, 'Users');
			$user = Users_Record_Model::getInstanceFromUserObject($focus);
			$userName = $user->getName();
			$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);
		}else{
			$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		}
		
		//SG140908 ajout des 'cf_xxx','contact_id', 'activitytype' et 'parent_id' dans la liste de fields du querygenerator
		//$basicfields est le tableau de champs utilisé dans la version originale auquel on a ajouté 'contact_id', 'activitytype','parent_id' et 'vehiculeid'
		//$customfields est la liste des Customfields du module Events
		//$cfarray est un tableau associatif $customfieldname=>$customfieldlabel (par exemple cf_703=>Véhicules")
		//Ces tableaux sont utilisés pour enrichir l'info sur les Events envoyée à fullcalendar.js
		$basicfields = array('subject', 'eventstatus', 'visibility','date_start','time_start','due_date','time_end','assigned_user_id','id','contact_id', 'activitytype','parent_id','vehiculeid');
		$finalfields = array();
		$customfields = array();
		$cfarray = array();
		$this->fillCustomFieldsArrays($customfields,$cfarray,'Events',$hasCustomField);
		
		$finalfields = array_merge($customfields,$basicfields);	
				
		$queryGenerator->setFields($finalfields);
		$query = $queryGenerator->getQuery();
		
		$query.= " AND vtiger_activity.activitytype NOT IN ('Emails','Task') AND ";
		$query.= " ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') OR ( due_date >= '$dbStartDate'))";
		
        $params = array();
		if(empty($userid)){
            $eventUserId  = $currentUser->getId();
        }else{
            $eventUserId = $userid;
        }	
        $params = array_merge(array($eventUserId), $this->getGroupsIdsForUsers($eventUserId));
        $query.= " AND vtiger_crmentity.smownerid IN (".  generateQuestionMarks($params).")";

	$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			
			// SG140904 Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
			$item['cflbls'] = $cfarray;
			foreach ($cfarray as $cfnm=>$cflbl) {				
				$item[$cfnm] = $record[$cfnm];
				}		
			if ($record['vehiculeid']) {
				$vehiculearray = getEntityName('Vehicules',$record['vehiculeid']);
				$vehiculecolor = getSingleFieldValue('vtiger_vehicules','calcolor','vehiculesid',$record['vehiculeid']);
				$item['vehiculename'] = $vehiculearray[$record['vehiculeid']];
				//$color = $vehiculecolor;
			}
			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			//record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel. Ne pas confondre avec $crmid qui est l'id de cet event.
			if ($record['crmid']) {	$pt = getSalesEntityType($record['crmid']);		
						$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);
						
						if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Campaigns','Campaigns');
							$item['parentname'] = getCampaignName($record['crmid']);
							}
						if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Potentials','Potentials');
							$item['parentname'] = getPotentialName($record['crmid']);
							}
						if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								//$item['parenttype'] = vtranslate('SINGLE_Accounts','Accounts');
								$item['parentname'] = getAccountName($record['crmid']);
								}
						}
			//if ($record['activitytype']) $item['activitytype'] = vtranslate($record['activitytype'],'Calendar');
			// END of SG1409
			
			if($visibility == 'Private' && $userid && $userid != $currentUser->getId()) {
				$item['title'] = decode_html($userName).' - '.decode_html(vtranslate('Busy','Events')).'*';
				$item['url']   = '';
			} else {
				$item['title'] = decode_html($record['subject']) 
			//SG1409
			//.' - (' . vtranslate($record['eventstatus'],'Calendar') . ')'
				;
				$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Converting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['vtigertype'] = 'Events';
			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $color;
			$item['textColor'] = $textColor;
			$result[] = $item;
			}
		//SG1409
		$this->groupResultsById($result);
	}

	protected function pullVehiculeEvents($start,$end,&$result,$vehiculedata) {

		foreach ($vehiculedata as $vehicid=>$color) {
			$colorComponents = explode(',',$color);
			$backgroundColor = $colorComponents[0];
			$textColor = $colorComponents[1];
			$vehiculeEvents = array();
			
			$this->pullEventsByVehiculeId($start, $end, $vehiculeEvents,$vehicid,$backgroundColor,$textColor);
								
			$result[$vehicid] = $vehiculeEvents;
		}
	}
	
	protected function pullEventsByVehiculeId($start, $end, &$result,$vehiculeid,$backcolor,$textcolor) {		
		
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Events');
		
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		
		$basicfields = array('subject', 'eventstatus', 'visibility','date_start','time_start','due_date','time_end','assigned_user_id','id','contact_id', 'activitytype','parent_id','vehiculeid');
		$finalfields = array();
		$customfields = array();
		$cfarray = array();
		$this->fillCustomFieldsArrays($customfields,$cfarray,'Events',$hasCustomField);		
		$finalfields = array_merge($customfields,$basicfields);	
				
		$queryGenerator->setFields($finalfields);
		$query = $queryGenerator->getQuery();
		
		$query.= " AND vtiger_activity.activitytype NOT IN ('Emails','Task') AND ";
		$query.= " ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') OR ( due_date >= '$dbStartDate'))";
		
		$query.= " AND vtiger_vehiculeactivityrel.vehiculeid = '$vehiculeid'";
		
        $params = array();
		if(empty($userid)){
            $eventUserId  = $currentUser->getId();
        }else{
            $eventUserId = $userid;
        }	
        $params = array_merge(array($eventUserId), $this->getGroupsIdsForUsers($eventUserId));
        $query.= " AND vtiger_crmentity.smownerid IN (".  generateQuestionMarks($params).")";
		
	
		
		
	$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			
			// SG140904 Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
			$item['cflbls'] = $cfarray;
			foreach ($cfarray as $cfnm=>$cflbl) {				
				$item[$cfnm] = $record[$cfnm];
				}		
			if ($record['vehiculeid']) {
				$vehiculearray = getEntityName('Vehicules',$record['vehiculeid']);
				$vehiculecolor = getSingleFieldValue('vtiger_vehicules','calcolor','vehiculesid',$record['vehiculeid']);
				$item['vehiculename'] = $vehiculearray[$record['vehiculeid']];
			}
			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			//record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel. Ne pas confondre avec $crmid qui est l'id de cet event.
			if ($record['crmid']) {	$pt = getSalesEntityType($record['crmid']);		
						$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);
						
						if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Campaigns','Campaigns');
							$item['parentname'] = getCampaignName($record['crmid']);
							}
						if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Potentials','Potentials');
							$item['parentname'] = getPotentialName($record['crmid']);
							}
						if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								//$item['parenttype'] = vtranslate('SINGLE_Accounts','Accounts');
								$item['parentname'] = getAccountName($record['crmid']);
								}
						}
			//if ($record['activitytype']) $item['activitytype'] = vtranslate($record['activitytype'],'Calendar');
			// END of SG1409
			
			if($visibility == 'Private' && $userid && $userid != $currentUser->getId()) {
				$item['title'] = decode_html($userName).' - '.decode_html(vtranslate('Busy','Events')).'*';
				$item['url']   = '';
			} else {
				$item['title'] = decode_html($record['subject']) 
			//SG1409
			//.' - (' . vtranslate($record['eventstatus'],'Calendar') . ')'
				;
				$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Converting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['vtigertype'] = 'Events';
			$item['editable'] = true;
			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $backcolor;
			$item['textColor'] = $textcolor;
			$result[] = $item;
			}
		//SG1409
		$this->groupResultsById($result);
		
	}
	
	protected function pullMGChauffeurAllActivities($start,$end,&$result,$mgcdata) {
			
		foreach ($mgcdata as $mgcuserid=>$color) {
			$colorComponents = explode(',',$color);
			$backgroundColor = $colorComponents[0];
			$textColor = $colorComponents[1];
			$mgchauffeurEvents = array();
			$mgchauffeurTransports = array();
			
			$this->pullEventsByMGCUserId($start, $end, $mgchauffeurEvents,$mgcuserid,$backgroundColor,$textColor);
					
			$this->pullTranportsByMGCUserId($start, $end, $mgchauffeurTransports,$mgcuserid,$backgroundColor,$textColor);
			
			//var_dump($mgchauffeurTransports);
			//die();
			
								
			$result[$mgcuserid] = array_merge($mgchauffeurEvents, $mgchauffeurTransports);;
		}
	}
	
	
	protected function pullMGChauffeurEvents($start,$end,&$result,$mgcdata) {
			
		foreach ($mgcdata as $mgcuserid=>$color) {
			$colorComponents = explode(',',$color);
			$backgroundColor = $colorComponents[0];
			$textColor = $colorComponents[1];
			$mgchauffeurEvents = array();
			
			
			$this->pullEventsByMGCUserId($start, $end, $mgchauffeurEvents,$mgcuserid,$backgroundColor,$textColor);
								
			$result[$mgcuserid] = $mgchauffeurEvents;
		}
	}
	protected function pullEventsByMGCUserId($start, $end, &$result,$mgcuserid,$backcolor,$textcolor) {		
		
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$query = "SELECT vtiger_activity.subject, vtiger_activity.eventstatus, vtiger_activity.visibility, vtiger_activity.date_start,
			vtiger_activity.time_start, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_crmentity.smownerid, vtiger_activity.activityid,
			vtiger_cntactivityrel.contactid, vtiger_activity.activitytype, vtiger_seactivityrel.crmid, vtiger_vehiculeactivityrel.vehiculeid,
			vtiger_invitees.inviteeid, vtiger_mgchauffeurs.uicolor as calcolor
			FROM vtiger_activity
			INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_invitees ON vtiger_activity.activityid = vtiger_invitees.activityid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			LEFT JOIN vtiger_mgchauffeurs ON vtiger_mgchauffeurs.userid = vtiger_users.id
			LEFT JOIN vtiger_groups ON vtiger_crmentity.smownerid = vtiger_groups.groupid
			LEFT JOIN vtiger_cntactivityrel ON vtiger_activity.activityid = vtiger_cntactivityrel.activityid
			LEFT JOIN vtiger_seactivityrel ON vtiger_activity.activityid = vtiger_seactivityrel.activityid
			LEFT JOIN vtiger_vehiculeactivityrel ON vtiger_activity.activityid = vtiger_vehiculeactivityrel.activityid
			WHERE vtiger_crmentity.deleted=0
			AND vtiger_activity.activityid > 0";
		
		$query.= " AND vtiger_activity.activitytype NOT IN ('Emails','Task') AND ";
		$query.= " ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') OR ( due_date >= '$dbStartDate'))";
		
		$query.= " AND vtiger_invitees.inviteeid = '$mgcuserid'";
		
		$params = array();
		if(empty($userid)){
			$eventUserId  = $currentUser->getId();
		}else{
			$eventUserId = $userid;
		}	
        $params = array_merge(array($eventUserId), $this->getGroupsIdsForUsers($eventUserId));
        $query.= " AND vtiger_crmentity.smownerid IN (".  generateQuestionMarks($params).")";
		
	//SGNOW
	//var_dump($params);
	//echo $query;
	
		
	$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			
			// SG1410 TODO if needed Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
			//$item['cflbls'] = $cfarray;
			//foreach ($cfarray as $cfnm=>$cflbl) {				
			//	$item[$cfnm] = $record[$cfnm];
			//	}		
			if ($record['vehiculeid']) {
				$vehiculearray = getEntityName('Vehicules',$record['vehiculeid']);
				$vehiculecolor = getSingleFieldValue('vtiger_vehicules','calcolor','vehiculesid',$record['vehiculeid']);
				$item['vehiculename'] = $vehiculearray[$record['vehiculeid']];
			}
			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			//record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel. Ne pas confondre avec $crmid qui est l'id de cet event.
			if ($record['crmid']) {	$pt = getSalesEntityType($record['crmid']);		
						$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);
						
						if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Campaigns','Campaigns');
							$item['parentname'] = getCampaignName($record['crmid']);
							}
						if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Potentials','Potentials');
							$item['parentname'] = getPotentialName($record['crmid']);
							}
						if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								//$item['parenttype'] = vtranslate('SINGLE_Accounts','Accounts');
								$item['parentname'] = getAccountName($record['crmid']);
								}
						}
			//if ($record['activitytype']) $item['activitytype'] = vtranslate($record['activitytype'],'Calendar');
			// END of SG1409
			
			if($visibility == 'Private' && $userid && $userid != $currentUser->getId()) {
				$item['title'] = decode_html($userName).' - '.decode_html(vtranslate('Busy','Events')).'*';
				$item['url']   = '';
			} else {
				$item['title'] = decode_html($record['subject']) 
			//SG1409
			//.' - (' . vtranslate($record['eventstatus'],'Calendar') . ')'
				;
				$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Converting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			
			
			$item['vtigertype'] = 'Events';
			$item['editable'] = true;
			
			$item['className'] = $cssClass;
			
			$item['allDay'] = ($record['date_start'] < $record['due_date']) ? true : false;
			
			$item['color'] = $backcolor;
			$item['textColor'] = $textcolor;
			$result[] = $item;
			}
		
		$this->groupResultsById($result);
		
	}
	
	protected function pullTranportsByMGCUserId($start, $end, &$result,$mgcuserid,$backcolor,$textcolor) {		
		
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$query = "SELECT vtiger_mgtransports.subject, vtiger_mgtransports.datetransport, vtiger_crmentity.crmid as mgtid, vtiger_mgtransports.mgtransportsid,
			vtiger_mgtransports.contactid, vtiger_mgtransports.accountid, vtiger_mgtransports.mgtypetransport,
			vtiger_crmentityrel.crmid,vtiger_crmentityrel.module, vtiger_crmentityrel.relcrmid,vtiger_crmentityrel.relmodule
			FROM vtiger_mgtransports
			INNER JOIN vtiger_crmentity ON vtiger_mgtransports.mgtransportsid = vtiger_crmentity.crmid
			INNER JOIN vtiger_mgchauffeurs ON (vtiger_mgchauffeurs.userid = '$mgcuserid')
			INNER JOIN vtiger_crmentityrel ON ( (vtiger_crmentityrel.crmid = vtiger_mgchauffeurs.mgchauffeursid AND vtiger_crmentityrel.relcrmid = vtiger_mgtransports.mgtransportsid)
							OR (vtiger_crmentityrel.relcrmid = vtiger_mgchauffeurs.mgchauffeursid AND vtiger_crmentityrel.crmid = vtiger_mgtransports.mgtransportsid))			
			WHERE vtiger_crmentity.deleted=0
			AND vtiger_mgtransports.mgtransportsid > 0";
		
		$query.= " AND (vtiger_mgtransports.datetransport  >= '$dbStartDateTime' AND vtiger_mgtransports.datetransport < '$dbEndDateTime')";
		
		$query.= " AND vtiger_mgchauffeurs.userid = '$mgcuserid'";
		$query.= " GROUP BY vtiger_mgtransports.mgtransportsid";
		
	//SGNOW
	
	//echo $query;
		
	$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			
			$item = array();
			$item = array();
			$item['id'] = $record['mgtransportsid'];
			$item['title'] = vtranslate('SINGLE_MGTransports','MGTransports'). ' : ' . decode_html($record['subject']) ." - " . decode_html($record['mgtypetransport']) ;
			//$item['description'] = decode_html($record['mgtypetransport']) ;
			$item['start'] = $record['datetransport'];
			$item['url']   = sprintf('index.php?module=MGTransports&view=Detail&record=%s', $record['mgtransportsid']);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			//SGTODONOW
			//$item['editable'] = false;
			$item['vtigertype'] = 'MGTransports';
			$item['editable'] = true;
			$item['allDay'] = true;			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			if ($record['accountid']) {
				if (getAccountName($record['accountid']) && getAccountName($record['accountid'])!='') {
								$item['accountname'] = getAccountName($record['accountid']);
								}
			}
			if ($record['potentialid']) {
				if (getPotentialName($record['potentialid']) && getPotentialName($record['potentialid'])!='') {
							$item['potentialname'] = getPotentialName($record['potentialid']);
							}
			}
			
			$item['color'] = $backcolor;
			$item['textColor'] = $textcolor;
			
			$result[] = $item;
			}
		
		//var_dump($result);
		
		$this->groupResultsById($result);
		
		//var_dump($result);
		
	}
	
	/*
	protected function pullInvitedEvents($start,$end,&$result,$mapping) {

		foreach ($mapping as $inviteeid=>$color) {
			$colorComponents = explode(',',$color);
			$backgroundColor = $colorComponents[0];
			$textColor = $colorComponents[1];
			$invitedEvents = array();
			
			$this->pullEventsByInvitedId($start, $end, $invitedEvents,$inviteeid,$backgroundColor,$textColor);
								
			$result[$inviteeid] = $invitedEvents;
		}
	}
	protected function pullEventsByInvitedId($start, $end, &$result,$invitedid,$backcolor,$textcolor) {		
		
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$query = "SELECT vtiger_activity.subject, vtiger_activity.eventstatus, vtiger_activity.visibility, vtiger_activity.date_start,
			vtiger_activity.time_start, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_crmentity.smownerid, vtiger_activity.activityid,
			vtiger_cntactivityrel.contactid, vtiger_activity.activitytype, vtiger_seactivityrel.crmid, vtiger_vehiculeactivityrel.vehiculeid,
			vtiger_invitees.inviteeid
			FROM vtiger_activity
			INNER JOIN vtiger_crmentity ON vtiger_activity.activityid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_invitees ON vtiger_activity.activityid = vtiger_invitees.activityid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			LEFT JOIN vtiger_groups ON vtiger_crmentity.smownerid = vtiger_groups.groupid
			LEFT JOIN vtiger_cntactivityrel ON vtiger_activity.activityid = vtiger_cntactivityrel.activityid
			LEFT JOIN vtiger_seactivityrel ON vtiger_activity.activityid = vtiger_seactivityrel.activityid
			LEFT JOIN vtiger_vehiculeactivityrel ON vtiger_activity.activityid = vtiger_vehiculeactivityrel.activityid
			WHERE vtiger_crmentity.deleted=0
			AND vtiger_activity.activityid > 0";
		
		$query.= " AND vtiger_activity.activitytype NOT IN ('Emails','Task') AND ";
		$query.= " ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') OR ( due_date >= '$dbStartDate'))";
		
		$query.= " AND vtiger_invitees.inviteeid = '$invitedid'";
		
		$params = array();
		if(empty($userid)){
			$eventUserId  = $currentUser->getId();
		}else{
			$eventUserId = $userid;
		}	
        $params = array_merge(array($eventUserId), $this->getGroupsIdsForUsers($eventUserId));
        $query.= " AND vtiger_crmentity.smownerid IN (".  generateQuestionMarks($params).")";
		
	//SGNOW
	//var_dump($params);
	//echo $query;
	
		
	$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			
			// SG1410 TODO if needed Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
			//$item['cflbls'] = $cfarray;
			//foreach ($cfarray as $cfnm=>$cflbl) {				
			//	$item[$cfnm] = $record[$cfnm];
			//	}		
			if ($record['vehiculeid']) {
				$vehiculearray = getEntityName('Vehicules',$record['vehiculeid']);
				$vehiculecolor = getSingleFieldValue('vtiger_vehicules','calcolor','vehiculesid',$record['vehiculeid']);
				$item['vehiculename'] = $vehiculearray[$record['vehiculeid']];
			}
			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			//record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel. Ne pas confondre avec $crmid qui est l'id de cet event.
			if ($record['crmid']) {	$pt = getSalesEntityType($record['crmid']);		
						$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);
						
						if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Campaigns','Campaigns');
							$item['parentname'] = getCampaignName($record['crmid']);
							}
						if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
							//$item['parenttype'] = vtranslate('SINGLE_Potentials','Potentials');
							$item['parentname'] = getPotentialName($record['crmid']);
							}
						if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								//$item['parenttype'] = vtranslate('SINGLE_Accounts','Accounts');
								$item['parentname'] = getAccountName($record['crmid']);
								}
						}
			//if ($record['activitytype']) $item['activitytype'] = vtranslate($record['activitytype'],'Calendar');
			// END of SG1409
			
			if($visibility == 'Private' && $userid && $userid != $currentUser->getId()) {
				$item['title'] = decode_html($userName).' - '.decode_html(vtranslate('Busy','Events')).'*';
				$item['url']   = '';
			} else {
				$item['title'] = decode_html($record['subject']) 
			//SG1409
			//.' - (' . vtranslate($record['eventstatus'],'Calendar') . ')'
				;
				$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Converting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['vtigertype'] = 'Events';
			$item['editable'] = true;
			
			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $backcolor;
			$item['textColor'] = $textcolor;
			$result[] = $item;
			}
		//SG1409
		$this->groupResultsById($result);
		
	}
	*/
	
	protected function pullMultipleEvents($start, $end, &$result, $data) {

		foreach ($data as $id=>$backgroundColorAndTextColor) {
			$userEvents = array();
			$colorComponents = explode(',',$backgroundColorAndTextColor);
			$this->pullEvents($start, $end, $userEvents ,null,$id, $colorComponents[0], $colorComponents[1]);
			$result[$id] = $userEvents;
		}
	}
	
	
	
	protected function pullTasks($start, $end, &$result, $cssClass) {
		$user = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);

		$hasCustomFields = false;
		
		//SG140908 ajout des 'cf_xxx','contact_id', 'activitytype' et 'parent_id' dans la liste de fields du querygenerator
		//$basicfields est le tableau de champs utilisé dans la version originale auquel on a ajouté 'contact_id', 'activitytype' et 'parent_id'
		//$customfields est la liste des Customfields des evenements considérés
		//$cfarray est un tableau associatif $customfieldname=>$customfieldlabel (par exemple cf_703=>Véhicules")
		//Ces tableaux sont utilisés pour enrichir l'info sur les Events ou les Tasks envoyée à fullcalendar.js
		//SG140921 ajout de vehiculeid champ entré "à la main" dans la base uitype=10, relation définie dans fieldmodulerel, et des liens dans crmentityrel avec deux véhicules
		
		$basicfields = array('subject', 'taskstatus','date_start','time_start','due_date','time_end','assigned_user_id','id','contact_id', 'activitytype','parent_id','vehiculeid');
		$finalfields = array();
		$customfields = array();
		$cfarray = array();
		$this->fillCustomFieldsArrays($customfields,$cfarray,'Tasks',$hasCustomFields);
		$finalfields = array_merge($customfields,$basicfields);	
				
		$queryGenerator->setFields($finalfields);
		
		
		// END
		
		//Old $queryGenerator->setFields(array('subject', 'taskstatus', 'date_start','time_start','due_date','time_end','id'));
		$query = $queryGenerator->getQuery();
		
		$query.= " AND vtiger_activity.activitytype = 'Task' AND ";
		$query.= " ((date_start >= '$start' AND due_date < '$end') OR ( due_date >= '$start'))";
	        $params = $userAndGroupIds;
		$query.= " AND vtiger_crmentity.smownerid IN (".generateQuestionMarks($params).")";
		
		$queryResult = $db->pquery($query,$params);
		
		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['subject']) . ' - (' . vtranslate($record['status'],'Calendar') . ')';
			
			// SG140904 Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
			$item['cflbls'] = $cfarray;
			foreach ($cfarray as $cfnm=>$cflbl) {
				$item[$cfnm] = $record[$cfnm];
				}
			//SG140921 $record['relcrmid'] est ici l'id de l'éventuel crmentity lié au calendar dans la table vtiger_crmentityrel
			if ($record['relcrmid']) {
					$item['relcrmtype'] = getSalesEntityType($record['relcrmid']);
					
					//$relcrmidkey = strtolower($item['relcrmtype']).'id';
					
					
					$relcrmid = $record['relcrmid'];
					//$item[$relcrmidkey] = $relcrmid;
					
					$relcrmnamekey = strtolower($item['relcrmtype']).'name';
					$item['relcrmnamekey'] = $relcrmnamekey;
					
					$relcrmarray = getEntityName($item['relcrmtype'],$relcrmid);
					
					$item[$relcrmnamekey] = $relcrmarray[$relcrmid];
					}
			//ENDSG140901
			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			
		
			
			//SG1409 record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel : Comptes ou Affaire liée. Ne pas confondre avec $crmid qui est l'id de cet event.
			if ($record['crmid']) {	$pt = getSalesEntityType($record['crmid']);		
						$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);
						
						if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
							$item['parentname'] = getCampaignName($record['crmid']);
							}
						if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
							$item['parentname'] = getPotentialName($record['crmid']);
							}
						if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								$item['parentname'] = getAccountName($record['crmid']);
								}
						}
			// END of SG1409
			
			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];
			$item['end']   = $record['due_date'];
			
			
			$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			$result[] = $item;
		}		
		$this->groupResultsById($result);		
	}
	
	//SG1409 Fills two arrays of the custom fields of Events module
	//$customfields is the array list of the custom fields
	//$cfarray is an associative array of the custom fields of events module : customfieldname=>customfieldlabel. This array can be sent to fullcalendar.js to show the custom fields data
	//$eventtype is 'Events' or 'Tasks' to get correct $tabid
	//$hasCustomFields flag for pullTasks or pullEvents
	
	
	protected function fillCustomFieldsArrays(&$customfields,&$cfarray,$eventtype,&$hasCustomFields) {
		//$customfields = array();
		//$cfarray = array();
		switch ($eventtype) {
			case 'Events' :
				$eventfields = getColumnFields('Events');
				$tabid = getTabid('Events');
				break;
			case 'Tasks' :
				$eventfields = getColumnFields('Calendar');
				$tabid = getTabid('Calendar');
				break;
			default :
				
				$eventfields = array();
		}	
		foreach ($eventfields as $fldnm=>$v) {			
			if (strpos ($fldnm,'cf_')!==false && strpos ($fldnm,'cf_') === 0) {
				$hasCustomFields = true;
				array_push($customfields,$fldnm);	
					$cfid = getFieldid($tabid,$fldnm);
					$cfrealtabid = getSingleFieldValue('vtiger_field','tabid','fieldid',$cfid);
					if ($cfrealtabid==$tabid) {
						$cflbl = getSingleFieldValue('vtiger_field','fieldlabel','fieldid',$cfid);
						$cfarray[$fldnm]=$cflbl;
					}
				}		
			}
	}
	//SG1409
	// Because a same activity can have multiple contacts or other field values, the SQL response gives multiple rows for the same id
	// This function acts as a basic "concat_group () ... GROUP BY id", concatenating the fields with multiple values.
	// The function works good here because the rows are quite similar and differ only for few fields. It doesn't pretend to be an universal "group by".
	//// This couldn't be done by SQL without modifying the QueryGenerator, which might have caused other issues...
	//@param &$res Result rows given by SQL
	//@return Array : Grouped and concatenated rows.
	
	protected function groupResultsById(&$res) {
	    $keychanged = array();
	    $resulttodel = array();
	    $changemap = array();
	    foreach ($res as $i=>$activity) {   
		$activityid = $activity['id'];
		if ($i < count($res)-1) {
		    for ($k=$i+1; $k<count($res);$k++) {          
			if ($activityid == $res[$k]['id']) {
			    if (is_array($resulttodel)&& in_array($k,$resulttodel)) {}
				else {
				    $diff = array_diff_assoc($activity,$res[$k]);
				    foreach ($diff as $key=>$value) {                         
				      $resulttodel[] = $k;                                               
				      $changemap[$i][$key][] = $res[$k][$key];          
				    }
				}
			    }       
		    }   
		}
	    };
	    foreach ($changemap as $rsltindex=>$keystochange) {
		foreach ($keystochange as $key=>$valuestoadd) {
		    $changemap[$rsltindex][$key] = array_unique($changemap[$rsltindex][$key]);
		}
	    }
	    foreach ($changemap as $rsltindex=>$keystochange) {    
	       foreach ($keystochange as $key=>$valuestoadd) {     
		    foreach ($valuestoadd as $v) {
			$res[$rsltindex][$key] .= ', '.$v;
                        }
		}  
	    }
	    for ($j=count($res)-1;$j>0;$j--) {
		if (is_array($resulttodel) && in_array($j,$resulttodel)) {       
		    unset($res[$j]);      
		}
	    };
	    foreach ($res as $value){
		$finalresult[] = $value;
		}
	    $res = $finalresult;
	   // return $res;
	}

	protected function pullPotentials($start, $end, &$result, $cssClass) {
		$query = "SELECT potentialname,closingdate FROM Potentials";
		$query.= " WHERE closingdate >= '$start' AND closingdate <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['potentialname']);
			$item['start'] = $record['closingdate'];
			$item['url']   = sprintf('index.php?module=Potentials&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			$item['editable'] = false;
		
			
			$result[] = $item;
		}
	}
	protected function pullMGTransports($start, $end, &$result, $cssClass) {
				
		$db = PearDatabase::getInstance();
		
		$user = Users_Record_Model::getCurrentUserModel();
		
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$params = $userAndGroupIds;
		
		$query = "SELECT mgtransportsid, subject, datetransport, mgtypetransport, contactid, accountid, potentialid FROM vtiger_mgtransports";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_mgtransports.mgtransportsid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .")";
		
		$query.= " AND datetransport >= '$start' AND datetransport <= '$end'";
		

		
		
		$queryResult = $db->pquery($query, $params);
		
		
		
		//$records = $this->queryForRecords($query,false);
		//SGNOW
		
		
		while($record = $db->fetchByAssoc($queryResult)){
			
			//var_dump($record);
			
			$item = array();
			$item['id'] = $record['mgtransportsid'];
			$item['title'] = decode_html($record['subject']) ." - " . decode_html($record['mgtypetransport']) ;
			//$item['description'] = decode_html($record['mgtypetransport']) ;
			$item['start'] = $record['datetransport'];
			$item['url']   = sprintf('index.php?module=MGTransports&view=Detail&record=%s', $record['mgtransportsid']);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			//SGTODONOW
			//$item['editable'] = false;
			$item['vtigertype'] = 'MGTransports';
			$item['editable'] = true;
			$item['allDay'] = true;
			
			if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			if ($record['accountid']) {
				if (getAccountName($record['accountid']) && getAccountName($record['accountid'])!='') {
								$item['accountname'] = getAccountName($record['accountid']);
								}
			}
			if ($record['potentialid']) {
				if (getPotentialName($record['potentialid']) && getPotentialName($record['potentialid'])!='') {
							$item['potentialname'] = getPotentialName($record['potentialid']);
							}
			}
			
			
			$result[] = $item;
		}
	}

	protected function pullContacts($start, $end, &$result, $cssClass) {
		$this->pullContactsBySupportEndDate($start, $end, $result, $cssClass);
		$this->pullContactsByBirthday($start, $end, $result, $cssClass);
	}

	protected function pullContactsBySupportEndDate($start, $end, &$result, $cssClass) {
		$query = "SELECT firstname,lastname,support_end_date FROM Contacts";
		$query.= " WHERE support_end_date >= '$start' AND support_end_date <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html(trim($record['firstname'] . ' ' . $record['lastname']));
			$item['start'] = $record['support_end_date'];
			$item['url']   = sprintf('index.php?module=Contacts&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			$item['editable'] = false;
			$result[] = $item;
		}
	}

	protected  function pullContactsByBirthday($start, $end, &$result, $cssClass) {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$startDateComponents = split('-', $start);
		$endDateComponents = split('-', $end);
        
        $userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
        $params = $userAndGroupIds;
        
		$year = $startDateComponents[0];

		$query = "SELECT firstname,lastname,birthday,crmid FROM vtiger_contactdetails";
		$query.= " INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (".  generateQuestionMarks($params) .") AND";
		$query.= " ((CONCAT('$year-', date_format(birthday,'%m-%d')) >= '$start'
						AND CONCAT('$year-', date_format(birthday,'%m-%d')) <= '$end')";

        
		$endDateYear = $endDateComponents[0];
		if ($year !== $endDateYear) {
			$query .= " OR
						(CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) >= '$start'
							AND CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) <= '$end')";
		}
		$query .= ")";

		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$recordDateTime = new DateTime($record['birthday']);

			$calendarYear = $year;
			if($recordDateTime->format('m') < $startDateComponents[1]) {
				$calendarYear = $endDateYear;
			}
			$recordDateTime->setDate($calendarYear, $recordDateTime->format('m'), $recordDateTime->format('d'));
			$item['id'] = $crmid;
			$item['title'] = decode_html(trim($record['firstname'] . ' ' . $record['lastname']));
			$item['start'] = $recordDateTime->format('Y-m-d');
			$item['url']   = sprintf('index.php?module=Contacts&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			$item['editable'] = false;
			$result[] = $item;
		}
	}

	protected function pullInvoice($start, $end, &$result, $cssClass) {
		$query = "SELECT subject,duedate FROM Invoice";
		$query.= " WHERE duedate >= '$start' AND duedate <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['subject']);
			$item['start'] = $record['duedate'];
			$item['url']   = sprintf('index.php?module=Invoice&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			$item['editable'] = false;
			
			$result[] = $item;
		}
	}

	/**
	 * Function to pull all the current user projects
	 * @param type $startdate
	 * @param type $actualenddate
	 * @param type $result
	 * @param type $cssClass
	 */
	protected function pullProjects($start, $end, &$result, $cssClass) {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
        $params = $userAndGroupIds;
        
		$query = "SELECT projectname, startdate, targetenddate, crmid FROM vtiger_project";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_project.projectid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .") AND ";
		$query.= " ((startdate >= '$start' AND targetenddate < '$end') OR ( targetenddate >= '$start'))";
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['projectname']);
			$item['start'] = $record['startdate'];
			$item['end'] = $record['targetenddate'];
			$item['url']   = sprintf('index.php?module=Project&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			$item['editable'] = false;
			
			$result[] = $item;
		}
	}

	/**
	 * Function to pull all the current user porjecttasks
	 * @param type $startdate
	 * @param type $enddate
	 * @param type $result
	 * @param type $cssClass
	 */
	protected function pullProjectTasks($start, $end, &$result, $cssClass) {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
        $userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
        $params = $userAndGroupIds;
		
		$query = "SELECT projecttaskname, startdate, enddate, crmid FROM vtiger_projecttask";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .") AND ";
		$query.= " ((startdate >= '$start' AND enddate < '$end') OR ( enddate >= '$start'))";
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['projecttaskname']);
			$item['start'] = $record['startdate'];
			$item['end'] = $record['enddate'];
			$item['url']   = sprintf('index.php?module=ProjectTask&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			//SG1410 as long as dropEvent and resizeEvent in CalendarView.js only manages Event or Task drag & drop.
			$item['editable'] = false;
			$result[] = $item;
		}
	}

}
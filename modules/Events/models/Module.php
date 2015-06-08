<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Calendar Module Model Class
 */
class Events_Module_Model extends Calendar_Module_Model {

    /**
	 * Function to get the url for list view of the module
	 * @return <string> - url
	 */
	public function getListViewUrl() {
		return 'index.php?module=Calendar&view='.$this->getListViewName();
	}

   /**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {		
		$recordModel = parent::saveRecord($recordModel);
		/* SG No use for now in MG Calendar
		//code added to send mail to the vtiger_invitees
		$selectUsers = $recordModel->get('selectedusers');
			if(!empty($selectUsers))
		{
		    $invities = implode(';',$selectUsers);
		    $mail_contents = $recordModel->getInviteUserMailData();
		    $activityMode = ($recordModel->getModuleName()=='Calendar') ? 'Task' : 'Events';
		    sendInvitation($invities,$activityMode,$recordModel->get('subject'),$mail_contents);
		}
        */
    }

	/**
	 * Function to retrieve name fields of a module
	 * @return <array> - array which contains fields which together construct name fields
	 */
	public function getNameFields(){
        $nameFieldObject = Vtiger_Cache::get('EntityField',$this->getName());
        $moduleName = $this->getName();
		if($nameFieldObject && $nameFieldObject->fieldname) {
			$this->nameFields = explode(',', $nameFieldObject->fieldname);
		} else {
			$adb = PearDatabase::getInstance();

			$query = "SELECT fieldname, tablename, entityidfield FROM vtiger_entityname WHERE tabid = ?";
			$result = $adb->pquery($query, array(getTabid('Calendar')));
			$this->nameFields = array();
			if($result){
				$rowCount = $adb->num_rows($result);
				if($rowCount > 0){
					$fieldNames = $adb->query_result($result,0,'fieldname');
					$this->nameFields = explode(',', $fieldNames);
				}
			}
			
			$entiyObj = new stdClass();
			$entiyObj->basetable = $adb->query_result($result, 0, 'tablename');
			$entiyObj->basetableid =  $adb->query_result($result, 0, 'entityidfield');
			$entiyObj->fieldname =  $fieldNames;
			Vtiger_Cache::set('EntityField',$this->getName(), $entiyObj);
		}
        return $this->nameFields;
	}
	/*
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	*/
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		
		$relatedModuleName = $relatedModule->getName();
		// this gets only activity, no tasks
		if ($functionName === 'get_vehicules' && ($relatedModuleName == 'Vehicules')) {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_vendorvehiculerel.vendorid AS vendorid,
						vtiger_crmentity.*, vtiger_vehicules.*, vtiger_vehiculescf.*
						FROM vtiger_vehicules
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vehicules.vehiculesid
						INNER JOIN vtiger_vehiculescf ON vtiger_vehiculescf.vehiculesid = vtiger_vehicules.vehiculesid
						INNER JOIN vtiger_vehiculeactivityrel ON (vtiger_vehiculeactivityrel.vehiculeid = vtiger_crmentity.crmid AND vtiger_vehiculeactivityrel.activityid = ".$recordId.")
													
						LEFT JOIN vtiger_vendorvehiculerel ON (vtiger_vendorvehiculerel.vehiculeid = vtiger_vehicules.vehiculesid)

						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						
							WHERE vtiger_vehiculeactivityrel.activityid = ".$recordId."
							AND vtiger_crmentity.deleted = 0
						";

			$relatedModuleName = $relatedModule->getName();
			
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		}
		
	
	else if ($functionName === 'get_contacts' && ($relatedModuleName == 'Contacts')) {
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT vtiger_crmentity.crmid, vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_contactdetails.phone, vtiger_contactdetails.accountid, vtiger_contactdetails.title, vtiger_contactdetails.email,
				vtiger_crmentity.smownerid, vtiger_contactaddress.mailingcity, vtiger_contactaddress.mailingcountry
				FROM vtiger_contactdetails
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_cntactivityrel ON (vtiger_cntactivityrel.contactid = vtiger_crmentity.crmid AND vtiger_cntactivityrel.activityid = ".$recordId.")
				
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid
				LEFT JOIN vtiger_contactaddress ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_customerdetails ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_users cur_user ON cur_user.id = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0
				AND (vtiger_cntactivityrel.activityid = ".$recordId." AND vtiger_cntactivityrel.contactid = vtiger_crmentity.crmid)
				";	

			$relatedModuleName = $relatedModule->getName();
			
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			//echo $query;
			//die();
		}
		else if ($functionName === 'get_invitees' && ($relatedModuleName == 'Users')) {
			

			$query = "SELECT vtiger_users.user_name,vtiger_users.id, vtiger_users.first_name, vtiger_users.last_name, vtiger_users.phone_home,
				vtiger_users.phone_mobile, vtiger_users.phone_work,vtiger_users.email1
				FROM vtiger_users				
				LEFT JOIN vtiger_invitees ON (vtiger_users.id = vtiger_invitees.inviteeid AND vtiger_invitees.activityid = ".$recordId.")
				WHERE vtiger_users.status = 'Active'
				AND (vtiger_invitees.activityid = ".$recordId." AND vtiger_invitees.inviteeid = vtiger_users.id)
				";	

			$relatedModuleName = $relatedModule->getName();
			
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			//echo $query;
			//die();
		}
	
		else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}
		
		return $query;
	}

	
}

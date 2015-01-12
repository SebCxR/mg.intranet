<?php
/*+***********************************************************************************
 * ED141025
 * ************************************************************************************/

class MGTransports_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_DASHBOARD',
				'linkurl' => $this->getDashBoardUrl(),
				'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $parentQuickLinks;
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
		if ($functionName === 'get_related_list' && ($relatedModuleName == 'Vehicules')) {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_vendorvehiculerel.vendorid AS vehicule_owner,
						vtiger_crmentity.*, vtiger_vehicules.*, vtiger_vehiculescf.*
						FROM vtiger_vehicules
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_vehicules.vehiculesid
						INNER JOIN vtiger_vehiculescf ON vtiger_vehiculescf.vehiculesid = vtiger_vehicules.vehiculesid
						INNER JOIN vtiger_crmentityrel ON ( vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid
							OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid )
							
						LEFT JOIN vtiger_vendorvehiculerel ON (vtiger_vendorvehiculerel.vehiculeid = vtiger_vehicules.vehiculesid)

						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						
							WHERE (vtiger_crmentityrel.crmid = ".$recordId." OR vtiger_crmentityrel.relcrmid = ".$recordId.")
							AND vtiger_crmentity.deleted = 0
						";

			$relatedModuleName = $relatedModule->getName();
			
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		}
		else if ($functionName === 'get_related_list' && ($relatedModuleName == 'MGChauffeurs')) {
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name, vtiger_crmentity.*, vtiger_mgchauffeurs.*
				FROM vtiger_mgchauffeurs
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_mgchauffeurs.mgchauffeursid
				INNER JOIN vtiger_users ON vtiger_mgchauffeurs.userid = vtiger_users.id
				INNER JOIN vtiger_crmentityrel ON ( vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid
							OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)";
				//SG1412 A voir comment on gere assigned_user inutile pour l'instant : LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid			
			$query .= " LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE (vtiger_crmentityrel.crmid = ".$recordId." OR vtiger_crmentityrel.relcrmid = ".$recordId.")
				AND vtiger_crmentity.deleted = 0
				AND vtiger_users.status = 'Active'
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
	
	else if ($functionName === 'get_related_list' && ($relatedModuleName == 'Contacts')) {
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT vtiger_crmentity.crmid, vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_contactdetails.phone, vtiger_contactdetails.accountid, vtiger_contactdetails.title, vtiger_contactdetails.email,
				vtiger_crmentity.smownerid, vtiger_contactaddress.mailingcity, vtiger_contactaddress.mailingcountry
				FROM vtiger_contactdetails
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_crmentityrel ON ((vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid AND vtiger_crmentityrel.crmid = ".$recordId.")
					OR (vtiger_crmentityrel.crmid = vtiger_crmentity.crmid AND vtiger_crmentityrel.relcrmid = ".$recordId."))
				LEFT JOIN vtiger_mgtransports ON vtiger_mgtransports.mgtransportsid = ".$recordId." 
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid
				LEFT JOIN vtiger_contactaddress ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_customerdetails ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_users cur_user ON cur_user.id = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0
				AND ((vtiger_crmentityrel.crmid = ".$recordId." AND vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid)
				OR (vtiger_crmentityrel.relcrmid = ".$recordId." AND vtiger_crmentityrel.crmid = vtiger_crmentity.crmid)
				OR vtiger_crmentity.crmid = vtiger_mgtransports.contactid)
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

//SELECT vtiger_crmentity.crmid,vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_contactdetails.phone, vtiger_contactdetails.accountid, vtiger_contactdetails.title, vtiger_contactdetails.email, vtiger_crmentity.smownerid, vtiger_contactaddress.mailingcity, vtiger_contactaddress.mailingcountry FROM vtiger_contactdetails INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid INNER JOIN vtiger_crmentityrel ON (vtiger_crmentityrel.relcrmid = vtiger_crmentity.crmid OR vtiger_crmentityrel.crmid = vtiger_crmentity.crmid) LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid LEFT JOIN vtiger_contactaddress ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid LEFT JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.contactsubscriptionid = vtiger_contactdetails.contactid LEFT JOIN vtiger_customerdetails ON vtiger_customerdetails.customerid = vtiger_contactdetails.contactid LEFT JOIN vtiger_contactscf ON vtiger_contactscf.contactid = vtiger_contactdetails.contactid LEFT JOIN vtiger_users cur_user ON cur_user.id = vtiger_crmentity.smownerid LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid WHERE vtiger_crmentity.deleted = 0 AND (vtiger_crmentityrel.crmid = 34134 OR vtiger_crmentityrel.relcrmid = 34134)


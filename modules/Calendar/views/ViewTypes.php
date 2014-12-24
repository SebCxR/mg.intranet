<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Calendar_ViewTypes_View extends Vtiger_IndexAjax_View {

    function __construct() {
        parent::__construct();
        $this->exposeMethod('getViewTypes');
	$this->exposeMethod('getSharedUsersList');
	//SG1410
	$this->exposeMethod('getVehiculesListForCalendar');
	$this->exposeMethod('getInvitedListForCalendar');
	
	$this->exposeMethod('getMGChauffeursListForCalendar');
	
	
    }
        
	function getViewTypes(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->assign('MODULE', $moduleName);
		$viewer->view('CalendarViewTypes.tpl', $moduleName);
	}
	
	/**
	 * Function to get Shared Users
	 * @param Vtiger_Request $request
	 */
	function getSharedUsersList(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		

		$moduleName = $request->getModule();
		$sharedUsers = Calendar_Module_Model::getSharedUsersOfCurrentUser($currentUser->id);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('SHAREDUSERS', $sharedUsers);
		$viewer->assign('CURRENTUSER_MODEL',$currentUser);
		$viewer->view('CalendarSharedUsers.tpl', $moduleName);
	}
	/**
	 * Function to get Invited Users
	 * @param Vtiger_Request $request
	 */
	function getInvitedListForCalendar(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		

		$moduleName = $request->getModule();
		$invitedUsers = Calendar_Module_Model::getInvitedUsersForCalendar($currentUser->id);
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('INVITEDUSERS', $invitedUsers);
		$viewer->assign('CURRENTUSER_MODEL',$currentUser);
		$viewer->view('CalendarInvitedUsers.tpl', $moduleName);
	}
	/**
	 * Function to get MGChauffeurs list
	 * @param Vtiger_Request $request
	 */
	function getMGChauffeursListForCalendar(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		

		$moduleName = $request->getModule();
		$mgchauffeurs = Calendar_Module_Model::getMGChauffeursForCalendar($currentUser->id);
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MGCHAUFFEURS', $mgchauffeurs);
		$viewer->assign('CURRENTUSER_MODEL',$currentUser);
		$viewer->view('CalendarMGChauffeurs.tpl', $moduleName);
	}
	/**
	 * Function to get Vehicules involved
	 * @param Vtiger_Request $request
	 */
	function getVehiculesListForCalendar(Vtiger_Request $request){
		$viewer = $this->getViewer($request);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		
		$criteria = '';
		$moduleName = $request->getModule();
		
		$vehiculesList = Calendar_Module_Model::getVehiculesForCalendar($criteria);
				
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('VEHICULES', $vehiculesList);
		
		$viewer->assign('CURRENTUSER_MODEL',$currentUser);
		$viewer->view('CalendarVehicules.tpl', $moduleName);
	}
}

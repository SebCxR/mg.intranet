<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class MGTransports_PrintData_View extends Vtiger_View_Controller {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	function preProcess(Vtiger_Request $request) {
		return false;
	}

	function postProcess(Vtiger_Request $request) {
		return false;
	}
	
	/**
	 * Function is called by the controller
	 * @param Vtiger_Request $request
	 */
	function process(Vtiger_Request $request) {
		
		$this->PrintData($request);
	}

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	function PrintData(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$moduleName = $request->get('source_module');
		$thisViewName = $request->get('viewname');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		
		$pagingModel = new Vtiger_Paging_Model();
		$listViewInstance = Vtiger_ListView_Model::getInstance($moduleName,$thisViewName);
		
		if (!empty($orderBy)) {
			$listViewInstance->set('orderby', $orderBy);
			$listViewInstance->set('sortorder',$sortOrder);
		}
		
		
		$headers = $listViewInstance->getListViewHeaders();
		$allEntries = $listViewInstance->getListViewEntries($pagingModel);
		//SG15
		//var_dump($headers);
		//var_dump($allEntries);
		$selectedEntries = $this->getFilteredEntries($request,$allEntries);
		
		//var_dump($selectedEntries);
		//die();
		
		$this->GetPrintList($request, $headers, $selectedEntries);
	}

	/**
	 * Function that generates list of entries to be printed based on the mode
	 * @param Vtiger_Request $request
	 * @param Array $entries
	 * @return <String> export query
	 */
	function getFilteredEntries($request,$rawEntries) {
				
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();
		$cvId = $request->get('viewname');
		$moduleName = $request->get('source_module');

		$filteredEntries = array();
		
		//var_dump($rawEntries);
		
		switch($mode) {
			case 'ExportAllData' :	return $rawEntries;			
						break;

			case 'ExportCurrentPage' :	$pagingModel = new Vtiger_Paging_Model();
							$limit = $pagingModel->getPageLimit();
							
							$currentPage = $request->get('page');
							if(empty($currentPage)) $currentPage = 1;
							
							$currentPageStart = ($currentPage - 1) * $limit;
							if ($currentPageStart < 0) $currentPageStart = 0;
							
							$currentPageEnd = $currentPageStart + $limit;
							$index = 0;
							foreach ($rawEntries as $recordId=>$recordData) {
								if (($index >= $currentPageStart) && ($index <= $currentPageEnd)) {									
									$filteredEntries[$recordId] = $recordData;	
								}
								elseif ($index > $currentPageEnd) {
									continue;	
								}
								$index++;
							}						
							return $filteredEntries;
						break;

			case 'ExportSelectedRecords' :	$idList = $request->get('selected_ids');				
							if(!empty($idList)) {
								foreach ($idList as $selectedkey) {
									$filteredEntries[$selectedkey] = $rawEntries[$selectedkey];	
								}
							}
							
							return $filteredEntries;
						break;

			default :return $rawEntries;
				break;
		}
	}
	
	 //Function displays the report in printable format	 
	function GetPrintList(Vtiger_Request $request, $headers, $entries) {
		$printData = $this->getPrintListHTMLDataArray($request, $headers, $entries);		
		$viewer = $this->getViewer($request);

		$listName = $this->getPrintListName($request);
		$moduleName = $request->getModule();			

		$viewer->assign('REPORT_NAME', $listName);
		$viewer->assign('PRINT_DATA', $printData[0]);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('ROW', $printData[1]);

		$viewer->view('PrintData.tpl', $moduleName);
	}

	//Function to get html template of printable list
	function getPrintListHTMLDataArray($request, $headers, $entries) {
		$modname = $request->getModule();
		$headertpl = "";
		$valtpl = "";
		foreach ($headers as $header) {
			$headertpl .= '<td class="rptCellLabel">' . vtranslate($header->get('label'),$modname) . '</td>';			
		}
		
		foreach ($entries as $recordModel) {
			$valtpl .= "<tr>";
			foreach ($headers as $header) {
				$headername = $header->get('name');
				$valtpl .= "<td>" . $recordModel->get($headername). "</td>";
			}
			
			$valtpl .= "</tr><tr></tr>";
		}
		
		$sHTML = '<tr>'.$headertpl.'</tr>'.$valtpl;
		$return_data[] = $sHTML;
		$return_data[] = count($entries);
		return $return_data;
	}
	
	//Function get the name of listview filter
	function getPrintListName($request) {
		$cvId = $request->get('viewname');
		$modname = $request->getModule();
		$mode = $request->get('mode');
		switch($mode) { case 'ExportAllData' : $cvlabel = getSingleFieldValue('vtiger_customview','viewname','cvid',$cvId);
							$translatedlbl = vtranslate($cvlabel,$modname);
							break;
				case 'ExportCurrentPage' : $cvlabel = getSingleFieldValue('vtiger_customview','viewname','cvid',$cvId);
							$translatedlbl = vtranslate("LBL_PAGE_FROM",$modname) . ' '. vtranslate($cvlabel,$modname);
							break;
				case 'ExportSelectedRecords' : $cvlabel = getSingleFieldValue('vtiger_customview','viewname','cvid',$cvId);
							$translatedlbl = vtranslate("LBL_SELECTED_FROM",$modname) . ' '. vtranslate($cvlabel,$modname);
							break;
		}
		
		
		return $translatedlbl;
	}
	
}
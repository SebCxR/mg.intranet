<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

//Overrides GetRelatedList : used to get related query
//TODO : Eliminate below hacking solution

include_once 'includes/main/WebUI.php';

include_once 'includes/runtime/BaseModel.php';
include_once ('modules/Vtiger/models/Module.php');
include_once ('modules/Vtiger/models/RecordStructure.php');

include_once 'modules/Vtiger/models/Record.php';

/*

include_once 'include/Webservices/Relation.php';




include_once 'modules/Vtiger/models/DetailView.php';
include_once 'modules/Calendar/views/Detail.php';


include_once 'modules/Calendar/models/Record.php';
include_once 'modules/Events/models/Record.php';

include_once 'modules/CustomView/CustomView.php';

*/

/* ED140822
 * Supprime un module
 */
 
//$Vtiger_Utils_Log = true;
/*
$module = Vtiger_Module::getInstance('ModuleED1');
if ($module) $module->delete();
*/
/* Puis, supprimer les tables et les fichiers
modules/<ModuleName>
languages/en_us/<ModuleName>.php
languages/fr_fr/<ModuleName>.php
layouts/vlayout/modules/<ModuleName>
cron/<ModuleName>
*/
/*essais sur related entities
$activities=Vtiger_Module::getInstance('Calendar');
$activities->setRelatedList(Vtiger_Module::getInstance('Vehicule'), 'Activities',Array('ADD','SELECT'),'get_activities');
echo var_dump ($activities);
echo var_dump (Vtiger_Module::getInstance('Vehicules'));
*/
/*
$activities = Vtiger_Module::getInstance('Calendar');
var_dump($activities);
*/

$arrayres = array (array("id"=>"34006","visibility"=>"Public","cflbls"=>'2',"vehiculelist"=>array("34002"=>"gr UE 14"),"contactname"=>"GALOPIN FRAN"),
                   array("id"=>"34006","visibility"=>"Public","cflbls"=>'3',"vehiculelist"=>array("34007"=>"TH 45 12"),"contactname"=>"UN Autre")
                  );

var_dump($arrayres);
groupResultsById(&$arrayres);
var_dump($arrayres);
function groupResultsById(&$res) {
	    $keychanged = array();
	    $resulttodel = array();
	    $changemap = array();
	    foreach ($res as $i=>$activity) {   
		$activityid = $activity['id'];
		if ($i < count($res)-1) {
		    for ($k=$i+1; $k<count($res);$k++) {          
			if ($activityid == $res[$k]['id']) {
			    if (is_array($resulttodel)&& in_array($k,$resulttodel)) {
                                  }
			    else {
				    $diff = array_diff_assoc($activity,$res[$k]);
                                    var_dump($diff);
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
	   
	}




/*
$moduleName = 'Events';
$recordId = '34006';
var_dump($recordId);

$focus = CRMEntity::getInstance($moduleName);
//var_dump($focus);

$focustabindex = $focus->tab_name_index['vtiger_vehiculeactivityrel'];
var_dump($focustabindex);
*/





//var_dump($focus->column_fields);

//		$focus->id = $recordId;
//		$focus->retrieve_entity_info($recordId, $moduleName);







 //  $moduleModel = Vtiger_Module_Model::getInstance($moduleName);
//   var_dump($moduleModel);  
   

//  $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
//  $module = $recordModel->getModule();
// var_dump($module);
  
  
		  
//var_dump($recordModel);

$module = $recordModel->getModule();
//var_dump($module);
//dans le tpl : $FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID);
//vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName();
// $FIELD_MODEL->getReferenceList()};
/*
//saveRecord( $recordModel ) dans vTiger/models/Module.php 
            $moduleName = $module->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$fields = $focus->column_fields;
                var_dump($fields);
                
		foreach($fields as $fieldName => $fieldValue) {
                    
                        
                    
			$fieldValue = $recordModel->get($fieldName);
                       // var_dump($fieldName);
                       // var_dump($fieldValue);
			if(is_array($fieldValue)){
                      $focus->column_fields[$fieldName] = $fieldValue;
            }else if($fieldValue !== null) {
				$focus->column_fields[$fieldName] = decode_html($fieldValue);
			}
		}

		$focus->mode = $recordModel->get('mode');
                
                var_dump($focus->mode);
		$focus->id = $recordModel->getId();
		$focus->save($moduleName);        //  ====>    Sorry !Attempt to access restricted file.....
		 $recordModel->setId($focus->id);
                
*/
?>

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
include_once 'include/Webservices/Relation.php';
include_once 'vtlib/Vtiger/Module.php';


include_once 'includes/main/WebUI.php';


$webUI = new Vtiger_WebUI();



$webUI->process(new Vtiger_Request($_REQUEST, $_REQUEST));

/* ED140822
 * Supprime un module
 */
 /*
$Vtiger_Utils_Log = true;

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
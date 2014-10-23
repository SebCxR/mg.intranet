<?php

class CityLookup {

	public function vtlib_handler($moduleName, $eventType) {
		if ($eventType == 'module.postinstall') {
			$this->_registerLinks($moduleName);
		} else if ($eventType == 'module.enabled') {
			$this->_registerLinks($moduleName);
		} else if ($eventType == 'module.disabled') {
			$this->_deregisterLinks($moduleName);
		}
	}

	protected function _registerLinks($moduleName) {
		$thisModuleInstance = Vtiger_Module::getInstance($moduleName);
		if ($thisModuleInstance) {
			$thisModuleInstance->addLink("HEADERSCRIPT", "City Autofill", "modules/CityLookup/js/CityLookupAutofill.js");
		}
	}

	protected function _deregisterLinks($moduleName) {
		$thisModuleInstance = Vtiger_Module::getInstance($moduleName);
		if ($thisModuleInstance) {
			$thisModuleInstance->deleteLink("HEADERSCRIPT", "City Autofill", "modules/CityLookup/js/CityLookupAutofill.js");
		}
	}
}

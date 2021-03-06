{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<div name='calendarViewTypes'>
	<div id="calendarview-feeds" style="margin-left:10px;">			
		{foreach key=ID item=VEHICULE from=$VEHICULES}
			<label class="checkbox">
				<input type="checkbox" data-calendar-sourcekey="Events55_{$ID}" data-calendar-feed="Vehicules" data-calendar-vehiculeid="{$ID}" data-calendar-vehiculecolor="{$VEHICULE['color']}">
				<span class="label" style="text-shadow:none">{$VEHICULE['name']}</span>
			</label>
		{/foreach}
	</div>
</div>
{/strip}
<script type="text/javascript">
jQuery(document).ready(function() {
	VehiculesCalendar_VehiculesCalendarView_Js.initiateCalendarFeeds();
});
</script>
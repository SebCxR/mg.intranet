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
		{foreach key=ID item=MGCHAUFFEUR from=$MGCHAUFFEURS}
			<label class="checkbox">
				<input type="checkbox" data-calendar-sourcekey="EventsMGC_{$ID}" data-calendar-feed="MGChauffeurs" data-calendar-userid="{$ID}" data-calendar-usercolor="{$MGCHAUFFEUR['color']}">
				<span class="label" style="text-shadow:none">{if $CURRENTUSER_MODEL->getId() eq ID}{vtranslate('LBL_MINE',$MODULE)}{else}{$MGCHAUFFEUR['name']}{/if}</span>
			</label>	
		{/foreach}
	</div>
</div>
{/strip}
<script type="text/javascript">
jQuery(document).ready(function() {
	MGChauffeursCalendar_MGChauffeursCalendarView_Js.initiateCalendarFeeds();
});
</script>
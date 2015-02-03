{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ -->*}

<!DOCTYPE>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" media="print" href="print.css" type="text/css">
		<title>{'LBL_PRINT_REPORT'|@vtranslate:$MODULE}</title>
	</head>
	<body marginheight="0" marginwidth="0" leftmargin="0" topmargin="0" onLoad="JavaScript:window.print();window.location.href = 'index.php?module={$MODULE}&view=List'">
	<table width="80%" border="0" cellpadding="5" cellspacing="0" align="center">
	<tr>
		<td align="left" valign="top" style="border:0px solid #000000;">
			<b>{$REPORT_NAME}</b>				
		</td>
		<td align="right" style="border:0px solid #000000;" valign="top">
			<span style="color:#CCCCCC">{$ROW} {'LBL_RECORDS'|@vtranslate:$MODULE}</span>
		</td>
	</tr>
	<tr>
		<td style="border:0px solid #000000;" colspan="2">
		{if $PRINTLIST_MODE eq 'MGOldSchool'}
		{assign var=COLSPAN value=count($PRINTLIST_HEADERS)-3}
		
		<table width="100%" border="0" cellpadding="5" cellspacing="0" align="center" valign="bottom" class="printReport" >
			{assign var=PREVIOUS_DATE value='init'}		
		{foreach item=PRINTLIST_ENTRY from=$PRINTLIST_ENTRIES}
				{assign var=ENTRY_DATE value=$PRINTLIST_ENTRY->get('datetransport')}
				{assign var=TIMESTAMP value=$ENTRY_DATE|@strtotime}
				{assign var=STRING_FULLDAY value=$TIMESTAMP|date_format:"%A"}
				{assign var=STRING_DAY value=$TIMESTAMP|date_format:"%e"}
				{assign var=STRING_FULLMONTH value=$TIMESTAMP|date_format:"%B"}
				{assign var=STRING_FULLYEAR value=$TIMESTAMP|date_format:"%Y"}
			{if ($PREVIOUS_DATE eq 'init' || $ENTRY_DATE neq $PREVIOUS_DATE)}
				{if $PREVIOUS_DATE neq 'init'}
			<tr>
			<td align="left" valign="top" style="border:0px solid #000000;" colspan="{$COLSPAN}">
				<table width="100%" border="1" cellpadding="5" cellspacing="0" align="center" valign="bottom">
				<caption style="caption-side:top">{'LBL_BUSY_MGCHAUFFEURS_ARRAY'|@vtranslate:$MODULE}{$PREVIOUS_DATE}</caption>
				<thead>
					<tr>
					{foreach item=MGCHAUFFEURS key=ACTIVITY_TYPE from=$BUSY_MGCHAUFFEURS_ARRAYS[$PREVIOUS_DATE]}
						<th>{$ACTIVITY_TYPE}</th>
					{/foreach}
					</tr>	
				</thead>
				<tbody>
					<tr>
					{foreach item=MGCHAUFFEURS key=ACTIVITY_TYPE from=$BUSY_MGCHAUFFEURS_ARRAYS[$PREVIOUS_DATE]}	
					<td>
						{foreach item=MGCHAUFFEUR from=$MGCHAUFFEURS}
						{$MGCHAUFFEUR} <br/>
						{/foreach}
					</td>
					{/foreach}	
					</tr>
				</tbody>						
				</table>
			</td>
			</tr>
				{/if}
			<tr>
			<td align="left" valign="top" style="border:0px solid #000000;" colspan="{$COLSPAN}">
				<b>{$STRING_FULLDAY|@vtranslate:$MODULE} {$STRING_DAY|@vtranslate:$MODULE} {$STRING_FULLMONTH|@vtranslate:$MODULE} {$STRING_FULLYEAR}<b>	
			</td>
			</tr>
			{/if}		
			<tr>						
				<td align="left" style="border:0px solid #000000; padding-left:1em" colspan="{$COLSPAN}">
					<b>{$PRINTLIST_ENTRY->get('subject')}</b>{$PRINTLIST_ENTRY->get('account')}					
				</td>
			</tr>
			<tr>
			{foreach item=PRINTLIST_HEADER from=$PRINTLIST_HEADERS}
				{assign var=HEADERNAME value=$PRINTLIST_HEADER->get('name')}
				{if ($HEADERNAME neq 'datetransport' && $HEADERNAME neq 'subject'&& $HEADERNAME neq 'account')}
				<td align="left" valign="top" style="border:0px solid #000000; padding-left:3em; padding-bottom:1em" >
					{$PRINTLIST_ENTRY->get($HEADERNAME)}
				</td>				
				{/if}
			{/foreach}
			</tr>
			{if $PRINTLIST_ENTRY@last}
			<tr>
			<td align="left" valign="top" style="border:0px solid #000000;" colspan="{$COLSPAN}">
				<table width="100%" border="1" cellpadding="5" cellspacing="0" align="center" valign="bottom">
				<caption style="caption-side:top">{'LBL_BUSY_MGCHAUFFEURS_ARRAY'|@vtranslate:$MODULE}{$ENTRY_DATE}</caption>
				<thead>
					<tr>
				{foreach item=MGCHAUFFEURS key=ACTIVITY_TYPE from=$BUSY_MGCHAUFFEURS_ARRAYS[$ENTRY_DATE]}
					<th>
						{$ACTIVITY_TYPE}
					</th>
				{/foreach}
					</tr>	
				</thead>
				<tbody>
				<tr>
				{foreach item=MGCHAUFFEURS key=ACTIVITY_TYPE from=$BUSY_MGCHAUFFEURS_ARRAYS[$ENTRY_DATE]}	
					<td>
					{foreach item=MGCHAUFFEUR from=$MGCHAUFFEURS}
					{$MGCHAUFFEUR} <br/>
					{/foreach}
					</td>
				{/foreach}	
				</tr>
				</tbody>						
				</table>
			</td>
			</tr>
			{/if}
			{assign var=PREVIOUS_DATE value=$ENTRY_DATE}
		{/foreach}		
		{else}
			<table width="100%" border="1" cellpadding="5" cellspacing="0" align="center" valign="bottom" class="printReport" >					
				{$PRINT_DATA}
		{/if}		
			</table>
		</td>
	</tr>
	</table>
</body>
</html>
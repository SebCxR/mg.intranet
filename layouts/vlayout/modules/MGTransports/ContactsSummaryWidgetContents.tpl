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
<div class="relatedContainer">
    <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE}" />
</div>
<table class="table table-bordered listViewEntriesTable unstyled">
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
			
			<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
				
					<div class="row-fluid">
						{assign var=PHONE value=$RELATED_RECORD->getDisplayValue('phone')}
						{if $PHONE && $PHONE neq 'NULL'} {else} {assign var=PHONE value=vtranslate('LBL_UNKNOWN', $MODULE)}{/if}
						
						{assign var=MAIL value=$RELATED_RECORD->getDisplayValue('email')}
						 
						
						<td class="span3 textOverflowEllipsis" nowrap>
							<h6>
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="Tel : {$PHONE}">
								{$RELATED_RECORD->getDisplayValue('lastname')}
							</a>
							</h6>
							{if $MAIL && $MAIL neq 'NULL'}						
							<span>
							{$MAIL}	
							</span>
							{/if}
						</td>
					
					<td class="span1" style="border: none !important"-->
						
						<div class="pull-right actions">
							{if $RELATED_RECORD->getId() eq $MAINCONTACT}
							<span class="pull-right">{vtranslate('LBL_MAINCONTACT',$MODULE)} </span>
							{else}
							<span class="actionImages">
							<span class="pull-right"><a class="relationDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a></span>
							</span>
							
							{/if}
							
						</div>	
					</td>
					</div>		
			</tr>	
	{/foreach}
</table>	
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentContacts cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}

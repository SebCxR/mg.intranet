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
		<tr class="listViewEntries row-fluid" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
			<td class="span9 textOverflowEllipsis" nowrap>
				<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('subject')}">
					{$RELATED_RECORD->getDisplayValue('subject')}
				</a>
			    
				<span class="pull-right">{$RELATED_RECORD->getDisplayValue('datetransport')}</span>
			
			</td>
			<td class="span1" style="border: none !important"-->
			<div class="pull-right actions">
			    <span class="actionImages">
				<span class="pull-right"><a class="relationDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a></span>
			    </span>
			</div>
			</td>
		</tr>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<tr>
			<div class="row-fluid">
				<div class="pull-right">
					<a class="moreRecentProducts cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
				</div>
			</div>
		</tr>
	{/if}
</table>
{/strip}

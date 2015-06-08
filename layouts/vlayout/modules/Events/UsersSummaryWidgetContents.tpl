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
    {*var_dump($RELATED_RECORDS)*}
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
		   
		    {assign var=PHONE value=$RELATED_RECORD->getDisplayValue('phone_mobile')}
			    {if $PHONE && $PHONE neq 'NULL'} {else} {assign var=PHONE value=vtranslate('LBL_UNKNOWN', $MODULE)}{/if}							 			
			    <td class="span3 textOverflowEllipsis" nowrap>
				    <h6>
				    <a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="Tel : {$PHONE}">
					    {$RELATED_RECORD->getDisplayValue('first_name')} {$RELATED_RECORD->getDisplayValue('last_name')}
				    </a>
				    </h6>
			    </td>
			   
			    <td class="span1 pull-right" style="border: none !important"-->
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

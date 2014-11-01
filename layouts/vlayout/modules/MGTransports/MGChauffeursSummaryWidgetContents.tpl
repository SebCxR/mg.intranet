{*<!--
/*********************************************************************************
** ED
********************************************************************************/
-->*}
{strip}
<div class="relatedContainer">
    <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE}" />
</div>
<table class="table table-bordered listViewEntriesTable unstyled">
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<tr class="listViewEntries row-fluid" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
			<td class="span6 textOverflowEllipsis" nowrap>
			    <a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('vehicule_name')}">
				{$RELATED_RECORD->getDisplayValue('name')}
			    </a>
			</td>
			<td class="span3" style="border: none !important"-->
			    <span class="pull-right">{$RELATED_RECORD->getDisplayValue('phone_mobile')}&nbsp;</span>
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
</table>
{/strip}
{*<!--
/*********************************************************************************
** ED
********************************************************************************/
-->*}
{strip}
<div class="relatedContainer">
    <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE}" />
</div>
{*var_dump($BUSYLIST)*}
<table class="table table-bordered listViewEntriesTable unstyled">
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		{assign var=CHAUFFEURID value={$RELATED_RECORD->getId()}}
		<tr class="listViewEntries {if $BUSYLIST[$VEHICULEID]} inBusyConflict{/if}" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
			<td class="span3 textOverflowEllipsis" nowrap>
			    <a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="Tel : {$RELATED_RECORD->getDisplayValue('phone_mobile')}">
				{$RELATED_RECORD->getDisplayValue('name')}
			    </a>
			</td>
			{if $BUSYLIST[$CHAUFFEURID]}				
			    <td class="span3 rowfluid busyState">
				<span class="ui-icon ui-icon-alert" title="{vtranslate('LBL_CONFLICT_WITH', {$RELATED_MODULE})}"></span>
				{foreach key=EVENTID item=EVENTINFO from=$BUSYLIST[$CHAUFFEURID] name=eventinfolist}
				    <a href='{$EVENTINFO['href']}' title='{vtranslate({$EVENTINFO['type']}, {$EVENTINFO['modulename']})}' style='color: red'>{$EVENTINFO['label']}
				    {if $smarty.foreach.eventinfolist.last}. {else}, {/if}
				    </a>
				{/foreach}
			    </td>
			{else}
			<td class="span3">	
			</td>
			{/if}
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
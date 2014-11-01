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
			<td class="span7 textOverflowEllipsis" nowrap>
				<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('vehicule_name')}">
					{$RELATED_RECORD->getDisplayValue('vehicule_name')}
				</a>
			</td>
			<td class="span3" style="border: none !important"-->
				<span class="pull-right">{$RELATED_RECORD->getDisplayValue('vehicule_type')}</span>
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

{*
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div>
			<ul class="unstyled">
				<li>
					<div class="row-fluid">
						<span class="span7 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('vehicule_name')}">
								{$RELATED_RECORD->getDisplayValue('vehicule_name')}
							</a>
						</span>
						<span class="span4">
							<span class="pull-right">{$RELATED_RECORD->getDisplayValue('vehicule_type')}</span>
						</span>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
*}

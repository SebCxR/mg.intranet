{*<!--
/*********************************************************************************
** ED
********************************************************************************/
-->*}
{strip}
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="recentActivitiesContainer">
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
{/strip}

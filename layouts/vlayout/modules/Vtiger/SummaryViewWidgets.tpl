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
{if !empty($MODULE_SUMMARY)}
	<div class="row-fluid">
		<div class="span7">
			<div class="summaryView row-fluid">
			{$MODULE_SUMMARY}
			</div>
{/if}
			{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
				{if $smarty.foreach.count.index % 2 == 0}
					<div class="summaryWidgetContainer">
						<div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
							<div class="widget_header row-fluid">
								<span class="span8 margin0px"><h4>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4></span>
								{assign var=RECORD_ACTIONS value=$DETAIL_VIEW_WIDGET->get('action')}
								{if is_array($RECORD_ACTIONS)}
									{* ED141101 'actionlabel' property of widget. As 'action', it is an array. *}
									{assign var=RECORD_ACTION_LABELS value=$DETAIL_VIEW_WIDGET->get('actionlabel')}
									{assign var=RECORD_ACTION_IDX value=0}
									{foreach item=RECORD_ACTION from=$RECORD_ACTIONS}
										{assign var=IS_SELECT_BUTTON value={$RECORD_ACTION eq "Select"}}
										<button type="button" class="btn addButton
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
										{if $IS_SELECT_BUTTON eq true} data-moduleName={$DETAIL_VIEW_WIDGET->get('linkName')} {/if}
										data-url="{$DETAIL_VIEW_WIDGET->get('actionURL')}"
										{if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>
											{if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}
											{if $RECORD_ACTION_LABELS}{assign var=RECORD_ACTION_LABEL value=$RECORD_ACTION_LABELS[$RECORD_ACTION_IDX]}
											{else}{assign var=RECORD_ACTION_LABEL value=vtranslate('LBL_'|cat:strtoupper($RECORD_ACTION),$MODULE_NAME)}
											{/if}
											&nbsp;<strong>{$RECORD_ACTION_LABEL}</strong>
										</button>
										{assign var=RECORD_ACTION_IDX value=$RECORD_ACTION_IDX+1}
									{/foreach}
									<input type="hidden" name="relatedModule" value="{$DETAIL_VIEW_WIDGET->get('linkName')}" />
								{/if}
							</div>
							<div class="widget_contents">
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		<div class="span5" style="overflow: hidden">
			<div id="relatedActivities">
				{$RELATED_ACTIVITIES}
			</div>
			{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
				{if $smarty.foreach.count.index % 2 != 0}
					<div class="summaryWidgetContainer">
						<div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}" data-name="{$DETAIL_VIEW_WIDGET->getLabel()}">
							<div class="widget_header row-fluid">
								<span class="span7 margin0px"><h4>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4></span>
								{assign var=RECORD_ACTIONS value=$DETAIL_VIEW_WIDGET->get('action')}
								{if is_array($RECORD_ACTIONS)}
									{* ED141101 'actionlabel' property of widget. As 'action', it is an array. *}
									{assign var=RECORD_ACTION_LABELS value=$DETAIL_VIEW_WIDGET->get('actionlabel')}
									{assign var=RECORD_ACTION_IDX value=0}
									{foreach item=RECORD_ACTION from=$RECORD_ACTIONS}
										{assign var=IS_SELECT_BUTTON value={$RECORD_ACTION eq "Select"}}
										<button type="button" class="btn addButton
										{if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
										{if $IS_SELECT_BUTTON eq true} data-moduleName={$DETAIL_VIEW_WIDGET->get('linkName')} {/if}
										data-url="{$DETAIL_VIEW_WIDGET->get('actionURL')}"
										{if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>
											{if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}
											{if $RECORD_ACTION_LABELS}{assign var=RECORD_ACTION_LABEL value=$RECORD_ACTION_LABELS[$RECORD_ACTION_IDX]}
											{else}{assign var=RECORD_ACTION_LABEL value=vtranslate('LBL_'|cat:strtoupper($RECORD_ACTION),$MODULE_NAME)}
											{/if}
											&nbsp;<strong>{$RECORD_ACTION_LABEL}</strong>
										</button>
										{assign var=RECORD_ACTION_IDX value=$RECORD_ACTION_IDX+1}
									{/foreach}
									<input type="hidden" name="relatedModule" value="{$DETAIL_VIEW_WIDGET->get('linkName')}" />
								{/if}
							</div>
							<div class="widget_contents">
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
{/strip}
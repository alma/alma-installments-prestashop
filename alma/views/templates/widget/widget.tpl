{if !$isExcluded}
    <div id="{$container}" data-widget-config="{$widgetConfig}"></div>
{elseif $showExcludedMessage}
    <div class="alma-widget-excluded-message">
        <img src="{$almaLogoUrl}" alt="Alma" />
        <p>{$excludedMessage}</p>
    </div>
{/if}

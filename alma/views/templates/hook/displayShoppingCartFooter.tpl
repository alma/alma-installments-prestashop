{*
 * 2018-2022 Alma SAS
 *
 * THE MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the
 * Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @author    Alma SAS <contact@getalma.eu>
 * @copyright 2018-2022 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}
 
<div style="display:none">
    <input type="hidden" id="alma-widget-config" value='{$widgetQuerySelectors|escape:'htmlall':'UTF-8'}' />
</div>
{if $isExcluded}
    <div style="margin: 15px 0">
        <img src="{$logo|escape:'htmlall':'UTF-8'}"
            style="width: auto !important; height: 25px !important; border: none !important; vertical-align: middle"
            alt="Alma"> <span style="text-transform: initial">{$eligibility_msg|escape:'htmlall':'UTF-8'}</span>
    </div>
{else}
    <div {if $settings.psVersion != "1.7"} style="margin:15px 0" {/if} id="alma-cart-widget" class="alma-widget-container" data-settings="{$settings|almaJsonEncode|escape:'htmlall':'UTF-8'}"></div>
    <script type="text/javascript">window.__alma_refreshWidgets && __alma_refreshWidgets();</script>
{/if}

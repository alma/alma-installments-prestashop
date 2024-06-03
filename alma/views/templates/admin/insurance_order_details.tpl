{*
 * 2018-2023 Alma SAS
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
 * @copyright 2018-2023 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}
<div id="alma-insurance-modal"></div>
<script data-cfasync="false" type="module" src="{$modalScriptUrl}"></script>

<script type="text/javascript">
    var dataSubscriptions = {$dataSubscriptions};
</script>

<div class="panel" id="fieldset_0">
    <div class="panel-heading">
        <img src="/modules/alma/views/img/logos/alma_tiny.svg" alt="{l s='Order details' mod='alma'}">{l s='Orders details' mod='alma'}
    </div>
    <div class="form-wrapper">
        <div class="form-group">
            <div class="alma--insurance-bo-form">
                <iframe id="subscription-alma-iframe" class="alma-insurance-iframe" src="{$iframeUrl}"></iframe>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{$scriptUrl}"></script>

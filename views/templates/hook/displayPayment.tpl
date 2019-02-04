{*
* 2018 Alma / Nabla SAS
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
* @author    Alma / Nabla SAS <contact@getalma.eu>
* @copyright 2018 Alma / Nabla SAS
* @license   https://opensource.org/licenses/MIT The MIT License
*}

<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            {if $disabled}
                <a href="#" onclick="return false;" class="disabled alma-button">
                    <span class="alma-button--logo">
                        <img src="{$logo}" alt="Alma">
                    </span>
                    <span class="alma-button--text">
                        <span class="alma-button--description">
                            {if $error}
                                {l s='Alma monthly payments are not available due to an error' mod='alma'}
                            {else}
                                {l s='Alma monthly payments are not available for this order' mod='alma'}
                            {/if}
                        </span>
                    </span>
                </a>
            {else}
                <a href="{$link->getModuleLink('alma', 'payment')|escape:'html'}" class="alma-button">
                    <span class="alma-button--logo">
                        <img src="{$logo}" alt="Alma">
                    </span>
                    <span class="alma-button--text">
                        <span class="alma-button--title">{$title}</span>
                        <br>
                        <span class="alma-button--description">{$desc}</span>
                    </span>
                </a>
            {/if}
        </p>
    </div>
</div>

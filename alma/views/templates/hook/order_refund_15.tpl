{*
 * 2018-2021 Alma SAS
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
 * @copyright 2018-2021 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}

<br>
<fieldset>
    <legend>
        <img src="{$iconPath}"/>
        {l s='Alma refund' mod='alma'}
    </legend>
    <form method="POST" action="" class="defaultForm form-horizontal">
        <input type="hidden" class="alma" name="orderID" required value="{$order.id}" />
        <div class="panel15">
            <div class="form-wrapper">
                <div class="form-group-15" id="amountDisplay">
                    <label class="control-label col-lg-3 required"> {l s='Amount (Max. %s):' sprintf=$order.maxAmount mod='alma'}</label>
                    <div class="col-lg-9">
                        <div class="input-group">
                            <span class="input-group-addon">{$order.currencySymbole}</span>
                            <input type="number" step="0.01" class="alma-input-number" id="amount" value="" name="amount" placeholder="{l s='Amount to refund...' mod='alma'}" />
                        </div>
                    </div>            
                </div>   
                <div class="form-group-15">
                    <label class="control-label col-lg-3 required"> {l s='Refund type:' mod='alma'}</label>
                    <div class="col-lg-9">
                        <div class="radio t">
                            <label class="t">
                                <input type="radio" name="refundType" value="total"/>
                                {l s='Total' mod='alma'}
                            </label>
                        </div>
                        <div class="radio t">
                            <label class="t">
                                <input type="radio" name="refundType" value="partial" checked="checked"/>
                                {l s='Partial' mod='alma'}
                            </label>
                        </div>
                    </div>
                </div>
                <p>
                {l s='Texte : remboursement alma + changement de status si total bla bla bla toussa toussa...' mod='alma'}
                </p>
            </div>
            <div class="panel-footer clear">
                <button type="submit" class="button btn btn-primary button-medium pull-right-15"><span>{l s='Process this refund' mod='alma'}</button>                
            </div>  
        </div>
    </form>
</fieldset>

<script type="text/javascript">
    $(function(){    
        $("#amount").prop('required',true);
        $('input[type=radio][name=refundType]').change(function() {
            if (this.value == 'total') {
                $('#amountDisplay').hide();
                $("#amount").prop('required',false);
            }
            else if (this.value == 'partial') {
                $('#amountDisplay').show();
                $("#amount").prop('required',true);
            }
        });
    });
</script>

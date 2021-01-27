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

<form id="alma-refund" method="POST" action="{$actionUrl}" class="defaultForm form-horizontal">
    <input type="hidden" class="alma" name="orderId" required value="{$order.id}"/>
    <div class="panel" id="alma_refunds">
        <div class="panel-heading">
            <img src="{$iconPath}"/>
            {l s='Alma refund' mod='alma'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3"></label>
                <div class="col-lg-9">
                    <p>
                        Texte : remboursement alma + changement de status si total bla bla bla toussa toussa...
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Refund type:' mod='alma'}</label>
                <div class="col-lg-9">
                    <div class="radio t">
                        <label>
                            <input type="radio" name="refundType" value="total" checked="checked"/>
                            {l s='Total' mod='alma'}
                        </label>
                    </div>
                    <div class="radio t">
                        <label>
                            <input type="radio" name="refundType" value="partial"/>
                            {l s='Partial' mod='alma'}
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group" id="amountDisplay" style="display: none">
                <label class="control-label col-lg-3 required"> {l s='Amount (Max. %s):' sprintf=$order.maxAmount mod='alma'}</label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <span class="input-group-addon">{$order.currencySymbol}</span>
                        <input type="text" class="alma" value="" name="amount"
                               placeholder="{l s='Amount to refund...' mod='alma'}"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer clear">
            <button type="submit" class="button btn btn-primary button-medium pull-right">
                <span>{l s='Process this refund' mod='alma'}</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(function () {
        $('input[type=radio][name=refundType]').change(function () {
            if (this.value === 'total') {
                $('#amountDisplay').hide();
                $("#amount").prop('required', false);
            } else if (this.value === 'partial') {
                $('#amountDisplay').show();
                $("#amount").prop('required', true);
            }
        });

        var $form = $('form#alma-refund');
        $form.submit(function (e) {
            if (e) {
                e.preventDefault();

                $.ajax({
                    type: 'POST',
                    url: $form.attr('action'),
                    dataType: 'json',
                    data: {
                        ajax: true,
                        action: 'Refund',
                        orderId: $form.find('[name=orderId]').val(),
                        refundType: $form.find('[name=refundType]:checked').val(),
                        amount: $form.find('[name=amount]').val(),
                    }
                })
                .done(function (data) {
                    console.log(data);
                })
                .fail(function (data) {
                    console.log(data);
                });

                return false;
            }
        });
    });
</script>

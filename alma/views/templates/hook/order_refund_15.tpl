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
<div class="alma-success conf" style="display:none"></div>
<div class="alma-danger error" style="display:none"></div>
<fieldset>
    <legend>
        <img src="{$iconPath}"/>
        {l s='Alma refund' mod='alma'}
    </legend>
    <form id="alma-refund"  method="POST" action="{$actionUrl}" class="defaultForm form-horizontal">
        <input type="hidden" class="alma" name="orderID" required value="{$order.id}" />
        <p>
            {l s='Texte : remboursement alma + changement de status si total bla bla bla toussa toussa...' mod='alma'}
        </p>                           
        <div class="form-group-15">
            <p><b>{l s='Refund type:' mod='alma'}</b></p>                                    
            <table>
                <tr>
                    <td><input type="radio" name="refundType" value="total" checked="checked"/></td>
                    <td>
                        {if $order.ordersId}
                            {l s='Refund the integrity of this purchase' mod='alma'}
                            <br>
                            <i>{l s='Refund this order (id : %1$d) and all linked orders (id : %2$s)' sprintf=array($order.id, $order.ordersId) mod='alma'}
                            <br>
                            {l s='Total amount : %s' sprintf=$order.ordersTotalAmount mod='alma'}
                            </i>
                        {else}
                            {l s='Total' mod='alma'}
                        {/if}
                    </td>
                </tr>
                {if $order.ordersId}
                <tr>
                    <td><input type="radio" class="refundType" name="refundType" value="partial_multi" /></td>
                    <td>{l s='Only this purchase (%s)' sprintf=$order.maxAmount mod='alma'}</td>
                </tr>
                {/if} 
                <tr>
                    <td><input type="radio" name="refundType" value="partial" /></td>
                    <td>{l s='Partial' mod='alma'}</td>
                </tr>
            </table>
            <table>
                <tr style="display:none;" id="amountDisplay">
                    <td>
                    {l s='Amount (Max. %s):' sprintf=$order.maxAmount mod='alma'}
                    </td>
                    <td>
                    <input type="text" class="alma-input-number" id="amount" value="" name="amount" placeholder="{l s='Amount to refund...' mod='alma'}" />
                    </td>
                </tr>   
            </table>                                                 
        </div>
        <div class="panel-footer clear">
            <button type="submit" class="button btn btn-primary button-medium pull-right-15"><span>{l s='Process this refund' mod='alma'}</button>                
        </div>         
    </form>
</fieldset>

<script type="text/javascript">
    $(function () {
        var $form = $('form#alma-refund');

        $('input[type=radio][name=refundType]').change(function () {
            if (this.value === 'partial') {
                $('#amountDisplay').show();                
                $($form.find('[name=amount]')).prop('required', true);
            } else {
                $('#amountDisplay').hide();
                $($form.find('[name=amount]')).prop('required', false);
            }            
        });

        
        $form.submit(function (e) {
            if (e) {
                e.preventDefault();
                $('.alma-danger').html('').hide();
                $('.alma-success').html('').hide();

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
                    $('.alma-success').html(data.message).show();
                })
                .fail(function (data) { 
                    console.log(data);                                       
                    //$('.alma-danger').html(data.responseJSON.message).show();                    
                });

                return false;
            }
        });
    });
</script>

{include file='./_partials/refunds.tpl'}

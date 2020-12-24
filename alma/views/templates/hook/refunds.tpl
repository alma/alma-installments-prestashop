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

{if $error}
<div class="alert alert-danger">
    {$error|escape}
    <button type="button" class="close" data-dismiss="alert">×</button>
</div>
{/if}
{if $success}
<div class="alert alert-success">
    {$success|escape}
    <button type="button" class="close" data-dismiss="alert">×</button>
</div>
{/if}
<form method="POST" action="" class="defaultForm form-horizontal">
    <input type="hidden" class="alma" name="orderID" required value="{$order.id}" />
    <div class="panel">
        <div class="panel-heading">
            <img src="{$iconPath}"/>
            {l s='Alma refund' mod='AdminAlmaRefunds'}
        </div>
        <div class="form-wrapper">            
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='Order ID:' mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">                    
                    <div class="radio">{$order.id}</div>
                </div>            
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='Reference:' mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">                    
                    <div class="radio">{$order.reference}</div>
                </div>            
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='Order amount:' mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">                    
                    <div class="radio">{$order.maxAmount}</div>
                </div>            
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='Customer:' mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">                    
                    <div class="radio">{$customer.firstName} {$customer.lastName}</div>
                </div>            
            </div>
            <div class="form-group">
                <label class="control-label col-lg-3"> {l s='Email:' mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">                    
                    <div class="radio">{$customer.email}</div>
                </div>            
            </div>
            <div class="form-group" id="amountDisplay">
                <label class="control-label col-lg-3"> {l s='Amount (Max. %s):' sprintf=$order.maxAmount mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <span class="input-group-addon">{$order.currencySymbole}</span>
                        <input type="number" step="0.01" class="alma" id="amount" value="{$order.amountRefund}" name="amount" placeholder="{l s='Amount to refund...' mod='AdminAlmaRefunds'}" />
                    </div>
                </div>            
            </div>   
            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Refund type:' mod='AdminAlmaRefunds'}</label>
                <div class="col-lg-9">
                    <div class="radio t">
                        <label>
                            <input type="radio" name="refundType" value="total"/>
                            {l s='Total' mod='AdminAlmaRefunds'}
                        </label>
                    </div>
                    <div class="radio t">
                        <label>
                            <input type="radio" name="refundType" value="partial" checked="checked"/>
                            {l s='Partial' mod='AdminAlmaRefunds'}
                        </label>
                    </div>                
                </div>            
            </div>       
        </div>  
        <div class="panel-footer">
                <button type="submit" class="button btn btn-default button-medium pull-right"><span>{l s='Process this refund' mod='AdminAlmaRefunds'}</button>                                         
                <button type="button" data-url="{$moduleUrl}" id="backToOrdersList" class="button btn btn-default button-medium pull-left"><span>{l s='Back to Alma orders list' mod='AdminAlmaRefunds'}</button>                                         
        </div>  
    </div>
</form>

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
        $('#backToOrdersList').click(function(){            
            window.location.href= $(this).data('url');
        })
    });
</script>

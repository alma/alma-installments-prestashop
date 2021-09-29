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
                $($form.find('[type=submit]')).attr("disabled", true);
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
                    $('.alma-progress').show();
                    $('.alma-progress .progress-bar').attr('aria-valuenow', data.percentRefund)
                    .width(data.percentRefund + '%')
                    .html(data.totalRefundAmount + ' / ' + data.totalOrderAmount);
                    if (data.totalRefund >= data.totalOrder) {
                        $form.addClass('disabled')
                    };
                })
                .fail(function (data) {
                    var jsondata = JSON.parse(data.responseText);                    
                    $('.alma-danger').html(jsondata.message).show();
                })
                .always(function(){
                    $($form.find('[type=submit]')).attr("disabled", false);
                });

                return false;
            }
        });
    });
</script>

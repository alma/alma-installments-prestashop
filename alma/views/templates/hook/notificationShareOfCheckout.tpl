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

<div class="alma share-of-checkout alert alert-info">
    <div class="row">
        <h2>{l s='Increase your performance & get insights !' mod='alma'}</h2>
        <p>
            {l s='By accepting this option, enable Alma to analyse the usage of your payment methods, [1]get more information to perform[/1] and share this data with you. You can [2]unsubscribe and erase your data[/2] at any moment.' tags=['<b>', '<a href="mailto:support@getalma.eu">'] mod='alma'}
        </p>
        <p>
            <a data-toggle="collapse" href="#collapseModalSoc" role="button" aria-expanded="false" aria-controls="collapseModalSoc" class="link-collapse collapsed">
                {l s='Know more about collected data' mod='alma'}
                <i class="icon icon-chevron-down"></i>
                <i class="icon icon-chevron-up"></i>
            </a>
        </p>
        <div class="collapse" id="collapseModalSoc">
            <div class="card card-body">
                <ul>
                    <li>{l s='total quantity of orders, amounts and currencies' mod='alma'}</li>
                    <li>{l s='payment provider for each order' mod='alma'}</li>
                    <li>{l s='customers order history' mod='alma'}</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <p>
            <a class="btn btn-default btn-share-of-checkout" data-consent='0' data-token='{$token}' href="#">{l s='Reject' mod='alma'}</a>
            <a class="btn btn-primary btn-share-of-checkout" data-consent='1' data-token='{$token}' href="#">{l s='Accept' mod='alma'}</a>
        </p>
    </div>
</div>

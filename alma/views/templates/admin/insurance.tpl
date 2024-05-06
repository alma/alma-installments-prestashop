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
<div class="alert alert-success alma-success"  style="display:none" data-alert="success"></div>
<div class="alert alert-danger alma-danger" style="display:none" data-alert="danger"></div>

<div class="panel" id="fieldset_0">
    <div class="panel-heading">
        <img src="/modules/alma/views/img/logos/alma_tiny.svg" alt="{l s='Configuration' mod='alma'}">{l s='Configuration' mod='alma'}
    </div>
    <div class="form-wrapper">
        <div class="form-group">
            <div class="alma--insurance-bo-form">
                <iframe id="config-alma-iframe" class="alma-insurance-iframe" src="{$iframeUrl}"></iframe>
            </div>
        </div>
    </div><!-- /.form-wrapper -->
    <div class="panel-footer">
        <button type="submit" value="1" id="alma_config_form_submit_btn" name="alma_config_form" class="button btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='alma'}
        </button>
    </div>
</div>
<script type='module'>
    window.addEventListener("load", (e) => {
        let insuranceConfigurationParams = {$insuranceConfigurationParams};

        sendConfigurationInsuranceParams(insuranceConfigurationParams);
        loadConfigurationInsurance('{$domainInsuranceUrl}', '{$insuranceConfigurationController}', '{$token}');
    });
</script>

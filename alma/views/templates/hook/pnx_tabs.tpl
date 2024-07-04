{*
 * 2018-2024 Alma SAS
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
 * @copyright 2018-2024 Alma SAS
 * @license   https://opensource.org/licenses/MIT The MIT License
 *}
<div id="alma-pnx-tabs">
    <ul class="nav nav-tabs">
        {foreach $tabs as $tabId => $title}
            <li {if $active == $tabId}class="active"{/if}><a href="#{$tabId|escape:'htmlall':'UTF-8'}" data-toggle="tab">{$title|escape:'htmlall':'UTF-8'}</a></li>
        {/foreach}
    </ul>
    <div class="tab-content panel">
        {foreach $tabs as $tabId => $title}
            <div id="{$tabId|escape:'htmlall':'UTF-8'}" class="tab-pane{if $active == $tabId} active{/if}"></div>
        {/foreach}
    </div>
</div>

<script type="text/javascript">
    (function($) {
        $(function() {
            {if isset($tabs) && $tabs|count}
                if (typeof helper_tabs == 'undefined') {
                    var helper_tabs = {$tabs|json_encode};
                    var unique_field_id = '';
                }
            {/if}
            var $tabs = $("#alma-pnx-tabs");
            var $formWrapper = $tabs.closest(".form-wrapper").addClass('alma-pnx-tabs');

            $tabs.children().prependTo($formWrapper);

            {foreach $tabs as $tabId => $title}
                $(".{$tabId|escape:'htmlall':'UTF-8'}-content").appendTo("#{$tabId|escape:'htmlall':'UTF-8'}");
            {/foreach}

            {if $forceTabs}
                $('.nav-tabs li a').on('click', function(e) {
                    e.preventDefault();
                    var target = $(this).attr('href');
                    $('.tab-pane').hide();
                    $('.nav-tabs li').removeClass('active');
                    $(target).show();
                    $(this).parent().addClass('active');
                    return false;
                })
            {/if}
        });
    })(jQuery);
</script>

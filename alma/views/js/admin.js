$(document).ready(function () {
    if (typeof helper_tabs != 'undefined' && typeof unique_field_id != 'undefined') {
        $.each(helper_tabs, function (index) {
            $('#' + unique_field_id + 'fieldset_' + index + ' .form-wrapper').prepend('<div class="tab-content panel" />');
            $('#' + unique_field_id + 'fieldset_' + index + ' .form-wrapper').prepend('<ul class="nav nav-tabs" />');
            $.each(helper_tabs[index], function (key, value) {
                // Move every form-group into the correct .tab-content > .tab-pane
                $('#' + unique_field_id + 'fieldset_' + index + ' .tab-content').append('<div id="' + key + '" class="tab-pane" />');
                var elemts = $('#' + unique_field_id + 'fieldset_' + index).find("[data-tab-id='" + key + "']");
                $(elemts).appendTo('#' + key);
                // Add the item to the .nav-tabs
                if (elemts.length != 0)
                    $('#' + unique_field_id + 'fieldset_' + index + ' .nav-tabs').append('<li><a href="#' + key + '" data-toggle="tab">' + value + '</a></li>');
            });
            // Activate the first tab
            $('#' + unique_field_id + 'fieldset_' + index + ' .tab-content div').first().addClass('active');
            $('#' + unique_field_id + 'fieldset_' + index + ' .nav-tabs li').first().addClass('active');
        });
    }
});

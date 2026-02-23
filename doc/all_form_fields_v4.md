# TO BE REMOVED WHEN THE FULL FORM WILL BE CREATED
## Array of form fields from our module v4 for knowledge
array(9) {
[0]=>
array(1) {
["form"]=>
array(3) {
["legend"]=>
array(2) {
["title"]=>
string(18) "Installments plans"
["image"]=>
string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
}
["input"]=>
array(41) {
[0]=>
array(4) {
["name"]=>
NULL
["label"]=>
NULL
["type"]=>
string(4) "html"
["html_content"]=>
string(3155) "<div id="alma-pnx-tabs">
<ul class="nav nav-tabs">
<li ><a href="#general_1_0_0" data-toggle="tab">&#10060; Pay now</a></li>
<li ><a href="#general_2_0_0" data-toggle="tab">&#10060; 2-installment payments</a></li>
<li class="active"><a href="#general_3_0_0" data-toggle="tab">&#9989; 3-installment payments</a></li>
<li ><a href="#general_4_0_0" data-toggle="tab">&#10060; 4-installment payments</a></li>
<li ><a href="#general_10_0_0" data-toggle="tab">&#10060; 10-installment payments</a></li>
<li ><a href="#general_12_0_0" data-toggle="tab">&#10060; 12-installment payments</a></li>
<li ><a href="#general_1_15_0" data-toggle="tab">&#10060; Deferred payments + 15 days</a></li>
<li ><a href="#general_1_30_0" data-toggle="tab">&#10060; Deferred payments + 30 days</a></li>
</ul>
<div class="tab-content panel">
<div id="general_1_0_0" class="tab-pane"></div>
<div id="general_2_0_0" class="tab-pane"></div>
<div id="general_3_0_0" class="tab-pane active"></div>
<div id="general_4_0_0" class="tab-pane"></div>
<div id="general_10_0_0" class="tab-pane"></div>
<div id="general_12_0_0" class="tab-pane"></div>
<div id="general_1_15_0" class="tab-pane"></div>
<div id="general_1_30_0" class="tab-pane"></div>
</div>
</div>
<script type="text/javascript">
    (function($) {
        $(function() {
                            if (typeof helper_tabs == 'undefined') {
                    var helper_tabs = {"general_1_0_0":"\u274c Pay now","general_2_0_0":"\u274c 2-installment payments","general_3_0_0":"\u2705 3-installment payments","general_4_0_0":"\u274c 4-installment payments","general_10_0_0":"\u274c 10-installment payments","general_12_0_0":"\u274c 12-installment payments","general_1_15_0":"\u274c Deferred payments + 15 days","general_1_30_0":"\u274c Deferred payments + 30 days"};
                    var unique_field_id = '';
                }
                        var $tabs = $("#alma-pnx-tabs");
            var $formWrapper = $tabs.closest(".form-wrapper").addClass('alma-pnx-tabs');
            $tabs.children().prependTo($formWrapper);
                            $(".general_1_0_0-content").appendTo("#general_1_0_0");
                            $(".general_2_0_0-content").appendTo("#general_2_0_0");
                            $(".general_3_0_0-content").appendTo("#general_3_0_0");
                            $(".general_4_0_0-content").appendTo("#general_4_0_0");
                            $(".general_10_0_0-content").appendTo("#general_10_0_0");
                            $(".general_12_0_0-content").appendTo("#general_12_0_0");
                            $(".general_1_15_0-content").appendTo("#general_1_15_0");
                            $(".general_1_30_0-content").appendTo("#general_1_30_0");
                    });
    })(jQuery);
</script>
"
}
[1]=>
array(6) {
["name"]=>
NULL
["label"]=>
bool(false)
["type"]=>
string(4) "html"
["form_group_class"]=>
string(21) "general_1_0_0-content"
["col"]=>
int(12)
["desc"]=>
string(545) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>

<p>
    <b>
            You can offer 1-installment payments for amounts between 0€ and 3000€.
        </b>
</p>

<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        0.9%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 1-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [2]=>
        array(6) {
          ["name"]=>
          string(26) "ALMA_general_1_0_0_ENABLED"
          ["label"]=>
          string(14) "Enable pay now"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(21) "general_1_0_0-content"
        }
        [3]=>
        array(7) {
          ["name"]=>
          string(29) "ALMA_general_1_0_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(true)
          ["form_group_class"]=>
          string(21) "general_1_0_0-content"
          ["max"]=>
          int(3000)
        }
        [4]=>
        array(7) {
          ["name"]=>
          string(29) "ALMA_general_1_0_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_1_0_0-content"
          ["max"]=>
          int(3000)
        }
        [5]=>
        array(6) {
          ["name"]=>
          string(29) "ALMA_general_1_0_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_1_0_0-content"
        }
        [6]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(21) "general_2_0_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(546) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>

<p>
    <b>
            You can offer 2-installment payments for amounts between 50€ and 3000€.
        </b>
</p>

<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        3.4%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 2-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [7]=>
        array(6) {
          ["name"]=>
          string(26) "ALMA_general_2_0_0_ENABLED"
          ["label"]=>
          string(29) "Enable 2-installment payments"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(21) "general_2_0_0-content"
        }
        [8]=>
        array(8) {
          ["name"]=>
          string(29) "ALMA_general_2_0_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_2_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [9]=>
        array(8) {
          ["name"]=>
          string(29) "ALMA_general_2_0_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_2_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [10]=>
        array(6) {
          ["name"]=>
          string(29) "ALMA_general_2_0_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_2_0_0-content"
        }
        [11]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(21) "general_3_0_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(604) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>
<p>
    <b>
            You can offer 3-installment payments for amounts between 50€ and 3000€.
        </b>
</p>
<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        3%
            <br>
        <b>Customers pay:</b>
        1.2%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 3-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [12]=>
        array(6) {
          ["name"]=>
          string(26) "ALMA_general_3_0_0_ENABLED"
          ["label"]=>
          string(29) "Enable 3-installment payments"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(21) "general_3_0_0-content"
        }
        [13]=>
        array(8) {
          ["name"]=>
          string(29) "ALMA_general_3_0_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_3_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [14]=>
        array(8) {
          ["name"]=>
          string(29) "ALMA_general_3_0_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_3_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [15]=>
        array(6) {
          ["name"]=>
          string(29) "ALMA_general_3_0_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_3_0_0-content"
        }
        [16]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(21) "general_4_0_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(604) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>
<p>
    <b>
            You can offer 4-installment payments for amounts between 50€ and 3000€.
        </b>
</p>
<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        3%
            <br>
        <b>Customers pay:</b>
        1.2%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 4-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [17]=>
        array(6) {
          ["name"]=>
          string(26) "ALMA_general_4_0_0_ENABLED"
          ["label"]=>
          string(29) "Enable 4-installment payments"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(21) "general_4_0_0-content"
        }
        [18]=>
        array(8) {
          ["name"]=>
          string(29) "ALMA_general_4_0_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_4_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [19]=>
        array(8) {
          ["name"]=>
          string(29) "ALMA_general_4_0_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_4_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [20]=>
        array(6) {
          ["name"]=>
          string(29) "ALMA_general_4_0_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(21) "general_4_0_0-content"
        }
        [21]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(22) "general_10_0_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(609) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>
<p>
    <b>
            You can offer 10-installment payments for amounts between 50€ and 3000€.
        </b>
</p>
<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        3.8%
            <br>
        <b>Customers pay:</b>
        6.31%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 10-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [22]=>
        array(6) {
          ["name"]=>
          string(27) "ALMA_general_10_0_0_ENABLED"
          ["label"]=>
          string(30) "Enable 10-installment payments"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(22) "general_10_0_0-content"
        }
        [23]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_10_0_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_10_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [24]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_10_0_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_10_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [25]=>
        array(6) {
          ["name"]=>
          string(30) "ALMA_general_10_0_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_10_0_0-content"
        }
        [26]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(22) "general_12_0_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(609) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>
<p>
    <b>
            You can offer 12-installment payments for amounts between 50€ and 3000€.
        </b>
</p>
<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        3.8%
            <br>
        <b>Customers pay:</b>
        0.45%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 12-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [27]=>
        array(6) {
          ["name"]=>
          string(27) "ALMA_general_12_0_0_ENABLED"
          ["label"]=>
          string(30) "Enable 12-installment payments"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(22) "general_12_0_0-content"
        }
        [28]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_12_0_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_12_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [29]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_12_0_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_12_0_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [30]=>
        array(6) {
          ["name"]=>
          string(30) "ALMA_general_12_0_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_12_0_0-content"
        }
        [31]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(22) "general_1_15_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(541) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>
<p>
    <b>
            You can offer deferred payments for amounts between 50€ and 3000€.
        </b>
</p>
<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        4.4%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 1-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [32]=>
        array(6) {
          ["name"]=>
          string(27) "ALMA_general_1_15_0_ENABLED"
          ["label"]=>
          string(33) "Enable deferred payments +15 days"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(22) "general_1_15_0-content"
        }
        [33]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_1_15_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_1_15_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [34]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_1_15_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_1_15_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [35]=>
        array(6) {
          ["name"]=>
          string(30) "ALMA_general_1_15_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_1_15_0-content"
        }
        [36]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(22) "general_1_30_0-content"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(539) "
<style type="text/css">
    p.alma-fee-plan-details {
        line-height: 1.5em;
    }
</style>
<p>
    <b>
            You can offer deferred payments for amounts between 50€ and 3000€.
        </b>
</p>
<p class="alma-fee-plan-details">
    Fees applied to each transaction for this plan:
    <br>
            <b>You pay:</b>
        5%
    <br><br>
    <a href='mailto:contact@getalma.eu?subject=Fees for 1-installment plan'>Contact us</a>
    if you think your sales volumes warrant better rates!
</p>
"
        }
        [37]=>
        array(6) {
          ["name"]=>
          string(27) "ALMA_general_1_30_0_ENABLED"
          ["label"]=>
          string(33) "Enable deferred payments +30 days"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["form_group_class"]=>
          string(22) "general_1_30_0-content"
        }
        [38]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_1_30_0_MIN_AMOUNT"
          ["label"]=>
          string(20) "Minimum amount (€)"
          ["desc"]=>
          string(45) "Minimum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_1_30_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [39]=>
        array(8) {
          ["name"]=>
          string(30) "ALMA_general_1_30_0_MAX_AMOUNT"
          ["label"]=>
          string(20) "Maximum amount (€)"
          ["desc"]=>
          string(45) "Maximum purchase amount to activate this plan"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_1_30_0-content"
          ["min"]=>
          int(50)
          ["max"]=>
          int(3000)
        }
        [40]=>
        array(6) {
          ["name"]=>
          string(30) "ALMA_general_1_30_0_SORT_ORDER"
          ["label"]=>
          string(8) "Position"
          ["desc"]=>
          string(57) "Use relative values to set the order on the checkout page"
          ["type"]=>
          string(6) "number"
          ["readonly"]=>
          bool(false)
          ["form_group_class"]=>
          string(22) "general_1_30_0-content"
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [1]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(30) "Display widget on product page"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(10) {
        [0]=>
        array(7) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(354) "This widget allows you to inform your customers of the availability of Alma's payment facilities right from the product page, which will help to increase your conversion rate. For more details on its configuration or in case of problems, please consult <a href="https://docs.getalma.eu/docs/prestashop-alma-widget" target="_blank">this documentation</a>."
          ["desc"]=>
          string(593) "<div class="row alma-sample-widget">
    <div class="col-lg-6">
        <img src="/modules/alma/views/img/widget-available.png" width="400" class="img-responsive" alt="Sample widget Alma" />
        <p class="help-block">Widget when Alma payments are available for this product.</p>
    </div>
    <div class="col-lg-6">
        <img src="/modules/alma/views/img/widget-unavailable.png" width="400" class="img-responsive" alt="Sample widget Alma" />
        <p class="help-block">Widget when Alma payments are not available for this product.</p>
    </div>
</div>
<hr class="alma--spacer" />
"
        }
        [1]=>
        array(5) {
          ["name"]=>
          string(29) "ALMA_SHOW_PRODUCT_ELIGIBILITY"
          ["label"]=>
          string(14) "Display widget"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
        }
        [2]=>
        array(5) {
          ["name"]=>
          string(27) "ALMA_PRODUCT_WDGT_NOT_ELGBL"
          ["label"]=>
          string(43) "Display even if the product is not eligible"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
        }
        [3]=>
        array(6) {
          ["name"]=>
          string(27) "ALMA_WIDGET_POSITION_CUSTOM"
          ["type"]=>
          string(5) "radio"
          ["label"]=>
          string(15) "Widget position"
          ["class"]=>
          string(1) "t"
          ["required"]=>
          bool(true)
          ["values"]=>
          array(2) {
            [0]=>
            array(3) {
              ["id"]=>
              string(31) "ALMA_WIDGET_POSITION_CUSTOM_OFF"
              ["value"]=>
              bool(false)
              ["label"]=>
              string(39) "Display widget after price (by default)"
            }
            [1]=>
            array(3) {
              ["id"]=>
              string(30) "ALMA_WIDGET_POSITION_CUSTOM_ON"
              ["value"]=>
              bool(true)
              ["label"]=>
              string(37) "Display widget on custom css selector"
            }
          }
        }
        [4]=>
        array(7) {
          ["name"]=>
          string(29) "ALMA_WIDGET_POSITION_SELECTOR"
          ["label"]=>
          string(37) "Display widget on custom css selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(95) "<b>Advanced</b> [Optional] Query selector for our scripts to display the widget on product page"
          ["placeholder"]=>
          string(21) "E.g. #id, .class, ..."
        }
        [5]=>
        array(6) {
          ["name"]=>
          string(27) "ALMA_PRODUCT_PRICE_SELECTOR"
          ["label"]=>
          string(28) "Product price query selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(97) "<b>Advanced</b> Query selector for our scripts to correctly find the displayed price of a product"
        }
        [6]=>
        array(6) {
          ["name"]=>
          string(26) "ALMA_PRODUCT_ATTR_SELECTOR"
          ["label"]=>
          string(41) "Product attribute dropdown query selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(113) "<b>Advanced</b> Query selector for our scripts to correctly find the selected attributes of a product combination"
        }
        [7]=>
        array(6) {
          ["name"]=>
          string(32) "ALMA_PRODUCT_ATTR_RADIO_SELECTOR"
          ["label"]=>
          string(45) "Product attribute radio button query selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(113) "<b>Advanced</b> Query selector for our scripts to correctly find the selected attributes of a product combination"
        }
        [8]=>
        array(6) {
          ["name"]=>
          string(32) "ALMA_PRODUCT_COLOR_PICK_SELECTOR"
          ["label"]=>
          string(35) "Product color picker query selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(101) "<b>Advanced</b> Query selector for our scripts to correctly find the chosen color option of a product"
        }
        [9]=>
        array(6) {
          ["name"]=>
          string(30) "ALMA_PRODUCT_QUANTITY_SELECTOR"
          ["label"]=>
          string(31) "Product quantity query selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(97) "<b>Advanced</b> Query selector for our scripts to correctly find the wanted quantity of a product"
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [2]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(27) "Display widget on cart page"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(5) {
        [0]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(354) "This widget allows you to inform your customers of the availability of Alma's payment facilities right from the product page, which will help to increase your conversion rate. For more details on its configuration or in case of problems, please consult <a href="https://docs.getalma.eu/docs/prestashop-alma-widget" target="_blank">this documentation</a>."
        }
        [1]=>
        array(5) {
          ["name"]=>
          string(29) "ALMA_SHOW_ELIGIBILITY_MESSAGE"
          ["label"]=>
          string(14) "Display widget"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
        }
        [2]=>
        array(5) {
          ["name"]=>
          string(24) "ALMA_CART_WDGT_NOT_ELGBL"
          ["label"]=>
          string(40) "Display even if the cart is not eligible"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
        }
        [3]=>
        array(6) {
          ["name"]=>
          string(32) "ALMA_CART_WIDGET_POSITION_CUSTOM"
          ["type"]=>
          string(5) "radio"
          ["label"]=>
          string(15) "Widget position"
          ["class"]=>
          string(1) "t"
          ["required"]=>
          bool(true)
          ["values"]=>
          array(2) {
            [0]=>
            array(3) {
              ["id"]=>
              string(36) "ALMA_CART_WIDGET_POSITION_CUSTOM_OFF"
              ["value"]=>
              bool(false)
              ["label"]=>
              string(38) "Display widget after cart (by default)"
            }
            [1]=>
            array(3) {
              ["id"]=>
              string(35) "ALMA_CART_WIDGET_POSITION_CUSTOM_ON"
              ["value"]=>
              bool(true)
              ["label"]=>
              string(37) "Display widget on custom css selector"
            }
          }
        }
        [4]=>
        array(7) {
          ["name"]=>
          string(27) "ALMA_CART_WDGT_POS_SELECTOR"
          ["label"]=>
          string(37) "Display widget on custom css selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(92) "<b>Advanced</b> [Optional] Query selector for our scripts to display the widget on cart page"
          ["placeholder"]=>
          string(21) "E.g. #id, .class, ..."
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [3]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(28) "Payment method configuration"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(13) {
        [0]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(464) "<p>Edit the text displayed when choosing the payment method in your checkout. It will adapt to the languages of your site.</p>
<div class="row alma-sample-payment-button">
    <div class="col-lg-6">
        <img src="/modules/alma/views/img/payment-button-1.7.png" class="img-responsive alma--border" width="485" alt="Sample payment button Alma" />
        <p class="help-block">Example of title and description.</p>
    </div>
</div>

<hr class="alma--spacer" />
"
        }
        [1]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(16) "<h2>Pay now</h2>"
        }
        [2]=>
        array(6) {
          ["name"]=>
          string(25) "ALMA_PAY_NOW_BUTTON_TITLE"
          ["label"]=>
          string(5) "Title"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [3]=>
        array(6) {
          ["name"]=>
          string(24) "ALMA_PAY_NOW_BUTTON_DESC"
          ["label"]=>
          string(11) "Description"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [4]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(44) "<h2>Payments in 2, 3 and 4 installments</h2>"
        }
        [5]=>
        array(6) {
          ["name"]=>
          string(21) "ALMA_PNX_BUTTON_TITLE"
          ["label"]=>
          string(5) "Title"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [6]=>
        array(6) {
          ["name"]=>
          string(20) "ALMA_PNX_BUTTON_DESC"
          ["label"]=>
          string(11) "Description"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [7]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(26) "<h2>Deferred payments</h2>"
        }
        [8]=>
        array(6) {
          ["name"]=>
          string(26) "ALMA_DEFERRED_BUTTON_TITLE"
          ["label"]=>
          string(5) "Title"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [9]=>
        array(6) {
          ["name"]=>
          string(25) "ALMA_DEFERRED_BUTTON_DESC"
          ["label"]=>
          string(11) "Description"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [10]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(45) "<h2>Payments in more than 4 installments</h2>"
        }
        [11]=>
        array(6) {
          ["name"]=>
          string(25) "ALMA_PNX_AIR_BUTTON_TITLE"
          ["label"]=>
          string(5) "Title"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
        [12]=>
        array(6) {
          ["name"]=>
          string(24) "ALMA_PNX_AIR_BUTTON_DESC"
          ["label"]=>
          string(11) "Description"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(true)
          ["lang"]=>
          bool(true)
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [4]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(19) "Excluded categories"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(3) {
        [0]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["desc"]=>
          string(830) "<div id="alma-excluded">
    <p>
                    Some products (gift cards, license keys, software, weapons, ...) cannot be sold with Alma, as per <a href="https://getalma.eu/legal/terms/payment" target="_blank">our terms</a> (see Exclusions paragraph).
    </p>
    <p>If you are selling such products on your shop, you need to configure Alma so that it is not enabled when customers view or shop them.</p>
    <p style="margin: 20px 0;">
                    Use the <strong><a href='http://prestashop-a-1-7-8-9.local.test/almin/index.php?controller=AdminAlmaCategories&token=58ccda3b78912661bc4bfe1b09cef580'>category exclusions page</a></strong> to comply with these restrictions.
    </p>
    <p>
        <strong>Categories currently excluded : </strong>
        No excluded categories
    </p>
</div>
"
        }
        [1]=>
        array(6) {
          ["name"]=>
          string(30) "ALMA_CATEGORIES_WDGT_NOT_ELGBL"
          ["label"]=>
          string(15) "Display message"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(52) "Display the message below if the product is excluded"
              }
            }
          }
          ["desc"]=>
          string(70) "Display the message below if the product is excluded from the category"
        }
        [2]=>
        array(7) {
          ["name"]=>
          string(28) "ALMA_NOT_ELIGIBLE_CATEGORIES"
          ["label"]=>
          string(43) "Excluded categories non-eligibility message"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(101) "Message displayed on an excluded product page or on the cart page if it contains an excluded product."
          ["lang"]=>
          bool(true)
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [5]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(24) "Refund with state change"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(4) {
        [0]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(147) "If you usually refund orders by changing their state, activate this option and choose the state you want to use to trigger refunds on Alma payments"
        }
        [1]=>
        array(6) {
          ["name"]=>
          NULL
          ["label"]=>
          bool(false)
          ["type"]=>
          string(4) "html"
          ["form_group_class"]=>
          string(10) "input_html"
          ["col"]=>
          int(12)
          ["html_content"]=>
          string(238) "With Alma, you can make your refunds directly from your PrestaShop back-office. Go to your order to find the new Alma section. <a href="https://docs.getalma.eu/docs/prestashop-refund" target="_blank">More information on how to use it.</a>"
        }
        [2]=>
        array(5) {
          ["name"]=>
          string(25) "ALMA_STATE_REFUND_ENABLED"
          ["label"]=>
          string(31) "Activate refund by change state"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
        }
        [3]=>
        array(6) {
          ["name"]=>
          string(17) "ALMA_STATE_REFUND"
          ["label"]=>
          string(18) "Refund state order"
          ["desc"]=>
          string(41) "Your order state to sync refund with Alma"
          ["type"]=>
          string(6) "select"
          ["required"]=>
          bool(true)
          ["options"]=>
          array(3) {
            ["query"]=>
            array(17) {
              [0]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "17"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "0"
                ["module_name"]=>
                string(11) "ps_checkout"
                ["color"]=>
                string(7) "#3498D8"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(38) "Authorized. To be captured by merchant"
                ["template"]=>
                string(0) ""
              }
              [1]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "10"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(14) "ps_wirepayment"
                ["color"]=>
                string(7) "#34209E"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(26) "Awaiting bank wire payment"
                ["template"]=>
                string(8) "bankwire"
              }
              [2]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "13"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "0"
                ["module_name"]=>
                string(17) "ps_cashondelivery"
                ["color"]=>
                string(7) "#34209E"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(36) "Awaiting Cash On Delivery validation"
                ["template"]=>
                string(14) "cashondelivery"
              }
              [3]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "1"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(15) "ps_checkpayment"
                ["color"]=>
                string(7) "#34209E"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(22) "Awaiting check payment"
                ["template"]=>
                string(6) "cheque"
              }
              [4]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "6"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#2C3E50"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(8) "Canceled"
                ["template"]=>
                string(14) "order_canceled"
              }
              [5]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "5"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "0"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#01B887"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "1"
                ["delivery"]=>
                string(1) "1"
                ["shipped"]=>
                string(1) "1"
                ["paid"]=>
                string(1) "1"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(9) "Delivered"
                ["template"]=>
                string(0) ""
              }
              [6]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "12"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#34209E"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(23) "On backorder (not paid)"
                ["template"]=>
                string(10) "outofstock"
              }
              [7]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "9"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#3498D8"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "1"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(19) "On backorder (paid)"
                ["template"]=>
                string(10) "outofstock"
              }
              [8]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "16"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "0"
                ["module_name"]=>
                string(11) "ps_checkout"
                ["color"]=>
                string(7) "#3498D8"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(15) "Partial payment"
                ["template"]=>
                string(0) ""
              }
              [9]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "15"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "0"
                ["module_name"]=>
                string(11) "ps_checkout"
                ["color"]=>
                string(7) "#01B887"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(14) "Partial refund"
                ["template"]=>
                string(0) ""
              }
              [10]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "2"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#3498D8"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "1"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "1"
                ["pdf_invoice"]=>
                string(1) "1"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(16) "Payment accepted"
                ["template"]=>
                string(7) "payment"
              }
              [11]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "8"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#E74C3C"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(13) "Payment error"
                ["template"]=>
                string(13) "payment_error"
              }
              [12]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "3"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#3498D8"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "1"
                ["delivery"]=>
                string(1) "1"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "1"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(22) "Processing in progress"
                ["template"]=>
                string(11) "preparation"
              }
              [13]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "7"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#01B887"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(8) "Refunded"
                ["template"]=>
                string(6) "refund"
              }
              [14]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "11"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#3498D8"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "1"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "1"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(23) "Remote payment accepted"
                ["template"]=>
                string(7) "payment"
              }
              [15]=>
              array(17) {
                ["id_order_state"]=>
                string(1) "4"
                ["invoice"]=>
                string(1) "1"
                ["send_email"]=>
                string(1) "1"
                ["module_name"]=>
                string(0) ""
                ["color"]=>
                string(7) "#01B887"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "1"
                ["delivery"]=>
                string(1) "1"
                ["shipped"]=>
                string(1) "1"
                ["paid"]=>
                string(1) "1"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(7) "Shipped"
                ["template"]=>
                string(7) "shipped"
              }
              [16]=>
              array(17) {
                ["id_order_state"]=>
                string(2) "14"
                ["invoice"]=>
                string(1) "0"
                ["send_email"]=>
                string(1) "0"
                ["module_name"]=>
                string(11) "ps_checkout"
                ["color"]=>
                string(7) "#34209E"
                ["unremovable"]=>
                string(1) "1"
                ["hidden"]=>
                string(1) "0"
                ["logable"]=>
                string(1) "0"
                ["delivery"]=>
                string(1) "0"
                ["shipped"]=>
                string(1) "0"
                ["paid"]=>
                string(1) "0"
                ["pdf_invoice"]=>
                string(1) "0"
                ["pdf_delivery"]=>
                string(1) "0"
                ["deleted"]=>
                string(1) "0"
                ["id_lang"]=>
                string(1) "1"
                ["name"]=>
                string(19) "Waiting for payment"
                ["template"]=>
                string(0) ""
              }
            }
            ["id"]=>
            string(14) "id_order_state"
            ["name"]=>
            string(4) "name"
          }
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [6]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(16) "In-page checkout"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(3) {
        [0]=>
        array(6) {
          ["name"]=>
          string(20) "ALMA_ACTIVATE_INPAGE"
          ["label"]=>
          string(25) "Activate in-page checkout"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
          ["desc"]=>
          string(170) "Let your customers pay with Alma in a secure pop-up, without leaving your site. <a href="https://docs.almapay.com/docs/in-page-prestashop" target="_blank">Learn more.</a>"
        }
        [1]=>
        array(7) {
          ["name"]=>
          string(35) "ALMA_INPAGE_PAYMENT_BUTTON_SELECTOR"
          ["label"]=>
          string(34) "Input payment button Alma selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(95) "<b>Advanced</b> [Optional] CSS selector used by our scripts to identify the Alma payment button"
          ["placeholder"]=>
          string(21) "E.g. #id, .class, ..."
        }
        [2]=>
        array(7) {
          ["name"]=>
          string(39) "ALMA_INPAGE_PLACE_ORDER_BUTTON_SELECTOR"
          ["label"]=>
          string(27) "Place order button selector"
          ["type"]=>
          string(4) "text"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(103) "<b>Advanced</b> [Optional] CSS selector used by our scripts to identify the payment confirmation button"
          ["placeholder"]=>
          string(21) "E.g. #id, .class, ..."
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [7]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(17) "API configuration"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(3) {
        [0]=>
        array(6) {
          ["name"]=>
          string(13) "ALMA_API_MODE"
          ["label"]=>
          string(8) "API Mode"
          ["desc"]=>
          string(127) "Use Test mode until you are ready to take real orders with Alma. In Test mode, only admins can see Alma on cart/checkout pages."
          ["type"]=>
          string(6) "select"
          ["required"]=>
          bool(true)
          ["options"]=>
          array(3) {
            ["query"]=>
            array(2) {
              [0]=>
              array(2) {
                ["api_mode"]=>
                string(4) "live"
                ["name"]=>
                string(4) "Live"
              }
              [1]=>
              array(2) {
                ["api_mode"]=>
                string(4) "test"
                ["name"]=>
                string(4) "Test"
              }
            }
            ["id"]=>
            string(8) "api_mode"
            ["name"]=>
            string(4) "name"
          }
        }
        [1]=>
        array(7) {
          ["name"]=>
          string(17) "ALMA_LIVE_API_KEY"
          ["label"]=>
          string(12) "Live API key"
          ["type"]=>
          string(6) "secret"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(147) "Not required for Test mode – You can find your Live API key on <a href="https://dashboard.getalma.eu/api" target="_blank">your Alma dashboard</a>"
          ["placeholder"]=>
          string(32) "********************************"
        }
        [2]=>
        array(7) {
          ["name"]=>
          string(17) "ALMA_TEST_API_KEY"
          ["label"]=>
          string(12) "Test API key"
          ["type"]=>
          string(6) "secret"
          ["size"]=>
          int(75)
          ["required"]=>
          bool(false)
          ["desc"]=>
          string(158) "Not required for Live mode – You can find your Test API key on <a href="https://dashboard.sandbox.getalma.eu/api" target="_blank">your sandbox dashboard</a>"
          ["placeholder"]=>
          string(32) "********************************"
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
  [8]=>
  array(1) {
    ["form"]=>
    array(3) {
      ["legend"]=>
      array(2) {
        ["title"]=>
        string(13) "Debug options"
        ["image"]=>
        string(43) "/modules/alma/views/img/logos/alma_tiny.svg"
      }
      ["input"]=>
      array(1) {
        [0]=>
        array(5) {
          ["name"]=>
          string(21) "ALMA_ACTIVATE_LOGGING"
          ["label"]=>
          string(16) "Activate logging"
          ["type"]=>
          string(11) "alma_switch"
          ["readonly"]=>
          bool(false)
          ["values"]=>
          array(3) {
            ["id"]=>
            string(2) "id"
            ["name"]=>
            string(5) "label"
            ["query"]=>
            array(1) {
              [0]=>
              array(3) {
                ["id"]=>
                string(2) "ON"
                ["val"]=>
                bool(true)
                ["label"]=>
                string(0) ""
              }
            }
          }
        }
      }
      ["submit"]=>
      array(2) {
        ["title"]=>
        string(4) "Save"
        ["class"]=>
        string(33) "button btn btn-default pull-right"
      }
    }
  }
}

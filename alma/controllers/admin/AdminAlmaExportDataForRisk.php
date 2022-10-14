<?php
/**
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
 */

class AdminAlmaExportDataForRiskController extends ModuleAdminController
{
    public function __construct()
    {
        $this->name = 'dataforriskexport';
        $this->version = '1.0.0';
        $this->bootstrap = true;
        $this->className = 'AdminAlmaExportDataForRiskController';
        parent::__construct();
        $this->_html = '';
    }

    public function initContent()
    {
        parent::initContent();
        if (Tools::getValue('action') == 'ExportDataForRisk') {
            $this->actionExportDataForRisk();
            die;
        }

        $link = $this->context->link->getAdminLink('AdminAlmaExportDataForRisk') . '&action=ExportDataForRisk';
        $this->context->smarty->assign('linkExportDataForRisk', $link);
        $this->setTemplate('dataforriskexport.tpl');
    }

    public function actionExportDataForRisk()
    {
        $array = Db::getInstance()->executeS($this->sql(), $array = true, $use_cache = 1);
        $this->downloadSendHeaders('data_export_' . date('Y-m-d') . '.csv');

        return $this->exportCsv($array);
    }

    private function exportCsv(array $array)
    {
        if (empty($array)) {
            return null;
        }
        ob_start();
        $df = fopen('php://output', 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
    }

    private function downloadSendHeaders($filename)
    {
        // disable caching
        $now = gmdate('D, d M Y H:i:s');
        header('Expires: Tue, 03 Jul 2099 06:00:00 GMT');
        header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
        header("Last-Modified: {$now} GMT");

        // force download
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header('Content-Type: text/x-csv');

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header('Content-Transfer-Encoding: binary');
    }

    private function sql()
    {
        return "SELECT * FROM (WITH tmp_customer(id_customer, date_add, is_guest, alma_payment_count) AS (
            SELECT
                cust.id_customer,
                cust.date_add,
                cust.is_guest,
                count(op.transaction_id) as alma_payment_count
            FROM
                ps_customer                 AS cust
                JOIN ps_orders              AS o      ON o.id_customer = cust.id_customer AND o.payment like 'Alma%'
                LEFT JOIN ps_order_payment  AS op     ON op.order_reference = o.reference
            GROUP BY
                cust.id_customer,
                cust.date_add,
                cust.is_guest
        )
        SELECT
            c.date_add as customer_created_at,
            o.date_add as order_created_at,
            c.is_guest,
            o.reference as order_reference,
            o.total_paid_tax_incl as purchase_amount,
            o.payment as payment_method,
            osl.name as current_state,
            op.transaction_id as alma_payment_id,
            cur.iso_code as currency,
            car.name as shipping_method,
            p.reference as item_sku,
            m.name as item_vendor,
            od.product_name as item_title,
            od.product_quantity as item_quantity,
            od.product_price as item_unit_price,
            od.total_price_tax_incl as item_line_price,
            basket.gift as basket_is_gift,
            GROUP_CONCAT(catlan.name) as item_categories,
            p.is_virtual as item_is_virtual
        FROM
            tmp_customer                AS c
            JOIN ps_orders              AS o      ON o.id_customer = c.id_customer
            LEFT JOIN ps_order_payment  AS op     ON op.order_reference = o.reference
            JOIN ps_currency            AS cur    ON cur.id_currency = o.id_currency
            JOIN ps_carrier             AS car    ON car.id_carrier = o.id_carrier
            JOIN ps_order_detail        AS od     ON od.id_order = o.id_order
            JOIN ps_product             AS p      ON p.id_product = od.product_id
            JOIN ps_manufacturer        AS m      ON m.id_manufacturer = p.id_manufacturer
            JOIN ps_cart                AS basket ON basket.id_cart = o.id_cart
            JOIN ps_category_product    AS catpro ON catpro.id_product = p.id_product
            JOIN ps_category_lang       AS catlan ON catlan.id_category = catpro.id_category
            JOIN ps_order_state_lang    AS osl    ON osl.id_order_state = o.current_state
        WHERE catlan.id_lang = 1
        AND osl.id_lang = 1
        AND c.alma_payment_count >= 1
        GROUP BY
            c.date_add,
            o.date_add,
            c.is_guest,
            o.reference,
            o.total_paid_tax_incl,
            o.payment,
            osl.name,
            op.transaction_id,
            cur.iso_code,
            car.name,
            p.reference,
            m.name,
            od.product_name,
            od.product_quantity,
            od.product_price,
            od.total_price_tax_incl,
            basket.gift,
            p.is_virtual
        ) AS dt;";
    }
}

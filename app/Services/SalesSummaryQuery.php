<?php

namespace App\Services;

class SalesSummaryQuery
{
    public static function paymentJoin(): string
    {
        return "LEFT JOIN (
            SELECT
                transaction_id,
                branch_id,
                pos_machine_id,
                SUM(CASE WHEN payment_type_name = 'cash' THEN amount ELSE 0 END) AS cash_sales,
                SUM(CASE WHEN payment_type_name = 'card' THEN amount ELSE 0 END) AS card_sales,
                SUM(CASE WHEN payment_type_name = 'mobile' THEN amount ELSE 0 END) AS mobile_sales,
                SUM(CASE WHEN payment_type_name = 'ar' THEN amount ELSE 0 END) AS ar_sales,
                SUM(CASE WHEN payment_type_name = 'online' THEN amount ELSE 0 END) AS online_sales
            FROM transactional_db.payments
            GROUP BY transaction_id, branch_id, pos_machine_id
        ) p ON p.transaction_id = t.transaction_id
            AND p.branch_id = t.branch_id
            AND p.pos_machine_id = t.pos_machine_id";
    }

    public static function paymentColumns(): string
    {
        return "SUM(COALESCE(p.cash_sales, 0)) as cash_sales,
                SUM(COALESCE(p.card_sales, 0)) as card_sales,
                SUM(COALESCE(p.mobile_sales, 0)) as mobile_sales,
                SUM(COALESCE(p.ar_sales, 0)) as ar_sales,
                SUM(COALESCE(p.online_sales, 0)) as online_sales";
    }
}

<?php

namespace Tests\Unit;

use App\Services\Gl\PosSalesAssembler;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PosSalesAssemblerTest extends TestCase
{
    private function accounts(): array
    {
        return [
            'sales_vatable' => 1,
            'sales_vat_exempt' => 2,
            'output_vat' => 3,
            'sales_discount' => 4,
            'accounts_receivable' => 5,
            'payment_types' => [10 => 6],
        ];
    }

    private function line(array $lines, int $accountId): array
    {
        foreach ($lines as $line) {
            if ($line['account_id'] === $accountId) {
                return $line;
            }
        }

        $this->fail('Missing account '.$accountId);
    }

    public function test_cash_sale_balances_sales_and_vat(): void
    {
        $lines = (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 100,
            'is_account_receivable_redeem' => false,
            'is_account_receivable' => false,
            'vatable_sales' => 89.2857,
            'vat_exempt_sales' => 0,
            'vat_amount' => 10.7143,
            'discount_amount' => 0,
            'change' => 0,
        ]], [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 100,
            'payment_type_id' => 10,
            'amount' => 100,
            'is_void' => false,
            'is_account_receivable' => false,
        ]], $this->accounts());

        $this->assertEqualsWithDelta(100, $this->line($lines, 6)['debit'], 0.0001);
        $this->assertEqualsWithDelta(89.2857, $this->line($lines, 1)['credit'], 0.0001);
        $this->assertEqualsWithDelta(10.7143, $this->line($lines, 3)['credit'], 0.0001);
        $this->assertBalanced($lines);
    }

    public function test_discount_is_debited_against_gross_sales(): void
    {
        $lines = (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 101,
            'is_account_receivable_redeem' => false,
            'is_account_receivable' => false,
            'vatable_sales' => 100,
            'vat_amount' => 12,
            'discount_amount' => 10,
            'change' => 0,
        ]], [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 101,
            'payment_type_id' => 10,
            'amount' => 102,
        ]], $this->accounts());

        $this->assertEqualsWithDelta(10, $this->line($lines, 4)['debit'], 0.0001);
        $this->assertEqualsWithDelta(102, $this->line($lines, 6)['debit'], 0.0001);
        $this->assertBalanced($lines);
    }

    public function test_charge_sale_debits_receivable_when_no_payment_line_exists(): void
    {
        $lines = (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 102,
            'is_account_receivable_redeem' => false,
            'is_account_receivable' => true,
            'vatable_sales' => 100,
            'vat_amount' => 12,
            'discount_amount' => 0,
            'change' => 0,
        ]], [], $this->accounts());

        $this->assertEqualsWithDelta(112, $this->line($lines, 5)['debit'], 0.0001);
        $this->assertBalanced($lines);
    }

    public function test_collection_credits_receivable_and_does_not_credit_sales(): void
    {
        $lines = (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 103,
            'is_account_receivable_redeem' => true,
            'vatable_sales' => 500,
            'vat_amount' => 60,
            'change' => 0,
        ]], [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 103,
            'payment_type_id' => 10,
            'amount' => 50,
        ]], $this->accounts());

        $this->assertEqualsWithDelta(50, $this->line($lines, 6)['debit'], 0.0001);
        $this->assertEqualsWithDelta(50, $this->line($lines, 5)['credit'], 0.0001);
        $this->assertCount(2, $lines);
        $this->assertBalanced($lines);
    }

    public function test_change_is_removed_from_the_tender(): void
    {
        $lines = (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 104,
            'is_account_receivable_redeem' => false,
            'vatable_sales' => 100,
            'change' => 20,
        ]], [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 104,
            'payment_type_id' => 10,
            'amount' => 120,
        ]], $this->accounts());

        $this->assertEqualsWithDelta(100, $this->line($lines, 6)['debit'], 0.0001);
        $this->assertBalanced($lines);
    }

    public function test_difference_is_absorbed_by_sales_discount(): void
    {
        $lines = (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 106,
            'is_account_receivable_redeem' => false,
            'vatable_sales' => 100,
            'change' => 0,
        ]], [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 106,
            'payment_type_id' => 10,
            'amount' => 90,
        ]], $this->accounts());

        $this->assertEqualsWithDelta(10, $this->line($lines, 4)['debit'], 0.0001);
        $this->assertBalanced($lines);
    }

    public function test_unmapped_payment_type_is_rejected(): void
    {
        $this->expectException(ValidationException::class);

        (new PosSalesAssembler())->build(7, [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 105,
            'is_account_receivable_redeem' => false,
            'vatable_sales' => 10,
        ]], [[
            'branch_id' => 7,
            'pos_machine_id' => 1,
            'transaction_id' => 105,
            'payment_type_id' => 99,
            'payment_type_name' => 'GCash',
            'amount' => 10,
        ]], $this->accounts());
    }

    private function assertBalanced(array $lines): void
    {
        $debit = round(collect($lines)->sum('debit'), 4);
        $credit = round(collect($lines)->sum('credit'), 4);
        $this->assertEqualsWithDelta($debit, $credit, 0.0001);
    }
}

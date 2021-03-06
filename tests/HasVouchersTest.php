<?php

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Tests\Models\Color;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use FrittenKeeZ\Vouchers\Vouchers;

class HasVouchersTest extends TestCase
{
    /**
     * Test HasVouchers::createVoucher().
     *
     * @return void
     */
    public function testCreateVoucher(): void
    {
        $user = $this->factory(User::class)->create();
        $voucher = $user->createVoucher();

        // Check user voucher relation.
        $this->assertTrue($voucher->is($user->vouchers->first()));
        $this->assertTrue($voucher->is($user->voucherEntities->first()->voucher));
    }

    /**
     * Test HasVouchers::createVoucher() with callback.
     *
     * @return void
     */
    public function testCreateVoucherWithCallback(): void
    {
        $user = $this->factory(User::class)->create();
        $color = $this->factory(Color::class)->create();
        $voucher = $user->createVoucher(function (Vouchers $vouchers) use ($color) {
            $vouchers->withEntities($color);
        });

        // Check user voucher relation.
        $this->assertTrue($voucher->is($user->vouchers->first()));
        $this->assertTrue($voucher->is($user->voucherEntities->first()->voucher));
        $this->assertTrue($color->is($voucher->getEntities(Color::class)->first()));
    }

    /**
     * Test HasVouchers::createVouchers().
     *
     * @return void
     */
    public function testCreateVouchers(): void
    {
        $user = $this->factory(User::class)->create();
        $vouchers = $user->createVouchers(3);

        foreach ($vouchers as $index => $voucher) {
            // Check user voucher relation.
            $this->assertTrue($voucher->is($user->vouchers[$index]));
            $this->assertTrue($voucher->is($user->voucherEntities[$index]->voucher));
        }
    }

    /**
     * Test HasVouchers::createVouchers() with callback.
     *
     * @return void
     */
    public function testCreateVouchersWithCallback(): void
    {
        $user = $this->factory(User::class)->create();
        $color = $this->factory(Color::class)->create();
        $vouchers = $user->createVouchers(3, function (Vouchers $vouchers) use ($color) {
            $vouchers->withEntities($color);
        });

        foreach ($vouchers as $index => $voucher) {
            // Check user voucher relation.
            $this->assertTrue($voucher->is($user->vouchers[$index]));
            $this->assertTrue($voucher->is($user->voucherEntities[$index]->voucher));
            $this->assertTrue($color->is($voucher->getEntities(Color::class)->first()));
        }
    }
}

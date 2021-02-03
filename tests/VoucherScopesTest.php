<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers\Tests;

use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;
use FrittenKeeZ\Vouchers\Tests\Models\User;
use Illuminate\Support\Carbon;

class VoucherScopesTest extends TestCase
{
    /**
     * Test Voucher::scopeCode().
     *
     * @return void
     */
    public function testCodeScope(): void
    {
        $voucher = Vouchers::create();

        $this->assertTrue(Voucher::code($voucher->code)->exists());
        $this->assertFalse(Voucher::code('NOPE')->exists());
    }

    /**
     * Test Voucher::scopeHasPrefix() and Voucher::scopeHasSuffix().
     *
     * @return void
     */
    public function testPrefixAndSuffixScopes(): void
    {
        Voucher::create(['code' => 'FOOTESTBAR']);
        Voucher::create(['code' => 'FOOTESTBAZ']);
        Voucher::create(['code' => 'FUUTESTBAR']);
        Voucher::create(['code' => 'FUUTESTBAZ']);
        Voucher::create(['code' => 'FOO-TEST-BAR']);
        Voucher::create(['code' => 'FOO-TEST-BAZ']);
        Voucher::create(['code' => 'FUU-TEST-BAR']);
        Voucher::create(['code' => 'FUU-TEST-BAZ']);

        // Test prefix scope with separator.
        $this->assertSame(2, Voucher::withPrefix('FOO')->count());
        $this->assertSame(2, Voucher::withPrefix('FOO', '-')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '-')->count());
        // Test prefix scope without separator
        $this->assertSame(4, Voucher::withPrefix('FOO', '')->count());
        $this->assertSame(4, Voucher::withPrefix('FUU', '')->count());

        // Test suffix scope with separator.
        $this->assertSame(2, Voucher::withSuffix('BAR')->count());
        $this->assertSame(2, Voucher::withSuffix('BAR', '-')->count());
        $this->assertSame(2, Voucher::withSuffix('BAZ')->count());
        $this->assertSame(2, Voucher::withSuffix('BAZ', '-')->count());
        // Test suffix scope without separator
        $this->assertSame(4, Voucher::withSuffix('BAR', '')->count());
        $this->assertSame(4, Voucher::withSuffix('BAZ', '')->count());

        // Test prefix and suffix scopes together with separator.
        $this->assertSame(1, Voucher::withPrefix('FOO')->withSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO', '-')->withSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU')->withSuffix('BAR')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU', '-')->withSuffix('BAR', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO')->withSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withPrefix('FOO', '-')->withSuffix('BAZ', '-')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU')->withSuffix('BAZ')->count());
        $this->assertSame(1, Voucher::withPrefix('FUU', '-')->withSuffix('BAZ', '-')->count());
        // Test prefix and suffix scopes together without separator
        $this->assertSame(2, Voucher::withPrefix('FOO', '')->withSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '')->withSuffix('BAR', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FOO', '')->withSuffix('BAZ', '')->count());
        $this->assertSame(2, Voucher::withPrefix('FUU', '')->withSuffix('BAZ', '')->count());
    }

    /**
     * Test Voucher::scopeWithStarted().
     *
     * @return void
     */
    public function testStartedScope(): void
    {
        Vouchers::create();
        Vouchers::withStartTime(Carbon::now()->subDay())->create();
        Vouchers::withStartTime(Carbon::now()->addDay())->create();

        $this->assertSame(3, Voucher::count());
        $this->assertSame(2, Voucher::withStarted(true)->count());
        $this->assertSame(1, Voucher::withStarted(false)->count());
    }

    /**
     * Test Voucher::scopeWithExpired().
     *
     * @return void
     */
    public function testExpiredScope(): void
    {
        Vouchers::create();
        Vouchers::withExpireTime(Carbon::now()->subDay())->create();
        Vouchers::withExpireTime(Carbon::now()->addDay())->create();

        $this->assertSame(3, Voucher::count());
        $this->assertSame(1, Voucher::withExpired(true)->count());
        $this->assertSame(2, Voucher::withExpired(false)->count());
    }

    /**
     * Test Voucher::scopeWithRedeemed().
     *
     * @return void
     */
    public function testRedeemedScope(): void
    {
        Vouchers::create();
        Vouchers::create()->update(['redeemed_at' => Carbon::now()->subDay()]);

        $this->assertSame(2, Voucher::count());
        $this->assertSame(1, Voucher::withRedeemed(true)->count());
        $this->assertSame(1, Voucher::withRedeemed(false)->count());
    }

    /**
     * Test Voucher::scopeWithRedeemable().
     *
     * @return void
     */
    public function testRedeemableScope(): void
    {
        Vouchers::create();
        Vouchers::withStartTime(Carbon::now()->subDay())->create();
        Vouchers::withStartTime(Carbon::now()->addDay())->create();
        Vouchers::withExpireTime(Carbon::now()->subDay())->create();
        Vouchers::withExpireTime(Carbon::now()->addDay())->create();
        Vouchers::create()->update(['redeemed_at' => Carbon::now()->subDay()]);

        $this->assertSame(6, Voucher::count());
        $this->assertSame(3, Voucher::withRedeemable(true)->count());
        $this->assertSame(3, Voucher::withRedeemable(false)->count());
    }

    /**
     * Test Voucher::scopeWithOwnerType() and Voucher::scopeWithOwner().
     *
     * @return void
     */
    public function testOwnerScopes(): void
    {
        // Create users.
        $first = $this->factory(User::class)->create();
        $second = $this->factory(User::class)->create();
        $third = $this->factory(User::class)->create();

        // Create vouchers.
        $first->createVoucher();
        $second->createVouchers(2);
        $third->createVouchers(3);

        $this->assertSame(6, Voucher::count());
        $this->assertSame(6, Voucher::withOwnerType(User::class)->count());
        $this->assertSame(1, Voucher::withOwner($first)->count());
        $this->assertSame(2, Voucher::withOwner($second)->count());
        $this->assertSame(3, Voucher::withOwner($third)->count());
    }
}

<?php

namespace Tests\Unit;

use App\Shared\Contracts\ReservationResult;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for the value object shared between the Orders module and
 * whichever inventory adapter is configured (fake or gRPC).
 */
class ReservationResultTest extends TestCase
{
    public function test_an_approved_reservation_carries_its_identifier(): void
    {
        $result = new ReservationResult(true, 'OK', [], 'order-uuid');

        $this->assertTrue($result->approved);
        $this->assertSame('OK', $result->reason);
        $this->assertSame([], $result->shortages());
        $this->assertSame('order-uuid', $result->reservationId);
        $this->assertFalse($result->isOutOfStock());
        $this->assertFalse($result->isProductMissing());
    }

    public function test_an_insufficient_stock_result_exposes_the_shortages(): void
    {
        $shortages = [
            ['product_id' => 1, 'requested' => 5, 'available' => 2],
        ];

        $result = new ReservationResult(false, 'INSUFFICIENT_STOCK', $shortages);

        $this->assertFalse($result->approved);
        $this->assertTrue($result->isOutOfStock());
        $this->assertFalse($result->isProductMissing());
        $this->assertSame($shortages, $result->shortages());
        $this->assertNull($result->reservationId);
    }

    public function test_a_product_missing_result_is_distinguishable_from_out_of_stock(): void
    {
        $result = new ReservationResult(false, 'PRODUCT_NOT_FOUND');

        $this->assertTrue($result->isProductMissing());
        $this->assertFalse($result->isOutOfStock());
        $this->assertFalse($result->approved);
    }
}

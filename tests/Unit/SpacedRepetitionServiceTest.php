<?php

namespace Tests\Unit;

use App\Services\SpacedRepetitionService;
use Tests\TestCase;

class SpacedRepetitionServiceTest extends TestCase
{
    public function test_high_confidence_correct_answer_increases_interval_and_ease(): void
    {
        $service = new SpacedRepetitionService();
        $current = (object) [
            'lapse_count' => 0,
            'consecutive_correct' => 1,
            'ease_factor' => 2.50,
        ];

        $result = $service->calculate($current, true, 5);

        $this->assertSame(7, $result['interval_days']);
        $this->assertSame('pending', $result['next_state']);
        $this->assertSame(2, $result['next_consecutive']);
        $this->assertEquals(2.65, $result['next_ease_factor']);
    }

    public function test_second_error_sets_item_as_due_and_decreases_ease(): void
    {
        $service = new SpacedRepetitionService();
        $current = (object) [
            'lapse_count' => 1,
            'consecutive_correct' => 0,
            'ease_factor' => 2.00,
        ];

        $result = $service->calculate($current, false, null);

        $this->assertSame(0, $result['interval_days']);
        $this->assertSame('due', $result['next_state']);
        $this->assertSame(2, $result['next_lapse_count']);
        $this->assertEquals(1.80, $result['next_ease_factor']);
    }
}

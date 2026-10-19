<?php

namespace Tests\Feature;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StackReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_stack_has_the_infrastructure_required_by_the_lesson(): void
    {
        $this->assertTrue(Schema::hasTable('clinics'));
        $this->assertTrue(Schema::hasTable('patients'));
        $this->assertTrue(Schema::hasTable('appointments'));
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasTable('failed_jobs'));
        $this->assertTrue(interface_exists(ShouldQueue::class));
        $this->assertTrue(class_exists(PendingRequest::class));
        $this->assertTrue(class_exists(JsonResource::class));
        $this->assertArrayHasKey('database', config('queue.connections'));
    }
}

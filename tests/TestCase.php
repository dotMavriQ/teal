<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('assertSeeVolt', fn ($component) => $this->assertSee($component));

        TestResponse::macro('assertSeeLivewire', fn ($component) => $this->assertSee($component));
    }
}

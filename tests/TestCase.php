<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Testing\TestResponse::macro('assertSeeVolt', function ($component) {
            return $this->assertSee($component);
        });

        \Illuminate\Testing\TestResponse::macro('assertSeeLivewire', function ($component) {
            return $this->assertSee($component);
        });
    }
}

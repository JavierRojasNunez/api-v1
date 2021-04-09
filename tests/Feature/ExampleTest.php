<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/confirmation');

        $response->assertStatus(200);
    }


    function it_loads_the_users_list_page()
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee(array);
    }
    
    /** @test */
    
}

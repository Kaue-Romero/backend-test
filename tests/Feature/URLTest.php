<?php

namespace Tests\Feature;

use App\Models\Register;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class URLTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_without_query_string()
    {
        $register = Register::factory()->create();

        $this->get('/r/'.$register->code)
            ->assertStatus(302)
            ->assertRedirect($register->url);
    }

    public function test_with_query_string()
    {
        $register = Register::factory()->create();

        $this->get('/r/'.$register->code.'?utm_source=google')
            ->assertStatus(302)
            ->assertRedirect($register->url.'?utm_source=google');
    }

    public function test_merging_queries()
    {
        $register = Register::factory()->create([
            'url' => 'http://example.com?utm_medium=email'
        ]);

        $this->get('/r/'.$register->code.'?utm_source=google')
            ->assertStatus(302)
            ->assertRedirect($register->url.'&utm_source=google');
    }

    public function test_merging_queries_with_same_key()
    {
        $register = Register::factory()->create([
            'url' => 'http://example.com?utm_medium=email'
        ]);

        $replacedURL = str_replace('utm_medium=email', 'utm_medium=google', $register->url);

        $this->get('/r/'.$register->code.'?utm_medium=google')
            ->assertStatus(302)
            ->assertRedirect($replacedURL);
    }

    public function test_merging_queries_ignoring_empty_key()
    {
        $register = Register::factory()->create([
            'url' => 'http://example.com?utm_medium=email&utc_campaign=example'
        ]);

        $replacedURL = str_replace('utm_medium=email', 'utm_medium=google', $register->url);

        $this->get('/r/'.$register->code.'?utm_medium=google')
            ->assertStatus(302)
            ->assertRedirect($replacedURL);
    }
}

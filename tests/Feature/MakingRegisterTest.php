<?php

namespace Tests\Feature;

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakingRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected $faker;
    protected $headers;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->headers = [
            'Accept' => 'application/json',
            'Referrer' => "https://www.google.com/",
            'User-Agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)"
        ];
    }

    /**
     * Criação de um redirect
     *
     *      Criação do redirect com URL válida
     *
     *      Criação do redirect com URL inválida por
     *          - DNS inválido
     *          - URL inválida
     *          - URL apontando para a própria aplicação
     *          - URL sem HTTPS
     *          - URL retornando status diferente de 200 ou 201
     *          - URL inválida pois possui query params com chave vazia
     */

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_creating_register_with_valid_url()
    {
        $response = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "https://www.google.com",
        ]);

        $response->assertStatus(201);
    }

    public function test_creating_register_with_invalid_url()
    {
        $response = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "https://www.google",
        ]);

        $response->assertStatus(400);
    }

    public function test_creating_register_without_https()
    {
        $attempt = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "www.google.com",
        ]);
        $attempt->assertStatus(400);

        $attempt2 = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "http://www.google.com",
        ]);
        $attempt2->assertStatus(400);
    }

    public function test_creating_register_with_invalid_dns()
    {
        $response = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "https://www.google.com.invalid",
        ]);

        $response->assertStatus(400);
    }

    public function test_creating_register_pointing_to_own_app()
    {
        $response = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => env('APP_URL'),
        ]);

        $response->assertStatus(400);
    }

    public function test_creating_register_with_invalid_query_params()
    {
        $response = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "https://www.google.com?param1=value1&param2=",
        ]);

        $response->assertStatus(400);
    }

    public function test_creating_register_with_invalid_status()
    {
        $response = $this->withHeaders($this->headers)->post('/api/register',[
            'url' => "https://www.google.com/404",
        ]);

        $response->assertStatus(400);
    }

}

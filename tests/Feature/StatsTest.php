<?php

namespace Tests\Feature;

use App\Models\Register;
use App\Models\RegisterLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use function PHPUnit\Framework\countOf;

class StatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Um teste para validação das estatísticas de acesso
     * Verificar se os redirects vindo do mesmo ip são contados como um único acesso no total de acessos únicos
     * Verificar se os headers referer são contados corretamente
     * Verificar se os acessos dos últimos 10 dias estão corretos
     * Verificar se os acessos dos últimos 10 dias estão corretos quando não há acessos
     * Verificar se os acessos dos últimos 10 dias estão corretos quando há acessos
     * Verificar se os acessos estão pegando somente os últimos 10 dias
     */

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_redirect_from_same_ip_as_unique()
    {
        $register = $this->post("/api/register", [
            "url" => "https://www.google.com"
        ]);

        $redirect_id = Register::where("code", $register->json("code"))->first()->id;

        for ($i = 0; $i < 5; $i++) {
            RegisterLog::factory()->create([
                "redirect_id" => $redirect_id,
                "ip" => "111.111.111.11" . $i,
            ]);
        }

        RegisterLog::factory(5)->create([
            "redirect_id" => $redirect_id,
            "ip" => "111.111.111.200",
        ]);

        $response = $this->get("/api/redirects/" . $register->json("code") . "/stats")->json();

        $this->assertEquals(6, $response[0]["redirects"]["uniques"]);
        $this->assertEquals(10, $response[0]["redirects"]["total"]);
    }

    public function test_last_10_days_access()
    {
        $register = $this->post("/api/register", [
            "url" => "https://www.google.com"
        ]);

        $response = $this->get("/api/redirects/" . $register->json("code") . "/stats")->json();

        $this->assertArrayHasKey("last_10_days", $response[0]);
        $this->assertEquals(0, count($response[0]["last_10_days"]));
    }

    public function test_last_10_days_access_with_logs()
    {
        $register = $this->post("/api/register", [
            "url" => "https://www.google.com"
        ]);

        $redirect_id = Register::where("code", $register->json("code"))->first()->id;


        $fakeUser = RegisterLog::factory()->create([
            "redirect_id" => $redirect_id
        ]);

        RegisterLog::factory()->create([
            "redirect_id" => $redirect_id,
            "created_at" => now()->subDays(11)
        ]);

        $this->get("/r/" . $register->json("code") . "/", [
            "ip" => $fakeUser->ip
        ]);

        $response = $this->get("/api/redirects/" . $register->json("code") . "/stats")->json();

        $this->assertArrayHasKey("last_10_days", $response[0]);
        $this->assertEquals(1, count($response[0]["last_10_days"]));
    }

    public function test_last_10_days_is_correct_and_only_10_days()
    {
        $register = $this->post("/api/register", [
            "url" => "https://www.google.com"
        ]);

        $redirect_id = Register::where("code", $register->json("code"))->first()->id;

        for ($i = 0; $i < 15; $i++) {
            RegisterLog::factory()->create([
                "redirect_id" => $redirect_id,
                "created_at" => now()->subDays($i)
            ]);
        }

        $response = $this->get("/api/redirects/" . $register->json("code") . "/stats")->json();

        $this->assertArrayHasKey("last_10_days", $response[0]);

        $this->assertEquals(10, count($response[0]["last_10_days"]));
    }

    public function test_headers_referer_count()
    {
        $register = $this->post("/api/register", [
            "url" => "https://www.google.com"
        ]);

        $redirect_id = Register::where("code", $register->json("code"))->first()->id;

        RegisterLog::factory(5)->create([
            "redirect_id" => $redirect_id,
            "header" => "https://www.google.com"
        ]);

        RegisterLog::factory(8)->create([
            "redirect_id" => $redirect_id,
            "header" => "https://www.youtube.com"
        ]);

        $response = $this->get("/api/redirects/" . $register->json("code") . "/stats")->json();

        $this->assertArrayHasKey("top_referer", $response[0]);
        $this->assertEquals("https://www.youtube.com", $response[0]["top_referer"]["referer"]);
        $this->assertEquals(8, $response[0]["top_referer"]["total"]);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FridayTimeTest extends TestCase
{
    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('cumayanekadarkaldi.com');
    }

    public function test_city_page_loads_for_valid_city(): void
    {
        $response = $this->get('/bursa-cuma-saati');
        $response->assertStatus(200);
        $response->assertSee('Bursa Cuma Saati');
        $response->assertSee('Bursa İçin Cuma Namazı Vakti');
    }

    public function test_city_page_returns_404_for_invalid_city(): void
    {
        $response = $this->get('/invalid-cuma-saati');
        $response->assertStatus(404);
    }

    public function test_api_returns_friday_time(): void
    {
        $response = $this->getJson('/api/friday-time?city=istanbul');
        $response->assertStatus(200)
            ->assertJsonStructure([
                'city',
                'friday_time',
                'next_friday',
                'weekly_times',
            ])
            ->assertJsonFragment(['city' => 'İstanbul']);
    }

    public function test_cache_is_used_for_api_calls(): void
    {
        Cache::flush();
        
        $this->getJson('/api/friday-time?city=ankara');
        $this->assertTrue(Cache::has('friday_time_ankara'));
        
        $response = $this->getJson('/api/friday-time?city=ankara');
        $response->assertStatus(200);
    }

    public function test_home_page_with_city_query_param(): void
    {
        $response = $this->get('/?city=izmir');
        $response->assertStatus(200);
        $response->assertSee('İzmir Cuma Saati');
    }

    public function test_city_page_contains_structured_data(): void
    {
        $response = $this->get('/ankara-cuma-saati');
        $response->assertStatus(200);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('Ankara Cuma Namazı', false);
    }

    public function test_city_normalization(): void
    {
        $response = $this->get('/istanbul-cuma-saati');
        $response->assertStatus(200);
        $response->assertSee('İstanbul Cuma Saati');
    }

    public function test_english_home_page_loads(): void
    {
        $response = $this->get('/en');
        $response->assertStatus(200);
        $response->assertSee('Friday Prayer Time');
    }

    public function test_english_city_page_loads(): void
    {
        $response = $this->get('/en/bursa-friday-time');
        $response->assertStatus(200);
        $response->assertSee('Bursa Friday Prayer Time');
    }

    public function test_widget_loads(): void
    {
        $response = $this->get('/widget/istanbul');
        $response->assertStatus(200);
        $response->assertSee('İstanbul');
    }

    public function test_weekly_times_shown_on_city_page(): void
    {
        $response = $this->get('/bursa-cuma-saati');
        $response->assertStatus(200);
        $response->assertSee('Haftalık Namaz Vakitleri');
    }
}

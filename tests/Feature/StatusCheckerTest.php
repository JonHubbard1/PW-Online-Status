<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class StatusCheckerTest extends TestCase
{
    public function test_page_renders_status_checker(): void
    {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSeeLivewire('status-checker');
    }

    public function test_status_shows_online_when_all_services_respond(): void
    {
        Http::fake([
            '*' => Http::response('OK', 200),
        ]);

        Livewire::test('status-checker')
            ->assertSee('Office Internet')
            ->assertSee('Static IP Line')
            ->assertSee('Online')
            ->assertSee('Last checked at')
            ->assertDontSee('Offline');
    }

    public function test_status_shows_offline_when_service_does_not_respond(): void
    {
        Http::fake(function (Request $request) {
            return str_contains($request->url(), 'office.pwprods.co.uk')
                ? Http::response('Service Unavailable', 503)
                : Http::response('OK', 200);
        });

        Livewire::test('status-checker')
            ->assertSee('Office Internet')
            ->assertSee('Static IP Line')
            ->assertSeeInOrder(['Online', 'Offline']);
    }

    public function test_status_shows_offline_on_request_exception(): void
    {
        Http::fake([
            '*' => function (): never {
                throw new ConnectionException('Could not connect');
            },
        ]);

        Livewire::test('status-checker')
            ->assertSee('Offline');
    }
}

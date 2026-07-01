<?php

use Illuminate\Support\Facades\Http;
use Livewire\Component;

new class extends Component
{
    /**
     * @var array<int, array{name: string, url: string, online: bool, error: string|null}>
     */
    public array $services = [];

    public ?string $checkedAt = null;

    public function mount(): void
    {
        $this->services = [
            [
                'name' => 'Office Internet',
                'url' => 'https://nas.prods.co.uk',
                'online' => false,
                'error' => null,
            ],
            [
                'name' => 'Static IP Line',
                'url' => 'https://office.pwprods.co.uk',
                'online' => false,
                'error' => null,
            ],
        ];

        $this->check();
    }

    public function check(): void
    {
        foreach ($this->services as $index => $service) {
            try {
                $response = Http::timeout(10)->get($service['url']);

                $this->services[$index]['online'] = $response->successful();
                $this->services[$index]['error'] = null;
            } catch (Throwable $exception) {
                $this->services[$index]['online'] = false;
                $this->services[$index]['error'] = $exception->getMessage();
            }
        }

        $this->checkedAt = now()->format('H:i:s');
    }
};
?>

<div wire:poll.30s="check" class="flex w-full flex-col items-center space-y-6">
    @foreach ($services as $service)
        @php
            $isOnline = $service['online'];
        @endphp

        <div class="w-full max-w-sm rounded-2xl border-2 p-6 shadow-lg transition-colors duration-500 sm:p-8 lg:max-w-md {{ $isOnline ? 'border-emerald-400 bg-emerald-50 dark:border-emerald-600 dark:bg-emerald-950/40' : 'border-red-400 bg-red-50 dark:border-red-600 dark:bg-red-950/40' }}">
            <div class="mb-6 flex items-center justify-center gap-3 sm:mb-8">
                <span class="relative flex h-6 w-6 sm:h-8 sm:w-8">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75 {{ $isOnline ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    <span class="relative inline-flex h-6 w-6 rounded-full sm:h-8 sm:w-8 {{ $isOnline ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                </span>

                <h2 class="text-xl font-semibold sm:text-2xl {{ $isOnline ? 'text-emerald-900 dark:text-emerald-100' : 'text-red-900 dark:text-red-100' }}">
                    {{ $service['name'] }}
                </h2>
            </div>

            <div class="mb-8 text-center sm:mb-10">
                <p class="text-5xl font-black tracking-tight sm:text-6xl {{ $isOnline ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $isOnline ? 'Online' : 'Offline' }}
                </p>

                @if ($service['error'])
                    <p class="mt-4 text-sm text-red-800 dark:text-red-200">
                        {{ $service['error'] }}
                    </p>
                @endif
            </div>
        </div>
    @endforeach

    <p class="text-center text-sm font-medium text-slate-300">
        {{ $checkedAt ? 'Last checked at '.$checkedAt : 'Checking now…' }}
    </p>

    <button
        wire:click="check"
        wire:loading.attr="disabled"
        type="button"
        class="w-full max-w-sm rounded-xl bg-white px-5 py-4 text-base font-bold text-slate-900 shadow-md transition-transform active:scale-95 hover:bg-slate-100 sm:text-lg lg:max-w-md"
    >
        <span wire:loading.remove>Check again</span>
        <span wire:loading>Checking…</span>
    </button>
</div>

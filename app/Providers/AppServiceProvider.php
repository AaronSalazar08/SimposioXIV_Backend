<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
       // DB::prohibitDestructiveCommands($this->app->isProduction());

        Event::listen(function (CommandStarting $event): void {
            if ($event->command === 'serve') {
                $this->mostrarEnlacesSimposio($event->input, $event->output);
            }
        });
    }

    private function mostrarEnlacesSimposio(InputInterface $input, OutputInterface $output): void
    {
        $host = $input->hasParameterOption('--host') ? $input->getParameterOption('--host') : '127.0.0.1';
        $port = $input->hasParameterOption('--port') ? $input->getParameterOption('--port') : 8000;
        $base = "http://{$host}:{$port}";
        $frontend = config('services.frontend.url');

        $output->writeln('');
        $output->writeln('  <fg=yellow;options=bold>XIV Simposio — enlaces rápidos</>');
        $output->writeln("    API health ..... <fg=cyan>{$base}/api/health</>");
        $output->writeln("    API tester ..... <fg=cyan>{$base}/api-tester</>");
        $output->writeln("    Frontend (SPA) . <fg=cyan>{$frontend}</> <fg=gray>(arrancar aparte: npm run dev en SimposioXIV_Frontend)</>");
        $output->writeln('');
    }
}

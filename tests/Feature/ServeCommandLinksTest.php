<?php

namespace Tests\Feature;

use Illuminate\Console\Events\CommandStarting;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class ServeCommandLinksTest extends TestCase
{
    public function test_muestra_enlaces_al_iniciar_el_comando_serve(): void
    {
        $output = new BufferedOutput;

        event(new CommandStarting('serve', new ArgvInput(['artisan', 'serve']), $output));

        $texto = $output->fetch();

        $this->assertStringContainsString('XIV Simposio', $texto);
        $this->assertStringContainsString('/api/health', $texto);
        $this->assertStringContainsString('/api-tester', $texto);
    }

    public function test_no_muestra_enlaces_para_otros_comandos(): void
    {
        $output = new BufferedOutput;

        event(new CommandStarting('migrate', new ArgvInput(['artisan', 'migrate']), $output));

        $this->assertSame('', $output->fetch());
    }

    public function test_usa_el_host_y_puerto_indicados_en_las_opciones(): void
    {
        $output = new BufferedOutput;

        event(new CommandStarting('serve', new ArgvInput(['artisan', 'serve', '--host=0.0.0.0', '--port=9000']), $output));

        $this->assertStringContainsString('http://0.0.0.0:9000/api/health', $output->fetch());
    }
}

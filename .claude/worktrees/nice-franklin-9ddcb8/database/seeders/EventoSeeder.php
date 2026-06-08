<?php

namespace Database\Seeders;

use App\Enums\TipoEvento;
use App\Models\Area;
use App\Models\Evento;
use App\Models\Horario;
use App\Models\Ponente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $horarios = Horario::with('aula')->get();

        $ponentes = Ponente::all()->keyBy('nombre');

        $areas = Area::all()->keyBy('nombre');

        $horarioDia1Auditorio = $horarios->first(fn ($h) => $h->numero_dia === 1);
        $horarioDia2Aula101 = $horarios->first(fn ($h) => $h->numero_dia === 2 && $h->aula->numero === 101);
        $horarioDia2Aula105 = $horarios->first(fn ($h) => $h->numero_dia === 2 && $h->aula->numero === 105);
        $horarioDia2Aula201 = $horarios->first(fn ($h) => $h->numero_dia === 2 && $h->aula->numero === 201);
        $horarioDia2Aula202 = $horarios->first(fn ($h) => $h->numero_dia === 2 && $h->aula->numero === 202);
        $horarioDia3Aula101 = $horarios->first(fn ($h) => $h->numero_dia === 3 && $h->aula->numero === 101);
        $horarioDia3Aula105 = $horarios->first(fn ($h) => $h->numero_dia === 3 && $h->aula->numero === 105);
        $horarioDia3Aula201 = $horarios->first(fn ($h) => $h->numero_dia === 3 && $h->aula->numero === 201);
        $horarioDia3Auditorio = $horarios->first(fn ($h) => $h->numero_dia === 3 && $h->aula->edificio === 'Auditorio');

        $eventos = [
            // Día 1
            [
                'horario' => $horarioDia1Auditorio,
                'ponente' => $ponentes->get('Manuel'),
                'titulo' => 'Ceremonia de Apertura — SIMPOSIO XIV',
                'descripcion' => 'Bienvenida oficial al XIV Simposio de Informática UCR sede Guanacaste Liberia. Palabras de apertura por las autoridades universitarias.',
                'tipo' => TipoEvento::Apertura,
                'capacidad' => 200,
                'areas' => [],
            ],
            // Día 2 — franja 08:00-10:00
            [
                'horario' => $horarioDia2Aula101,
                'ponente' => $ponentes->get('Carlos'),
                'titulo' => 'Introducción a Machine Learning con Python',
                'descripcion' => 'Taller práctico de fundamentos de ML: regresión, clasificación y evaluación de modelos usando scikit-learn.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 30,
                'areas' => ['Inteligencia Artificial'],
            ],
            [
                'horario' => $horarioDia2Aula105,
                'ponente' => $ponentes->get('Laura'),
                'titulo' => 'Fundamentos de Ciberseguridad',
                'descripcion' => 'Taller sobre vectores de ataque, OWASP Top 10 y técnicas básicas de pentesting.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 25,
                'areas' => ['Ciberseguridad'],
            ],
            // Día 2 — franja 10:30-12:00
            [
                'horario' => $horarioDia2Aula201,
                'ponente' => $ponentes->get('Andrés'),
                'titulo' => 'APIs REST con Laravel',
                'descripcion' => 'Charla sobre diseño de APIs RESTful, autenticación con Sanctum y buenas prácticas en Laravel.',
                'tipo' => TipoEvento::Charla,
                'capacidad' => 40,
                'areas' => ['Desarrollo Web'],
            ],
            [
                'horario' => $horarioDia2Aula202,
                'ponente' => $ponentes->get('Patricia'),
                'titulo' => 'Cloud Native con Kubernetes',
                'descripcion' => 'Charla sobre contenerización con Docker, orquestación con Kubernetes y despliegue en la nube.',
                'tipo' => TipoEvento::Charla,
                'capacidad' => 40,
                'areas' => ['Cloud Computing'],
            ],
            // Día 3 — franja 08:00-10:00
            [
                'horario' => $horarioDia3Aula101,
                'ponente' => $ponentes->get('Diego'),
                'titulo' => 'Análisis de Datos con Pandas y Matplotlib',
                'descripcion' => 'Taller práctico de limpieza, transformación y visualización de datos con Python.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 25,
                'areas' => ['Ciencia de Datos'],
            ],
            [
                'horario' => $horarioDia3Aula105,
                'ponente' => $ponentes->get('Roberto'),
                'titulo' => 'Modelado Avanzado de Bases de Datos',
                'descripcion' => 'Taller sobre normalización, índices, optimización de consultas y diseño físico.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 30,
                'areas' => ['Bases de Datos'],
            ],
            // Día 3 — franja 10:30-12:00
            [
                'horario' => $horarioDia3Aula201,
                'ponente' => $ponentes->get('Ana'),
                'titulo' => 'El Futuro de la IA Generativa',
                'descripcion' => 'Charla sobre LLMs, agentes de IA, RAG y el impacto en la industria tech.',
                'tipo' => TipoEvento::Charla,
                'capacidad' => 40,
                'areas' => ['Inteligencia Artificial'],
            ],
            // Día 3 — Clausura
            [
                'horario' => $horarioDia3Auditorio,
                'ponente' => $ponentes->get('Manuel'),
                'titulo' => 'Ceremonia de Clausura — SIMPOSIO XIV',
                'descripcion' => 'Cierre oficial del XIV Simposio. Reconocimientos, premiaciones y palabras finales.',
                'tipo' => TipoEvento::Clausura,
                'capacidad' => 200,
                'areas' => [],
            ],
        ];

        foreach ($eventos as $data) {
            $evento = Evento::create([
                'horario_id' => $data['horario']?->id,
                'ponente_id' => $data['ponente']?->id,
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'tipo' => $data['tipo'],
                'capacidad' => $data['capacidad'],
                'numero_inscritos' => 0,
                'esta_activo' => true,
            ]);

            if (! empty($data['areas'])) {
                $areaIds = collect($data['areas'])
                    ->map(fn ($nombre) => $areas->get($nombre)?->id)
                    ->filter()
                    ->values()
                    ->all();

                $evento->areas()->attach($areaIds);
            }
        }
    }
}

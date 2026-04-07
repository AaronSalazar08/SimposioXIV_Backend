<?php

namespace Database\Seeders;

use App\Enums\TipoEvento;
use App\Models\Area;
use App\Models\Aula;
use App\Models\Evento;
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
        $aulaAuditorio = Aula::where('edificio', 'Auditorio')->first();
        $aulas = Aula::where('edificio', '!=', 'Auditorio')->get();

        $areaIA = Area::where('nombre', 'Inteligencia Artificial')->first();
        $areaSeg = Area::where('nombre', 'Ciberseguridad')->first();
        $areaWeb = Area::where('nombre', 'Desarrollo Web')->first();
        $areaBD = Area::where('nombre', 'Bases de Datos')->first();
        $areaCloud = Area::where('nombre', 'Cloud Computing')->first();
        $areaCiencia = Area::where('nombre', 'Ciencia de Datos')->first();

        // Día 1 — Apertura
        $apertura = Evento::create([
            'titulo' => 'Ceremonia de Apertura — SIMPOSIO XIV',
            'descripcion' => 'Bienvenida oficial al XIV Simposio de Informática UCR sede Guanacaste Liberia. Palabras de apertura por las autoridades universitarias.',
            'tipo' => TipoEvento::Apertura,
            'capacidad' => 200,
            'numero_inscritos' => 0,
            'esta_activo' => true,
        ]);

        if ($aulaAuditorio) {
            $apertura->horarios()->create([
                'aula_id' => $aulaAuditorio->id,
                'numero_dia' => 1,
                'hora_inicio' => '2025-06-09 08:00:00',
                'hora_fin' => '2025-06-09 10:00:00',
            ]);
        }

        // Día 2 — Talleres y Charlas
        $talleres = [
            [
                'titulo' => 'Introducción a Machine Learning con Python',
                'descripcion' => 'Taller práctico de fundamentos de ML: regresión, clasificación y evaluación de modelos usando scikit-learn.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 30,
                'area' => $areaIA,
                'aula' => $aulas->get(0),
                'hora_inicio' => '2025-06-10 08:00:00',
                'hora_fin' => '2025-06-10 10:00:00',
            ],
            [
                'titulo' => 'Fundamentos de Ciberseguridad',
                'descripcion' => 'Taller sobre vectores de ataque, OWASP Top 10 y técnicas básicas de pentesting.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 25,
                'area' => $areaSeg,
                'aula' => $aulas->get(1),
                'hora_inicio' => '2025-06-10 08:00:00',
                'hora_fin' => '2025-06-10 10:00:00',
            ],
            [
                'titulo' => 'APIs REST con Laravel',
                'descripcion' => 'Charla sobre diseño de APIs RESTful, autenticación con Sanctum y buenas prácticas en Laravel.',
                'tipo' => TipoEvento::Charla,
                'capacidad' => 40,
                'area' => $areaWeb,
                'aula' => $aulas->get(2),
                'hora_inicio' => '2025-06-10 10:30:00',
                'hora_fin' => '2025-06-10 12:00:00',
            ],
            [
                'titulo' => 'Cloud Native con Kubernetes',
                'descripcion' => 'Charla sobre contenerización con Docker, orquestación con Kubernetes y despliegue en la nube.',
                'tipo' => TipoEvento::Charla,
                'capacidad' => 40,
                'area' => $areaCloud,
                'aula' => $aulas->get(3),
                'hora_inicio' => '2025-06-10 10:30:00',
                'hora_fin' => '2025-06-10 12:00:00',
            ],
        ];

        // Día 3 — Talleres y Charlas + Clausura
        $dia3 = [
            [
                'titulo' => 'Análisis de Datos con Pandas y Matplotlib',
                'descripcion' => 'Taller práctico de limpieza, transformación y visualización de datos con Python.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 25,
                'area' => $areaCiencia,
                'aula' => $aulas->get(0),
                'hora_inicio' => '2025-06-11 08:00:00',
                'hora_fin' => '2025-06-11 10:00:00',
            ],
            [
                'titulo' => 'Modelado Avanzado de Bases de Datos',
                'descripcion' => 'Taller sobre normalización, índices, optimización de consultas y diseño físico.',
                'tipo' => TipoEvento::Taller,
                'capacidad' => 30,
                'area' => $areaBD,
                'aula' => $aulas->get(1),
                'hora_inicio' => '2025-06-11 08:00:00',
                'hora_fin' => '2025-06-11 10:00:00',
            ],
            [
                'titulo' => 'El Futuro de la IA Generativa',
                'descripcion' => 'Charla sobre LLMs, agentes de IA, RAG y el impacto en la industria tech.',
                'tipo' => TipoEvento::Charla,
                'capacidad' => 40,
                'area' => $areaIA,
                'aula' => $aulas->get(2),
                'hora_inicio' => '2025-06-11 10:30:00',
                'hora_fin' => '2025-06-11 12:00:00',
            ],
        ];

        foreach (array_merge($talleres, $dia3) as $data) {
            $evento = Evento::create([
                'titulo' => $data['titulo'],
                'descripcion' => $data['descripcion'],
                'tipo' => $data['tipo'],
                'capacidad' => $data['capacidad'],
                'numero_inscritos' => 0,
                'esta_activo' => true,
            ]);

            if ($data['area']) {
                $evento->areas()->attach($data['area']->id);
            }

            if ($data['aula']) {
                $evento->horarios()->create([
                    'aula_id' => $data['aula']->id,
                    'numero_dia' => str_contains($data['hora_inicio'], '06-10') ? 2 : 3,
                    'hora_inicio' => $data['hora_inicio'],
                    'hora_fin' => $data['hora_fin'],
                ]);
            }
        }

        // Clausura — Día 3
        $clausura = Evento::create([
            'titulo' => 'Ceremonia de Clausura — SIMPOSIO XIV',
            'descripcion' => 'Cierre oficial del XIV Simposio. Reconocimientos, premiaciones y palabras finales.',
            'tipo' => TipoEvento::Clausura,
            'capacidad' => 200,
            'numero_inscritos' => 0,
            'esta_activo' => true,
        ]);

        if ($aulaAuditorio) {
            $clausura->horarios()->create([
                'aula_id' => $aulaAuditorio->id,
                'numero_dia' => 3,
                'hora_inicio' => '2025-06-11 14:00:00',
                'hora_fin' => '2025-06-11 16:00:00',
            ]);
        }
    }
}

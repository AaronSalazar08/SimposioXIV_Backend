<?php

namespace Database\Seeders;

use App\Models\Ponente;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PonentesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ponentes = [
            [
                'nombre' => 'Equipo Organizador',
                'apellidos' => 'SG-CIE',
                'educacion' => 'Universidad de Costa Rica',
                'grado_academico' => 'Licenciado',
                'descripcion' => 'Equipo organizador del XIV Simposio de Informática Empresarial, Sede Guanacaste UCR.',
            ],
            [
                'nombre' => 'Manuel',
                'apellidos' => 'Rodríguez Solano',
                'educacion' => 'Universidad de Costa Rica',
                'grado_academico' => 'Doctor',
                'descripcion' => 'Director de la Sede de Guanacaste UCR. Especialista en gestión universitaria y tecnologías educativas.',
            ],
            [
                'nombre' => 'Carlos',
                'apellidos' => 'Mendoza Alfaro',
                'educacion' => 'Instituto Tecnológico de Costa Rica',
                'grado_academico' => 'Doctor',
                'descripcion' => 'Investigador en Inteligencia Artificial y Machine Learning. Autor de múltiples publicaciones en aprendizaje automático y visión por computadora.',
            ],
            [
                'nombre' => 'Laura',
                'apellidos' => 'Jiménez Vargas',
                'educacion' => 'Universidad Nacional de Costa Rica',
                'grado_academico' => 'Magíster',
                'descripcion' => 'Experta en ciberseguridad ofensiva y defensiva. Certificada en OSCP y CEH. Consultora de seguridad para empresas del sector financiero.',
            ],
            [
                'nombre' => 'Andrés',
                'apellidos' => 'Vargas Mora',
                'educacion' => 'Universidad de Costa Rica',
                'grado_academico' => 'Licenciado',
                'descripcion' => 'Desarrollador fullstack con más de 10 años de experiencia. Especialista en Laravel y ecosistemas PHP modernos. Contribuidor activo de paquetes open source.',
            ],
            [
                'nombre' => 'Patricia',
                'apellidos' => 'Solano Castro',
                'educacion' => 'Universidad de Costa Rica',
                'grado_academico' => 'Doctor',
                'descripcion' => 'Arquitecta de soluciones cloud certificada en AWS y GCP. Investigadora en computación distribuida y arquitecturas nativas en la nube.',
            ],
            [
                'nombre' => 'Diego',
                'apellidos' => 'Mora Gutiérrez',
                'educacion' => 'Instituto Tecnológico de Costa Rica',
                'grado_academico' => 'Magíster',
                'descripcion' => 'Científico de datos con experiencia en análisis estadístico, visualización y modelos predictivos aplicados a negocios.',
            ],
            [
                'nombre' => 'Roberto',
                'apellidos' => 'Soto Herrera',
                'educacion' => 'Universidad Nacional de Costa Rica',
                'grado_academico' => 'Doctor',
                'descripcion' => 'Especialista en bases de datos relacionales y no relacionales. Autor del libro "Diseño y Optimización de Bases de Datos en Entornos Empresariales".',
            ],
            [
                'nombre' => 'Ana',
                'apellidos' => 'Gutiérrez Blanco',
                'educacion' => 'Universidad de Costa Rica',
                'grado_academico' => 'Magíster',
                'descripcion' => 'Investigadora en modelos de lenguaje de gran escala (LLMs) y sistemas de IA generativa. Ponente internacional en congresos de IA.',
            ],
        ];

        foreach ($ponentes as $ponente) {
            Ponente::create($ponente);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MatrizDinamicaSeeder extends Seeder
{
    public function run()
    {
        // 1. LIMPIEZA TOTAL Y RESET DE IDs
    // Desactivamos llaves foráneas para que nos deje limpiar la tabla
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    DB::table('matriz_homologacions')->truncate(); 
    DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $estructura = [
            // --- DEPARTAMENTO DE MECÁNICA ---
            'Mecánica' => [
                // Grupo Mecánica Básica (G1, G2)
                'basicos' => [
                    'talleres' => ['G1', 'G2'],
                    'epps' => [
                        'Casco de Seguridad modelo Jockey' => 'obligatorio', // X
                        'Lentes Modelo Checklite con antiempañe, Protección' => 'especifico', // Y
                        'Guantes de poliester C/ Palma de Jebe Antideslizan' => 'obligatorio', // X
                        'Guantes dieléctricos en baja tensión GUANTE HYFLEX' => 'especifico', // Y
                        'Guantes de protección química de nitrilo Solvex® 3' => 'especifico', // Y
                        'Respirador media cara 3M 6000' => 'especifico', // Y
                        'Filtro para particulas 3M 2097' => 'especifico', // Y
                        'Cartucho para vapores orgánicos 3M 6003' => 'especifico', // Y
                        'Protector de oido (tipo tapón)' => 'especifico', // Y
                        'Zapato de seguridad con punta de acero' => 'obligatorio', // X
                        'Chaleco con Cinta Reflectiva' => 'obligatorio', // X
                        'Mandil para talleres / Laboratorios' => 'obligatorio', // X
                    ]
                ],
                // Grupo Soldadura (G3, G6B, F3)
                'soldadura' => [
                    'talleres' => ['G3', 'G6B', 'F3'],
                    'epps' => [
                        'Casco de Seguridad modelo Jockey' => 'obligatorio',
                        'Lentes Modelo Checklite con antiempañe, Protección' => 'obligatorio',
                        'Careta de Esmerilar con visor' => 'especifico',
                        'Careta para soldador' => 'especifico',
                        'Guantes de poliester C/ Palma de Jebe Antideslizan' => 'obligatorio',
                        'Guante para soldadura y herrería' => 'especifico',
                        'Respirador media cara 3M 6000' => 'especifico',
                        'Filtro para particulas 3M 2097' => 'especifico',
                        'Protector de oido (tipo tapón)' => 'especifico',
                        'Mandil para soldar' => 'especifico',
                        'Zapato de seguridad con punta de acero' => 'obligatorio',
                        'Escarpines' => 'especifico',
                        'Chaleco con Cinta Reflectiva' => 'obligatorio',
                        'Mandil para talleres / Laboratorios' => 'obligatorio',
                    ]
                ],
                // Grupo Eléctrico/Mantenimiento (G5A, G5B, G6A, H2, H3, H4, D3)
                'electricos' => [
                    'talleres' => ['G5A', 'G5B', 'G6A', 'H2', 'H3', 'H4', 'D3'],
                    'epps' => [
                        'Casco de Seguridad modelo Jockey' => 'obligatorio',
                        'Lentes Modelo Checklite con antiempañe, Protección' => 'obligatorio',
                        'Zapato dieléctrico con punta reforzada para alta t' => 'especifico',
                        'Zapato de seguridad con punta de acero' => 'obligatorio',
                        'Protector de oido (tipo tapón)' => 'especifico',
                        'Chaleco con Cinta Reflectiva' => 'obligatorio',
                        'Mandil para talleres / Laboratorios' => 'obligatorio',
                    ]
                ],
            ],

            // --- DEPARTAMENTO DE ESTUDIOS GENERALES Y TOPOGRAFÍA ---
            'Estudios Generales' => [
                'topografia_y_d1' => [
                    'talleres' => ['Topografía', 'D1', 'D1/ H4'],
                    'epps' => [
                        'Casco de Seguridad modelo Jockey' => 'obligatorio',
                        'Lentes Modelo Checklite con antiempañe, Protección' => 'obligatorio',
                        'Zapato de seguridad con punta de acero' => 'obligatorio',
                        'Chaleco con Cinta Reflectiva' => 'obligatorio',
                        'Mandil para talleres / Laboratorios' => 'obligatorio',
                    ]
                ]
            ],

            // --- DEPARTAMENTO DE TECNOLOGÍA DIGITAL Y DE GESTIÓN ---
            'Tecnología Digital' => [
                'digital' => [
                    'talleres' => ['D1 Química', 'D1 Física', 'G4', 'FABLAB'],
                    'epps' => [
                        'Lentes Modelo Checklite con antiempañe, Protección' => 'obligatorio',
                        'Lentes Google (Antiparra de Seguridad) STEELPRO' => 'especifico',
                        'Guantes de protección química de nitrilo Solvex® 3' => 'especifico',
                        'Respirador media cara 3M 6000' => 'especifico',
                        'Zapato de seguridad con punta de acero' => 'obligatorio',
                        'Careta de Esmerilar con visor' => 'especifico',
                    ]
                ]
            ]
        ];

        // --- LÓGICA MEJORADA ---
        foreach ($estructura as $nombreDepto => $grupos) {
            // Buscamos el departamento
            $depto = DB::table('departamentos')->where('nombre', 'like', "%$nombreDepto%")->first();

            if ($depto) {
                foreach ($grupos as $config) {
                    foreach ($config['talleres'] as $nombreTaller) {
                        foreach ($config['epps'] as $nombreEpp => $tipo) {
                            
                            // MEJORA: Buscamos por nombre completo o parcial sin cortar rígidamente a 30
                            $epp = DB::table('epps')
                                ->where('nombre', 'like', '%' . trim($nombreEpp) . '%')
                                ->first();

                            if ($epp) {
                                DB::table('matriz_homologacions')->insert([
                                    'taller' => $nombreTaller,
                                    'epp_id' => $epp->id,
                                    'departamento_id' => $depto->id,
                                    'tipo_requerimiento' => $tipo,
                                    'puesto' => 'Docente TC/TP',
                                    'activo' => true,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                            } else {
                                // Esto te avisará en la terminal si el EPP no existe
                                $this->command->warn("EPP no encontrado: " . $nombreEpp);
                            }
                        }
                    }
                }
            } else {
                $this->command->error("Departamento no encontrado: " . $nombreDepto);
            }
        }
        $this->command->info("¡Matriz sincronizada con éxito!");
    }
    }

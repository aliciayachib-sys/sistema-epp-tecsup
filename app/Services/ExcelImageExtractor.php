<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ExcelImageExtractor
{
    public static function extraerImagenesConNombres($archivoPath)
    {
        $imagenesPorEpp = [];
        
        try {
            $spreadsheet = IOFactory::load($archivoPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $drawings = $worksheet->getDrawingCollection();
            
            Log::info('Extrayendo imágenes. Total encontrado: ' . count($drawings));
            
            $zip = new ZipArchive();
            if ($zip->open($archivoPath) !== true) {
                Log::error('No se pudo abrir el archivo Excel como ZIP');
                return $imagenesPorEpp;
            }
            
            foreach ($drawings as $drawing) {
                $coordinate = $drawing->getCoordinates();
                preg_match('/(\d+)$/', $coordinate, $matches);
                
                if (!isset($matches[1])) continue;
                
                $numeroFila = (int)$matches[1];
                $nombreEpp = self::obtenerNombreEppDeFila($worksheet, $numeroFila);
                
                if (!$nombreEpp) {
                    Log::warning("No se pudo obtener nombre de EPP para fila {$numeroFila}");
                    continue;
                }
                
                $imagenPath = self::guardarImagenDesdeZip($zip, $drawing, $nombreEpp);
                
                if ($imagenPath) {
                    $clave = trim($nombreEpp);
                    $imagenesPorEpp[$clave] = $imagenPath;
                    Log::info("✓ Imagen guardada para EPP '{$clave}': {$imagenPath}");
                }
            }
            
            $zip->close();
            
            Log::info('Extracción completada. Total guardadas: ' . count($imagenesPorEpp));
            Log::info('Mapeo de imágenes: ' . json_encode(array_keys($imagenesPorEpp)));
            
        } catch (\Exception $e) {
            Log::error('Error extrayendo imágenes: ' . $e->getMessage());
        }
        
        return $imagenesPorEpp;
    }
    
    private static function obtenerNombreEppDeFila($worksheet, $numeroFila)
    {
        try {
            $celda = $worksheet->getCellByColumnAndRow(2, $numeroFila);
            $valor = $celda->getValue();
            return $valor ? trim((string)$valor) : null;
        } catch (\Exception $e) {
            Log::warning("Error leyendo nombre de EPP en fila {$numeroFila}: " . $e->getMessage());
            return null;
        }
    }

    private static function guardarImagenDesdeZip($zip, $drawing, $nombreEpp)
    {
        try {
            $rutaEnZip = $drawing->getPath();
            
            if (strpos($rutaEnZip, '#') !== false) {
                $rutaEnZip = substr($rutaEnZip, strpos($rutaEnZip, '#') + 1);
            }
            
            Log::info("Intentando extraer imagen de ZIP: {$rutaEnZip}");
            
            $imagenStream = $zip->getStream($rutaEnZip);
            
            if (!$imagenStream) {
                Log::warning("No se pudo leer imagen del ZIP: {$rutaEnZip}");
                return null;
            }
            
            $imagenContenido = stream_get_contents($imagenStream);
            fclose($imagenStream);
            
            if (empty($imagenContenido)) {
                Log::warning("El contenido de la imagen está vacío para: {$rutaEnZip}");
                return null;
            }
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imagenContenido);
            finfo_close($finfo);
            
            $extensiones = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
            ];
            
            $ext = $extensiones[$mimeType] ?? 'jpg';
            
            Log::info("MIME type detectado: {$mimeType}, extensión: {$ext}");
            
            $nombreSanitizado = preg_replace('/[^a-zA-Z0-9_-]/', '_', substr($nombreEpp, 0, 30));

            // ── Detectar CLOUDINARY_URL con getenv() que funciona bien en Docker/Railway ──
            $cloudinaryUrl = getenv('CLOUDINARY_URL') ?: env('CLOUDINARY_URL');

            if (!empty($cloudinaryUrl)) {
                Log::info("Cloudinary detectado, subiendo imagen...");

                $tempFile = tempnam(sys_get_temp_dir(), 'epp_') . '.' . $ext;
                file_put_contents($tempFile, $imagenContenido);

                try {
                    $result = cloudinary()->upload($tempFile, [
                        'folder'    => 'epps',
                        'public_id' => 'epp_' . $nombreSanitizado . '_' . time(),
                    ]);

                    unlink($tempFile);

                    $url = $result->getSecurePath();
                    Log::info("✓ Imagen subida a Cloudinary: {$url}");

                    return $url;

                } catch (\Throwable $cloudEx) {
                    Log::error("Error subiendo a Cloudinary: " . $cloudEx->getMessage());
                    if (file_exists($tempFile)) unlink($tempFile);
                    // Si falla Cloudinary, cae al fallback local
                }
            } else {
                Log::warning("CLOUDINARY_URL no detectado, usando storage local.");
            }

            // ── Fallback: guardar en disco local ──
            $nombreFinal = 'epps/epp_' . $nombreSanitizado . '_' . time() . '.' . $ext;
            Storage::disk('public')->put($nombreFinal, $imagenContenido);
            Log::info("✓ Imagen guardada localmente: {$nombreFinal}");

            return $nombreFinal;
            
        } catch (\Throwable $e) {
            Log::error("Error extrayendo imagen del ZIP para EPP '{$nombreEpp}': " . $e->getMessage());
            return null;
        }
    }
}
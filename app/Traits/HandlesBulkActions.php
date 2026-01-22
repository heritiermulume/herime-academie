<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HandlesBulkActions
{
    /**
     * Traiter une action en lot
     */
    protected function handleBulkAction(Request $request, $modelClass, $actions = [])
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'action' => 'required|string'
        ]);

        $ids = $request->input('ids');
        $action = $request->input('action');

        // Actions par défaut
        $defaultActions = [
            'delete' => function($ids, $modelClass) {
                return $this->bulkDelete($ids, $modelClass);
            },
            'activate' => function($ids, $modelClass) {
                return $this->bulkUpdate($ids, $modelClass, ['is_active' => true]);
            },
            'deactivate' => function($ids, $modelClass) {
                return $this->bulkUpdate($ids, $modelClass, ['is_active' => false]);
            },
            'publish' => function($ids, $modelClass) {
                return $this->bulkUpdate($ids, $modelClass, ['is_published' => true]);
            },
            'unpublish' => function($ids, $modelClass) {
                return $this->bulkUpdate($ids, $modelClass, ['is_published' => false]);
            },
        ];

        $allActions = array_merge($defaultActions, $actions);

        if (!isset($allActions[$action])) {
            return response()->json([
                'success' => false,
                'message' => 'Action non reconnue.'
            ], 400);
        }

        try {
            DB::beginTransaction();
            
            $result = $allActions[$action]($ids, $modelClass);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $result['message'] ?? 'Action effectuée avec succès.',
                'count' => $result['count'] ?? count($ids),
                'reload' => $result['reload'] ?? true
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'action en lot: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer en lot
     */
    protected function bulkDelete($ids, $modelClass)
    {
        $count = $modelClass::whereIn('id', $ids)->delete();
        
        return [
            'message' => "{$count} élément(s) supprimé(s) avec succès.",
            'count' => $count
        ];
    }

    /**
     * Mettre à jour en lot
     */
    protected function bulkUpdate($ids, $modelClass, $attributes)
    {
        $count = $modelClass::whereIn('id', $ids)->update($attributes);
        
        $actionLabel = $this->getActionLabel($attributes);
        
        return [
            'message' => "{$count} élément(s) {$actionLabel} avec succès.",
            'count' => $count
        ];
    }

    /**
     * Obtenir le libellé de l'action
     */
    protected function getActionLabel($attributes)
    {
        if (isset($attributes['is_active'])) {
            return $attributes['is_active'] ? 'activé(s)' : 'désactivé(s)';
        }
        if (isset($attributes['is_published'])) {
            return $attributes['is_published'] ? 'publié(s)' : 'dépublié(s)';
        }
        return 'modifié(s)';
    }

    /**
     * Exporter les données
     */
    protected function exportData(Request $request, $query, $columns, $filename = 'export')
    {
        try {
            $format = $request->get('format', 'csv');
            $ids = $request->get('ids');
            
            // Filtrer par IDs si fournis
            if ($ids) {
                $idsArray = is_array($ids) ? $ids : explode(',', $ids);
                $idsArray = array_filter(array_map('trim', $idsArray));
                if (!empty($idsArray)) {
                    $query->whereIn('id', $idsArray);
                }
            }
            
            $data = $query->get();
            
            if ($format === 'excel') {
                return $this->exportToExcel($data, $columns, $filename);
            } elseif ($format === 'pdf') {
                return $this->exportToPdf($data, $columns, $filename);
            } else {
                return $this->exportToCsv($data, $columns, $filename);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            // Retourner une réponse d'erreur au lieu de laisser Laravel retourner 404
            return response()->json([
                'error' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter en CSV
     */
    protected function exportToCsv($data, $columns, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, array_values($columns));
            
            // Données
            foreach ($data as $row) {
                $csvRow = [];
                foreach (array_keys($columns) as $key) {
                    $value = $this->getNestedValue($row, $key);
                    $csvRow[] = $this->formatCsvValue($value);
                }
                fputcsv($file, $csvRow);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter en Excel (format CSV avec extension .xls pour compatibilité)
     */
    protected function exportToExcel($data, $columns, $filename)
    {
        // Pour une vraie exportation Excel, utiliser maatwebsite/excel
        // Pour l'instant, on retourne un CSV avec extension .xls
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}.xls\"",
        ];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, array_values($columns), "\t");
            
            // Données
            foreach ($data as $row) {
                $excelRow = [];
                foreach (array_keys($columns) as $key) {
                    $value = $this->getNestedValue($row, $key);
                    $excelRow[] = $this->formatCsvValue($value);
                }
                fputcsv($file, $excelRow, "\t");
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter en PDF avec dompdf
     */
    protected function exportToPdf($data, $columns, $filename)
    {
        try {
            $dompdf = new Dompdf();
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf->setOptions($options);
            
            // Générer le HTML
            $html = $this->generatePdfHtml($data, $columns, $filename);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}.pdf\"");
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'export PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback vers CSV en cas d'erreur
            return $this->exportToCsv($data, $columns, $filename);
        }
    }
    
    /**
     * Générer le HTML pour le PDF
     */
    protected function generatePdfHtml($data, $columns, $filename)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        h1 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #dee2e6;
            padding: 6px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Export: ' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '</h1>
        <p>Date d\'export: ' . now()->format('d/m/Y à H:i') . '</p>
    </div>
    <table>
        <thead>
            <tr>';
        
        foreach ($columns as $label) {
            $html .= '<th>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        
        $html .= '</tr>
        </thead>
        <tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach (array_keys($columns) as $key) {
                $value = $this->getNestedValue($row, $key);
                $formattedValue = $this->formatPdfValue($value);
                $html .= '<td>' . htmlspecialchars($formattedValue, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
    </table>
    <div class="footer">
        <p>Généré le ' . now()->format('d/m/Y à H:i') . ' - Total: ' . $data->count() . ' élément(s)</p>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Formater une valeur pour PDF
     */
    protected function formatPdfValue($value)
    {
        if (is_null($value)) {
            return '—';
        }
        
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }
        
        if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
            return $value->format('d/m/Y H:i');
        }
        
        return (string) $value;
    }

    /**
     * Obtenir une valeur imbriquée (support des relations comme category.name)
     */
    protected function getNestedValue($row, $key)
    {
        if (is_array($row)) {
            return data_get($row, $key, '');
        }
        
        // Support des clés imbriquées comme "category.name"
        if (strpos($key, '.') !== false) {
            return data_get($row, $key, '');
        }
        
        return $row->$key ?? '';
    }
    
    /**
     * Formater une valeur pour CSV
     */
    protected function formatCsvValue($value)
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }
        
        if ($value instanceof \DateTime || $value instanceof \Carbon\Carbon) {
            return $value->format('d/m/Y H:i:s');
        }
        
        return (string) $value;
    }
}

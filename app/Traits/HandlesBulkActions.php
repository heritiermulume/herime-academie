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
        $format = $request->get('format', 'csv');
        $ids = $request->get('ids');
        
        // Filtrer par IDs si fournis
        if ($ids) {
            $idsArray = explode(',', $ids);
            $query->whereIn('id', $idsArray);
        }
        
        $data = $query->get();
        
        if ($format === 'excel') {
            return $this->exportToExcel($data, $columns, $filename);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($data, $columns, $filename);
        } else {
            return $this->exportToCsv($data, $columns, $filename);
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
                    $value = is_array($row) ? ($row[$key] ?? '') : $row->$key ?? '';
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
                    $value = is_array($row) ? ($row[$key] ?? '') : $row->$key ?? '';
                    $excelRow[] = $this->formatCsvValue($value);
                }
                fputcsv($file, $excelRow, "\t");
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporter en PDF (basique - pour une vraie exportation PDF, utiliser dompdf ou similar)
     */
    protected function exportToPdf($data, $columns, $filename)
    {
        // Pour l'instant, on retourne un CSV
        // TODO: Implémenter une vraie exportation PDF si nécessaire
        return $this->exportToCsv($data, $columns, $filename);
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

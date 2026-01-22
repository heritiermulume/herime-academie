# Implémentation de la Sélection Multiple et Actions en Lot

## ✅ Composants créés

### 1. Composant Blade réutilisable
- **Fichier**: `resources/views/components/admin/bulk-actions.blade.php`
- **Usage**: `<x-admin.bulk-actions tableId="..." exportRoute="..." :actions="[...]" />`
- **Fonctionnalités**:
  - Barre d'actions flottante avec compteur
  - Boutons d'actions personnalisables
  - Menu d'export (CSV, Excel, PDF)
  - Design responsive

### 2. Script JavaScript
- **Fichier**: `public/js/bulk-actions.js`
- **Fonctionnalités**:
  - Gestion de la sélection multiple
  - Checkbox "Sélectionner tout"
  - Actions en lot via AJAX
  - Export avec filtres préservés
  - Interface utilisateur réactive

### 3. Trait PHP
- **Fichier**: `app/Traits/HandlesBulkActions.php`
- **Méthodes**:
  - `handleBulkAction()` - Traite les actions en lot
  - `bulkDelete()` - Suppression en lot
  - `bulkUpdate()` - Mise à jour en lot
  - `exportData()` - Export générique
  - `exportToCsv()`, `exportToExcel()`, `exportToPdf()` - Formats d'export

## ✅ Routes ajoutées

### Admin
- `POST /admin/orders/bulk-action` - Actions en lot sur les commandes
- `GET /admin/orders/export` - Export des commandes (déjà existant, amélioré)
- `POST /admin/users/bulk-action` - Actions en lot sur les utilisateurs
- `GET /admin/users/export` - Export des utilisateurs
- `POST /admin/contents/bulk-action` - Actions en lot sur les contenus
- `GET /admin/contents/export` - Export des contenus

### Prestataire
- `POST /provider/contents/bulk-action` - Actions en lot sur les contenus
- `GET /provider/contents/export` - Export des contenus
- `GET /provider/customers/export` - Export des clients

### Ambassadeur
- `POST /admin/ambassadors/bulk-action` - Actions en lot sur les ambassadeurs
- `GET /admin/ambassadors/export` - Export des ambassadeurs
- `POST /admin/ambassadors/applications/bulk-action` - Actions en lot sur les candidatures
- `GET /admin/ambassadors/applications/export` - Export des candidatures
- `POST /admin/ambassadors/commissions/bulk-action` - Actions en lot sur les commissions
- `GET /admin/ambassadors/commissions/export` - Export des commissions

## ✅ Pages intégrées

### Admin
- ✅ `/admin/orders` - Commandes (complète avec checkboxes et barre d'actions)

### À intégrer
- ⏳ `/admin/users` - Utilisateurs
- ⏳ `/admin/contents` - Contenus
- ⏳ `/admin/ambassadors` - Ambassadeurs
- ⏳ `/admin/ambassadors/applications` - Candidatures
- ⏳ `/admin/ambassadors/commissions` - Commissions

### Prestataire
- ⏳ `/provider/contents` - Contenus
- ⏳ `/provider/customers` - Clients

## 📝 Guide d'intégration

### 1. Dans la vue Blade

```blade
@push('before-content')
    <x-admin.bulk-actions 
        tableId="maTable"
        :exportRoute="route('ma.route.export')"
        :exportFormats="['csv', 'excel']"
        :actions="[
            'delete' => [
                'label' => 'Supprimer',
                'icon' => 'fa-trash',
                'class' => 'btn-danger',
                'confirm' => true,
                'confirmMessage' => 'Confirmer la suppression ?',
                'route' => route('ma.route.bulk-action'),
                'method' => 'POST'
            ]
        ]"
    />
@endpush

<table id="maTable" data-bulk-select="true" data-export-route="{{ route('ma.route.export') }}">
    <thead>
        <tr>
            <th>
                <input type="checkbox" data-select-all data-table-id="maTable">
            </th>
            <!-- autres colonnes -->
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>
                <input type="checkbox" data-item-id="{{ $item->id }}">
            </td>
            <!-- autres colonnes -->
        </tr>
        @endforeach
    </tbody>
</table>

@push('scripts')
<script src="{{ asset('js/bulk-actions.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    bulkActions.init('maTable', {
        exportRoute: '{{ route('ma.route.export') }}'
    });
});
</script>
@endpush
```

### 2. Dans le contrôleur

```php
use App\Traits\HandlesBulkActions;

class MonController extends Controller
{
    use HandlesBulkActions;
    
    public function bulkAction(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                // Logique personnalisée
            },
            'activate' => function($ids) {
                return $this->bulkUpdate($ids, MonModel::class, ['is_active' => true]);
            }
        ];
        
        return $this->handleBulkAction($request, MonModel::class, $actions);
    }
    
    public function export(Request $request)
    {
        $query = MonModel::query();
        
        // Appliquer les filtres
        // ...
        
        $columns = [
            'id' => 'ID',
            'name' => 'Nom',
            // ...
        ];
        
        return $this->exportData($request, $query, $columns, 'mon-export');
    }
}
```

## 🎯 Actions disponibles par défaut

- `delete` - Supprimer
- `activate` - Activer (is_active = true)
- `deactivate` - Désactiver (is_active = false)
- `publish` - Publier (is_published = true)
- `unpublish` - Dépublier (is_published = false)

## 📦 Formats d'export

- **CSV** - Format texte avec séparateur virgule
- **Excel** - Format tabulé (.xls)
- **PDF** - (À implémenter avec dompdf si nécessaire)

## 🔧 Personnalisation

Les actions peuvent être personnalisées dans chaque contrôleur en passant un tableau de callbacks à `handleBulkAction()`.

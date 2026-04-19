<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Délai de paiement des factures d'abonnement (création → échéance)
    |--------------------------------------------------------------------------
    |
    | Passé ce délai, une facture encore « pending » est traitée comme en retard
    | par failOverduePendingInvoices (statut failed, abonnement past_due ou expired).
    |
    */
    'invoice_due_minutes' => (int) env('SUBSCRIPTION_INVOICE_DUE_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Ancien cache « visite web » (middleware retiré : traitement via scheduler + file)
    |--------------------------------------------------------------------------
    |
    | Conservés pour compatibilité si du code ou des déploiements y font encore référence.
    |
    */
    'process_renewals_visit_cache_seconds' => (int) env('SUBSCRIPTION_RENEWALS_VISIT_CACHE_SECONDS', 600),

    'process_renewals_admin_visit_cache_seconds' => (int) env('SUBSCRIPTION_RENEWALS_ADMIN_VISIT_CACHE_SECONDS', 600),

];

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
    | Cache entre deux passages de processRenewalsForUser() (client connecté)
    |--------------------------------------------------------------------------
    |
    | Limite la charge serveur sur les GET. Défaut 10 minutes (aligné commandes Moneroo).
    | Une facture peut donc être traitée en « en retard » jusqu’à ce délai après son due_at.
    |
    */
    'process_renewals_visit_cache_seconds' => (int) env('SUBSCRIPTION_RENEWALS_VISIT_CACHE_SECONDS', 600),

    /*
    |--------------------------------------------------------------------------
    | Cache entre deux processRenewals() globaux déclenchés par un admin (GET)
    |--------------------------------------------------------------------------
    */
    'process_renewals_admin_visit_cache_seconds' => (int) env('SUBSCRIPTION_RENEWALS_ADMIN_VISIT_CACHE_SECONDS', 600),

];

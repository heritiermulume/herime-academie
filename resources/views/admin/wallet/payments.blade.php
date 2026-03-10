@extends('layouts.admin')

@section('title', 'Wallet - Paiements')
@section('admin-title', 'Wallet - Paiements')
@section('admin-subtitle', 'Initier un payout et consulter les paiements effectués')

@section('admin-content')
    @include('admin.wallet.partials.tabs')

    <section class="admin-panel">
        <div class="admin-panel__header">
            <h3><i class="fas fa-paper-plane me-2"></i>Initier un payout</h3>
        </div>
        <div class="admin-panel__body">
            <form action="{{ route('admin.wallet.payout.store') }}" method="POST" class="row g-3" id="payout-form">
                @csrf
                <div class="col-12 col-md-6">
                    <label for="payout_source_currency" class="form-label">Portefeuille source <span class="text-danger">*</span></label>
                    <select class="form-select" id="payout_wallet_id" name="source_currency" required>
                        <option value="">Choisir un portefeuille</option>
                        @foreach($walletsByCurrency as $item)
                            @php $avail = (float) ($item['available_balance'] ?? 0); @endphp
                            <option value="{{ $item['currency'] }}" data-currency="{{ $item['currency'] }}" data-available="{{ $avail }}">Portefeuille {{ $item['currency'] }} — Solde : {{ number_format($avail, 2) }} {{ $item['currency'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label for="payout_amount" class="form-label">Montant <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="{{ \App\Models\Setting::get('wallet_minimum_payout_amount', 5) }}" class="form-control" id="payout_amount" name="amount" value="{{ old('amount') }}" required disabled>
                    <div class="invalid-feedback" id="payout_amount_feedback">Le montant ne peut pas dépasser le solde disponible du portefeuille sélectionné.</div>
                </div>
                <div class="col-12 col-md-6">
                    <label for="payout_account_id" class="form-label">Compte bénéficiaire (optionnel)</label>
                    <select class="form-select" id="payout_account_id" name="payout_account_id" disabled>
                        <option value="">Saisie manuelle</option>
                        @foreach($payoutAccounts as $acc)
                            <option value="{{ $acc->id }}" data-currency="{{ $acc->currency ?? '' }}" data-country="{{ $acc->country_code ?? '' }}" data-method="{{ $acc->method ?? '' }}" data-phone="{{ $acc->phone ?? '' }}">{{ $acc->name }} — {{ $acc->phone }} ({{ $acc->currency }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label for="payout_currency" class="form-label">Devise <span class="text-danger">*</span></label>
                    <input type="text" class="form-control text-uppercase" id="payout_currency" name="currency" value="{{ old('currency', 'USD') }}" maxlength="3" required disabled>
                </div>
                <div class="col-12 col-md-4">
                    <label for="payout_country" class="form-label">Pays (si manuel)</label>
                    <select class="form-select" id="payout_country" name="country" disabled>
                        <option value="">—</option>
                        @foreach($monerooData['countries'] as $c)
                            <option value="{{ $c['code'] }}">{{ $c['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="payout_method" class="form-label">Opérateur (si manuel)</label>
                    <select class="form-select" id="payout_method" name="method" disabled>
                        <option value="">—</option>
                        @foreach($monerooData['providers'] as $p)
                            <option value="{{ $p['code'] }}" data-country="{{ $p['country'] ?? '' }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="payout_phone" class="form-label">Téléphone (si manuel)</label>
                    <input type="text" class="form-control" id="payout_phone" name="phone" value="{{ old('phone') }}" placeholder="+243..." disabled>
                </div>
                <div class="col-12">
                    <label for="payout_description" class="form-label">Description (optionnel)</label>
                    <input type="text" class="form-control" id="payout_description" name="description" value="{{ old('description') }}" disabled>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" id="payout_submit_btn" disabled>Initier le payout</button>
                </div>
            </form>
        </div>
    </section>

    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-edit me-2"></i>Enregistrer un retrait manuel</h3>
        </div>
        <div class="admin-panel__body">
            <p class="text-muted small mb-3">Enregistrer une transaction de retrait sans passer par Moneroo (ex. virement manuel, espèce). Le solde du portefeuille principal sélectionné sera débité.</p>
            <form action="{{ route('admin.wallet.payout.manual.store') }}" method="POST" class="row g-3" id="manual-payout-form">
                @csrf
                <div class="col-12 col-md-4">
                    <label for="manual_wallet_id" class="form-label">Portefeuille <span class="text-danger">*</span></label>
                    <select class="form-select" id="manual_wallet_id" name="source_currency" required>
                        <option value="">Choisir un portefeuille</option>
                        @foreach($walletsByCurrency as $item)
                            @php $mAvail = (float) ($item['available_balance'] ?? 0); @endphp
                            <option value="{{ $item['currency'] }}" data-currency="{{ $item['currency'] }}" data-available="{{ $mAvail }}" {{ old('source_currency') == $item['currency'] ? 'selected' : '' }}>Portefeuille {{ $item['currency'] }} — Solde : {{ number_format($mAvail, 2) }} {{ $item['currency'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="manual_amount" class="form-label">Montant <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="manual_amount" name="amount" value="{{ old('amount') }}" required>
                    <div class="invalid-feedback" id="manual_amount_feedback">Le montant ne peut pas dépasser le solde disponible du portefeuille.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label for="manual_description" class="form-label">Description (optionnel)</label>
                    <input type="text" class="form-control" id="manual_description" name="description" value="{{ old('description') }}" placeholder="Ex. Virement manuel">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary" id="manual_payout_submit_btn" disabled><i class="fas fa-save me-1"></i>Enregistrer le retrait</button>
                </div>
            </form>
        </div>
    </section>

    <section class="admin-panel mt-4">
        <div class="admin-panel__header">
            <h3><i class="fas fa-list me-2"></i>Paiements effectués</h3>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Portefeuille</th>
                            <th>Montant</th>
                            <th>Méthode</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payouts as $p)
                            <tr>
                                <td>{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $p->wallet && $p->wallet->user ? $p->wallet->user->name : '—' }}</td>
                                <td>{{ number_format($p->amount, 2) }} {{ $p->currency }}</td>
                                <td>{{ $p->method }}</td>
                                <td>{!! $p->status_badge !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center">Aucun payout.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $payouts->links() }}
        </div>
    </section>

    <script>
    (function() {
        var minPayout = {{ (float) \App\Models\Setting::get('wallet_minimum_payout_amount', 5) }};

        // ——— Initier un payout : champs et ordre ———
        var walletSelect = document.getElementById('payout_wallet_id');
        var amountInput = document.getElementById('payout_amount');
        var accountSelect = document.getElementById('payout_account_id');
        var currencyInput = document.getElementById('payout_currency');
        var countrySelect = document.getElementById('payout_country');
        var methodSelect = document.getElementById('payout_method');
        var phoneInput = document.getElementById('payout_phone');
        var descriptionInput = document.getElementById('payout_description');
        var submitBtn = document.getElementById('payout_submit_btn');

        var allProviderOptions = [];
        if (methodSelect) {
            for (var i = 1; i < methodSelect.options.length; i++) {
                allProviderOptions.push({
                    el: methodSelect.options[i],
                    country: methodSelect.options[i].getAttribute('data-country') || ''
                });
            }
        }

        function filterMethodsByCountry(countryCode) {
            if (!methodSelect) return;
            var currentValue = methodSelect.value;
            methodSelect.innerHTML = '<option value="">—</option>';
            allProviderOptions.forEach(function(opt) {
                if (!countryCode || opt.country === countryCode) {
                    methodSelect.appendChild(opt.el.cloneNode(true));
                }
            });
            methodSelect.value = currentValue && methodSelect.querySelector('option[value="' + currentValue + '"]') ? currentValue : '';
        }

        function updatePayoutFieldsState() {
            var walletOk = walletSelect && walletSelect.value !== '';
            var amountVal = amountInput ? parseFloat(amountInput.value) : NaN;
            var availableBalance = 0;
            var walletCurrency = '';
            if (walletSelect && walletSelect.value) {
                var opt = walletSelect.options[walletSelect.selectedIndex];
                if (opt && opt.dataset.available !== undefined) availableBalance = parseFloat(opt.dataset.available) || 0;
                if (opt && opt.dataset.currency) walletCurrency = opt.dataset.currency;
            }
            var amountExceedsBalance = !isNaN(amountVal) && amountVal > availableBalance;
            var amountOk = !isNaN(amountVal) && amountVal >= minPayout && !amountExceedsBalance;
            if (amountInput) {
                if (amountExceedsBalance && walletCurrency) {
                    amountInput.setCustomValidity('Le montant ne peut pas dépasser le solde disponible (' + availableBalance.toFixed(2) + ' ' + walletCurrency + ').');
                    amountInput.classList.add('is-invalid');
                    var fb = document.getElementById('payout_amount_feedback');
                    if (fb) fb.textContent = 'Le montant ne peut pas dépasser le solde disponible du portefeuille (' + availableBalance.toFixed(2) + ' ' + walletCurrency + ').';
                } else {
                    amountInput.setCustomValidity('');
                    amountInput.classList.remove('is-invalid');
                }
            }
            var accountVal = accountSelect ? accountSelect.value : '';
            var accountSelected = accountVal !== '';
            var currencyOk = currencyInput && currencyInput.value.trim().length >= 2;
            var countryVal = countrySelect ? countrySelect.value : '';
            var methodOk = methodSelect && methodSelect.value !== '';
            var phoneVal = phoneInput ? phoneInput.value.trim() : '';
            var phoneOk = phoneVal.length >= 8;

            amountInput.disabled = !walletOk;
            accountSelect.disabled = !walletOk || !amountOk;
            currencyInput.disabled = !walletOk || !amountOk;
            countrySelect.disabled = !walletOk || !amountOk || !currencyOk || accountSelected;
            methodSelect.disabled = !walletOk || !amountOk || !currencyOk || !countryVal || accountSelected;
            phoneInput.disabled = !walletOk || !amountOk || !currencyOk || !countryVal || !methodOk || accountSelected;
            descriptionInput.disabled = !walletOk || !amountOk;

            var canSubmit = walletOk && amountOk && currencyOk;
            if (accountSelected) {
                canSubmit = canSubmit && true;
            } else {
                canSubmit = canSubmit && countryVal && methodOk && phoneOk;
            }
            submitBtn.disabled = !canSubmit;
        }

        if (walletSelect) walletSelect.addEventListener('change', function() {
            var opt = walletSelect.options[walletSelect.selectedIndex];
            if (opt && opt.value && opt.dataset.currency && currencyInput) {
                currencyInput.value = opt.dataset.currency;
            }
            updatePayoutFieldsState();
        });
        if (amountInput) amountInput.addEventListener('input', updatePayoutFieldsState);
        if (accountSelect) accountSelect.addEventListener('change', function() {
            var opt = accountSelect.options[accountSelect.selectedIndex];
            if (opt && opt.value && opt.dataset.currency) {
                currencyInput.value = opt.dataset.currency || '';
                if (countrySelect) countrySelect.value = opt.dataset.country || '';
                if (methodSelect) methodSelect.value = opt.dataset.method || '';
                if (phoneInput) phoneInput.value = opt.dataset.phone || '';
                filterMethodsByCountry(opt.dataset.country || '');
            } else {
                filterMethodsByCountry(countrySelect ? countrySelect.value : '');
            }
            updatePayoutFieldsState();
        });
        if (currencyInput) currencyInput.addEventListener('input', updatePayoutFieldsState);
        if (countrySelect) countrySelect.addEventListener('change', function() {
            filterMethodsByCountry(countrySelect.value);
            if (methodSelect) methodSelect.value = '';
            updatePayoutFieldsState();
        });
        if (methodSelect) methodSelect.addEventListener('change', updatePayoutFieldsState);
        if (phoneInput) phoneInput.addEventListener('input', updatePayoutFieldsState);
        updatePayoutFieldsState();

        // ——— Enregistrer un retrait manuel : bouton activé si portefeuille + montant remplis et montant <= solde ———
        var manualWallet = document.getElementById('manual_wallet_id');
        var manualAmount = document.getElementById('manual_amount');
        var manualSubmit = document.getElementById('manual_payout_submit_btn');

        function updateManualPayoutButton() {
            var w = manualWallet && manualWallet.value !== '';
            var amountVal = manualAmount ? parseFloat(manualAmount.value) : NaN;
            var a = !isNaN(amountVal) && amountVal >= 0.01;
            var availableBalance = 0;
            var walletCurrency = '';
            if (manualWallet && manualWallet.value) {
                var opt = manualWallet.options[manualWallet.selectedIndex];
                if (opt && opt.dataset.available !== undefined) availableBalance = parseFloat(opt.dataset.available) || 0;
                if (opt && opt.dataset.currency) walletCurrency = opt.dataset.currency;
            }
            var amountExceedsBalance = a && amountVal > availableBalance;
            a = a && !amountExceedsBalance;
            if (manualAmount) {
                if (amountExceedsBalance && walletCurrency) {
                    manualAmount.setCustomValidity('Le montant ne peut pas dépasser le solde disponible (' + availableBalance.toFixed(2) + ' ' + walletCurrency + ').');
                    manualAmount.classList.add('is-invalid');
                    var mFb = document.getElementById('manual_amount_feedback');
                    if (mFb) mFb.textContent = 'Le montant ne peut pas dépasser le solde disponible du portefeuille (' + availableBalance.toFixed(2) + ' ' + walletCurrency + ').';
                } else {
                    manualAmount.setCustomValidity('');
                    manualAmount.classList.remove('is-invalid');
                }
            }
            manualSubmit.disabled = !(w && a);
        }

        if (manualWallet) manualWallet.addEventListener('change', updateManualPayoutButton);
        if (manualAmount) manualAmount.addEventListener('input', updateManualPayoutButton);
        updateManualPayoutButton();
    })();
    </script>
@endsection

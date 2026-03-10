@extends('layouts.admin')

@section('title', 'Wallet - Comptes')
@section('admin-title', 'Wallet - Comptes')
@section('admin-subtitle', 'Comptes de paiement qui recevront les payouts')

@section('admin-content')
    @include('admin.wallet.partials.tabs')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="admin-panel">
        <div class="admin-panel__header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h3 class="mb-0"><i class="fas fa-university me-2"></i>Liste des comptes</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                <i class="fas fa-plus me-2"></i>Ajouter un compte
            </button>
        </div>
        <div class="admin-panel__body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Pays</th>
                            <th>Opérateur</th>
                            <th>Téléphone</th>
                            <th>Devise</th>
                            <th>Par défaut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>{{ $account->name }}</td>
                                <td>{{ $account->country_code }}</td>
                                <td>{{ $account->method }}</td>
                                <td>{{ $account->phone }}</td>
                                <td>{{ $account->currency }}</td>
                                <td>
                                    @if($account->is_default)
                                        <span class="badge bg-success">Oui</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.wallet.accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce compte ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted text-center">Aucun compte configuré. Cliquez sur « Ajouter un compte ».</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- Modal Ajouter un compte --}}
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.wallet.accounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountModalLabel"><i class="fas fa-plus me-2"></i>Ajouter un compte de paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Les pays et opérateurs proviennent du fournisseur de paiement (Moneroo). Remplissez les champs dans l’ordre pour activer les suivants.</p>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="account_name" class="form-label">Nom du compte <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="account_name" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Ex: Compte principal M-Pesa">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="account_country" class="form-label">Pays <span class="text-danger">*</span></label>
                                <select class="form-select" id="account_country" name="country" required disabled>
                                    <option value="">Choisir un pays</option>
                                    @foreach($monerooData['countries'] as $c)
                                        <option value="{{ $c['code'] }}" {{ old('country') === $c['code'] ? 'selected' : '' }}>{{ $c['name'] }} ({{ $c['code'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="account_method" class="form-label">Opérateur <span class="text-danger">*</span></label>
                                <select class="form-select" id="account_method" name="method" required disabled>
                                    <option value="">Choisir un opérateur</option>
                                    @php
                                        $providerCurrenciesMap = collect($monerooData['providers'] ?? [])->mapWithKeys(function ($p) {
                                            $currencies = $p['currencies'] ?? (isset($p['currency']) && $p['currency'] !== '' ? [$p['currency']] : []);
                                            return [$p['code'] => array_values($currencies)];
                                        })->toArray();
                                    @endphp
                                    @foreach($monerooData['providers'] as $p)
                                        @php
                                            $currencies = $p['currencies'] ?? (isset($p['currency']) && $p['currency'] !== '' ? [$p['currency']] : []);
                                        @endphp
                                        <option value="{{ $p['code'] }}" data-country="{{ $p['country'] ?? '' }}" data-currency="{{ $p['currency'] ?? '' }}" data-currencies="{{ json_encode(array_values($currencies)) }}" {{ old('method') === $p['code'] ? 'selected' : '' }}>{{ $p['name'] }} ({{ $p['code'] }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="account_phone" class="form-label">Numéro (pour recevoir le payout) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="account_phone" name="phone" value="{{ old('phone') }}" required placeholder="+243..." disabled>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="account_currency" class="form-label">Devise <span class="text-danger">*</span></label>
                                <select class="form-select" id="account_currency" name="currency" required disabled>
                                    <option value="">Choisir une devise</option>
                                </select>
                                <input type="hidden" id="account_provider_currencies" value="{{ json_encode($providerCurrenciesMap ?? []) }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="account_recipient_first_name" class="form-label">Prénom du bénéficiaire</label>
                                <input type="text" class="form-control" id="account_recipient_first_name" name="recipient_first_name" value="{{ old('recipient_first_name') }}" maxlength="100" disabled>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="account_recipient_last_name" class="form-label">Nom du bénéficiaire</label>
                                <input type="text" class="form-control" id="account_recipient_last_name" name="recipient_last_name" value="{{ old('recipient_last_name') }}" maxlength="100" disabled>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_default" value="1" id="account_is_default" {{ old('is_default') ? 'checked' : '' }} disabled>
                                    <label class="form-check-label" for="account_is_default">Définir comme compte par défaut</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="account_submit_btn" disabled>Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var nameInput = document.getElementById('account_name');
            var countrySelect = document.getElementById('account_country');
            var methodSelect = document.getElementById('account_method');
            var phoneInput = document.getElementById('account_phone');
            var currencySelect = document.getElementById('account_currency');
            var recipientFirst = document.getElementById('account_recipient_first_name');
            var recipientLast = document.getElementById('account_recipient_last_name');
            var isDefaultCheck = document.getElementById('account_is_default');
            var submitBtn = document.getElementById('account_submit_btn');
            var modalEl = document.getElementById('addAccountModal');

            var allMethodOptions = [];
            if (methodSelect) {
                for (var i = 1; i < methodSelect.options.length; i++) {
                    var o = methodSelect.options[i];
                    allMethodOptions.push({ el: o.cloneNode(true), country: o.getAttribute('data-country') || '', currency: o.getAttribute('data-currency') || '', currencies: o.getAttribute('data-currencies') || '[]' });
                }
            }
            var providerCurrenciesMap = {};
            try {
                var hidden = document.getElementById('account_provider_currencies');
                if (hidden && hidden.value) providerCurrenciesMap = JSON.parse(hidden.value);
            } catch (e) {}

            function updateCurrencyOptionsByMethod(methodCode) {
                if (!currencySelect) return;
                var currencies = (methodCode && providerCurrenciesMap[methodCode]) ? providerCurrenciesMap[methodCode] : [];
                var currentVal = currencySelect.value;
                currencySelect.innerHTML = '<option value="">Choisir une devise</option>';
                if (Array.isArray(currencies) && currencies.length > 0) {
                    currencies.forEach(function(cur) {
                        var opt = document.createElement('option');
                        opt.value = cur;
                        opt.textContent = cur;
                        currencySelect.appendChild(opt);
                    });
                    if (currencies.length === 1) {
                        currencySelect.value = currencies[0];
                    } else if (currentVal && currencies.indexOf(currentVal) !== -1) {
                        currencySelect.value = currentVal;
                    }
                }
            }

            function filterMethodOptionsByCountry(countryCode) {
                if (!methodSelect) return;
                var currentVal = methodSelect.value;
                methodSelect.innerHTML = '<option value="">Choisir un opérateur</option>';
                allMethodOptions.forEach(function(opt) {
                    if (!countryCode || opt.country === countryCode) {
                        var clone = opt.el.cloneNode(true);
                        clone.setAttribute('data-country', opt.country);
                        clone.setAttribute('data-currency', opt.currency);
                        clone.setAttribute('data-currencies', opt.currencies);
                        methodSelect.appendChild(clone);
                    }
                });
                methodSelect.value = (currentVal && methodSelect.querySelector('option[value="' + currentVal + '"]')) ? currentVal : '';
            }

            function setCurrencyFromMethod() {
                var opt = methodSelect && methodSelect.options[methodSelect.selectedIndex];
                if (opt && opt.getAttribute('data-currency') && currencySelect) {
                    var cur = opt.getAttribute('data-currency');
                    if (cur && currencySelect.querySelector('option[value="' + cur + '"]')) {
                        currencySelect.value = cur;
                    }
                }
            }

            function updateAccountModalState() {
                var nameOk = nameInput && nameInput.value.trim() !== '';
                var countryOk = countrySelect && countrySelect.value !== '';
                var methodOk = methodSelect && methodSelect.value !== '';
                var phoneVal = phoneInput ? phoneInput.value.trim() : '';
                var phoneOk = phoneVal.length >= 10 && /^\+?[0-9]{10,15}$/.test(phoneVal);
                var currencyOk = currencySelect && currencySelect.value !== '';

                countrySelect.disabled = !nameOk;
                methodSelect.disabled = !nameOk || !countryOk;
                phoneInput.disabled = !nameOk || !countryOk || !methodOk;
                currencySelect.disabled = !nameOk || !countryOk || !methodOk;
                if (recipientFirst) recipientFirst.disabled = !nameOk || !countryOk || !methodOk || !phoneOk || !currencyOk;
                if (recipientLast) recipientLast.disabled = !nameOk || !countryOk || !methodOk || !phoneOk || !currencyOk;
                if (isDefaultCheck) isDefaultCheck.disabled = !nameOk || !countryOk || !methodOk || !phoneOk || !currencyOk;
                submitBtn.disabled = !(nameOk && countryOk && methodOk && phoneOk && currencyOk);
            }

            if (nameInput) nameInput.addEventListener('input', updateAccountModalState);
            if (countrySelect) countrySelect.addEventListener('change', function() {
                filterMethodOptionsByCountry(countrySelect.value);
                if (methodSelect) methodSelect.value = '';
                updateCurrencyOptionsByMethod('');
                updateAccountModalState();
            });
            if (methodSelect) methodSelect.addEventListener('change', function() {
                updateCurrencyOptionsByMethod(methodSelect.value);
                setCurrencyFromMethod();
                updateAccountModalState();
            });
            if (phoneInput) phoneInput.addEventListener('input', updateAccountModalState);
            if (currencySelect) currencySelect.addEventListener('change', updateAccountModalState);

            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function() {
                    filterMethodOptionsByCountry(countrySelect ? countrySelect.value : '');
                    updateCurrencyOptionsByMethod(methodSelect ? methodSelect.value : '');
                    updateAccountModalState();
                });
            }
            filterMethodOptionsByCountry(countrySelect ? countrySelect.value : '');
            updateCurrencyOptionsByMethod(methodSelect ? methodSelect.value : '');
            updateAccountModalState();
        });
    </script>
    @endpush
@endsection

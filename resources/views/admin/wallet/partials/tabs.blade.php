@php
    $currentRoute = request()->route()->getName();
@endphp
<nav class="nav nav-tabs mb-4 admin-wallet-tabs" role="tablist" style="--bs-nav-link-color: #0b1f3a; --bs-nav-tabs-link-active-bg: #0b1f3a; --bs-nav-tabs-link-active-color: #fff;">
    <a href="{{ route('admin.wallet.index') }}" class="nav-link text-nowrap {{ $currentRoute === 'admin.wallet.index' ? 'active' : '' }}">
        <i class="fas fa-home me-2"></i><span>Tableau de bord</span>
    </a>
    <a href="{{ route('admin.wallet.balance') }}" class="nav-link text-nowrap {{ $currentRoute === 'admin.wallet.balance' ? 'active' : '' }}">
        <i class="fas fa-balance-scale me-2"></i><span>Solde</span>
    </a>
    <a href="{{ route('admin.wallet.accounts') }}" class="nav-link text-nowrap {{ $currentRoute === 'admin.wallet.accounts' ? 'active' : '' }}">
        <i class="fas fa-university me-2"></i><span>Comptes</span>
    </a>
    <a href="{{ route('admin.wallet.payments') }}" class="nav-link text-nowrap {{ $currentRoute === 'admin.wallet.payments' ? 'active' : '' }}">
        <i class="fas fa-money-bill-wave me-2"></i><span>Paiements</span>
    </a>
    <a href="{{ route('admin.wallet.config') }}" class="nav-link text-nowrap {{ $currentRoute === 'admin.wallet.config' ? 'active' : '' }}">
        <i class="fas fa-cog me-2"></i><span>Configuration</span>
    </a>
</nav>
<style>
.admin-wallet-tabs {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
}
.admin-wallet-tabs .nav-link {
    white-space: nowrap;
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
}
</style>

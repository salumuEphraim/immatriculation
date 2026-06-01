<x-app-layout>
    <x-slot name="header">
        <div class="owner-infractions-pro-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-1 fw-bold text-white">
                    <i class="bi bi-receipt-cutoff me-2"></i>Mes infractions et amendes
                </h2>
                <p class="small mb-0 text-white-50">Suivi des amendes liees a vos vehicules</p>
            </div>
            <span class="owner-infractions-pro-count">
                {{ $infractions->count() }} au total
            </span>
        </div>
    </x-slot>

    <div class="py-4 owner-infractions-pro">
        <div class="container-fluid px-0">
            <div class="owner-infractions-pro-note mb-4">
                <p class="small mb-0">
                    <strong>Information :</strong> seules les amendes validees peuvent etre payees en Mobile Money.
                    @if(config('mobile_money.driver') === 'shwary')
                        <span class="d-block mt-1"><strong>Shwary :</strong> saisissez votre numero au format +243, puis confirmez la demande sur votre telephone.</span>
                    @elseif(config('mobile_money.driver') === 'flexpay')
                        <span class="d-block mt-1"><strong>Flexpay :</strong> confirmez sur votre telephone pour finaliser le paiement.</span>
                    @elseif(config('mobile_money.driver') === 'mock')
                        <span class="d-block mt-1">Mode demo actif : validation immediate sans operateur reel.</span>
                    @endif
                </p>
            </div>

            <div class="owner-infractions-pro-card">
                <div class="p-4 p-md-5">
                    @if($infractions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-shield-check fs-1 text-success"></i>
                            <p class="mt-2 mb-0 text-muted">Felicitations, vous n'avez aucune infraction en cours.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 owner-infractions-pro-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Vehicule</th>
                                        <th>Infraction</th>
                                        <th>Montant</th>
                                        <th>Statut et recu</th>
                                        <th>Paiement Mobile Money</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($infractions as $infraction)
                                        <tr>
                                            <td>
                                                {{ $infraction->date_infraction?->format('d/m/Y') ?? $infraction->created_at->format('d/m/Y') }}
                                                <span class="d-block small text-muted">{{ $infraction->date_infraction?->format('H:i') ?? $infraction->created_at->format('H:i') }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold font-monospace text-dark">{{ $infraction->vehicule->plaque ?? 'N/A' }}</div>
                                                <div class="small text-muted">{{ $infraction->vehicule->marque }} {{ $infraction->vehicule->modele }}</div>
                                            </td>
                                            <td>
                                                <span class="owner-infractions-pro-type">{{ $infraction->type }}</span>
                                            </td>
                                            <td class="fw-bold text-dark">
                                                {{ number_format($infraction->montant, 0, ',', ' ') }} CDF
                                            </td>
                                            <td>
                                                @php $status = $infraction->statut ?? 'en_attente'; @endphp
                                                @if($status === 'validee' || $status === 'payee')
                                                    <span class="owner-infractions-pro-pill is-ok">
                                                        {{ $status === 'validee' ? 'Validee' : 'Payee' }}
                                                    </span>
                                                @elseif($status === 'en_attente')
                                                    <span class="owner-infractions-pro-pill is-wait">En attente</span>
                                                @else
                                                    <span class="owner-infractions-pro-pill">{{ ucfirst($status) }}</span>
                                                @endif
                                                @if($infraction->code_unique)
                                                    <div class="mt-1">
                                                        <a href="{{ route('agent.infractions.recu', $infraction->id) }}" class="owner-infractions-pro-link" target="_blank">
                                                            Voir recu
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($infraction->reference_paiement)
                                                    <div class="mt-1 small text-muted">Ref : {{ $infraction->reference_paiement }}</div>
                                                @endif
                                            </td>
                                            <td class="align-top">
                                                @php
                                                    $rowStatus = $infraction->statut ?? 'en_attente';
                                                    $enAttentePaiement = ($infraction->paiement_statut ?? null) === 'initiated' && !($infraction->est_payee ?? false);
                                                    $peutPayer = $rowStatus === 'validee' && !($infraction->est_payee ?? false) && !$enAttentePaiement;
                                                @endphp

                                                @if($enAttentePaiement)
                                                    <div class="owner-infractions-pro-pay-state is-wait">
                                                        <i class="bi bi-phone-vibrate me-1"></i>
                                                        Paiement en cours, validez sur votre telephone.
                                                    </div>
                                                @elseif($peutPayer)
                                                    @php
                                                        $paymentDriver = config('mobile_money.driver');
                                                        $paymentAmount = (int) round((float) $infraction->montant);
                                                        $minShwaryAmount = (int) config('mobile_money.shwary.min_amount_cdf', 2900);
                                                    @endphp
                                                    <form action="{{ route('proprietaire.infractions.payer', $infraction) }}" method="post" class="owner-infractions-pro-pay-card">
                                                        @csrf
                                                        <div class="owner-pay-card-head">
                                                            <div>
                                                                <span class="owner-pay-provider">
                                                                    <i class="bi bi-shield-lock-fill"></i>
                                                                    {{ $paymentDriver === 'shwary' ? 'Shwary Pay' : 'Mobile Money' }}
                                                                </span>
                                                                <strong>{{ number_format($paymentAmount, 0, ',', ' ') }} CDF</strong>
                                                            </div>
                                                            <i class="bi bi-phone-flip owner-pay-icon"></i>
                                                        </div>

                                                        @if($paymentDriver !== 'shwary')
                                                            <label>Operateur</label>
                                                            <select name="operateur">
                                                                <option value="mpesa">M-Pesa</option>
                                                                <option value="orange">Orange Money</option>
                                                                <option value="money">Airtel Money</option>
                                                                <option value="airtel">Autre</option>
                                                            </select>
                                                        @else
                                                            <input type="hidden" name="operateur" value="shwary">
                                                        @endif

                                                        <label>Numero Mobile Money</label>
                                                        <input type="tel" name="telephone" required inputmode="tel" placeholder="+243 972 345 678" autocomplete="tel">

                                                        <label>Montant exact (CDF)</label>
                                                        <input type="number" name="montant_cdf" required min="{{ $paymentDriver === 'shwary' ? $minShwaryAmount : 1 }}" step="1" value="{{ $paymentAmount }}">

                                                        @if($paymentDriver === 'mock')
                                                            <label>Code secret demo</label>
                                                            <input type="password" name="code_secret" inputmode="numeric" pattern="[0-9]{4,5}" maxlength="5" minlength="4" autocomplete="one-time-code" placeholder="0000">
                                                        @endif

                                                        <div class="owner-pay-safe-line">
                                                            <i class="bi bi-lock-fill"></i>
                                                            <span>Confirmation finale sur telephone. Aucun code secret n'est stocke.</span>
                                                        </div>

                                                        <button type="submit">
                                                            <i class="bi bi-credit-card-2-front-fill me-1"></i>
                                                            Payer maintenant
                                                        </button>
                                                    </form>
                                                    @if($paymentDriver === 'mock')
                                                        <p class="small text-muted mt-1">Mode demo : enregistrement immediat.</p>
                                                    @elseif($paymentDriver === 'shwary')
                                                        <p class="small text-muted mt-1">Shwary enverra une demande de validation sur ce numero.</p>
                                                    @endif
                                                @elseif($rowStatus === 'payee' || ($infraction->est_payee ?? false))
                                                    <span class="owner-infractions-pro-pill is-paid">Reglee</span>
                                                @else
                                                    <span class="small text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="small text-muted mb-0">
                    En cas de contestation, presentez-vous au bureau de la PCR avec vos documents.
                </p>
            </div>
        </div>
    </div>
</x-app-layout>

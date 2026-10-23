@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Buy Direct Settings</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Buy Direct Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                @include('admin.buy-direct._status')

                <form action="/settings/buy-direct" method="POST">
                    @csrf

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title text-white mb-0"><i class="bi bi-hourglass-split me-2"></i>Escrow deadlines</h5>
                        </div>
                        <div class="card-body pt-4">
                            <div class="mb-4">
                                <label class="form-label fw-semibold" for="ship_days">Seller shipping deadline</label>
                                <p class="text-muted small mb-2">Days the seller has to mark a paid order as shipped. After that the order is canceled and the buyer refunded automatically.</p>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="number" id="ship_days" name="buy_direct_ship_deadline_days" class="form-control" style="width:120px;" min="1" max="30"
                                           value="{{ old('buy_direct_ship_deadline_days', $settings['buy_direct_ship_deadline_days']) }}" required>
                                    <span class="text-muted">days after payment</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-semibold" for="release_days">Buyer confirmation window</label>
                                <p class="text-muted small mb-2">Days the buyer has after shipping to confirm receipt or report a problem. If they do neither, the payment is released to the seller automatically.</p>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="number" id="release_days" name="buy_direct_auto_release_days" class="form-control" style="width:120px;" min="1" max="60"
                                           value="{{ old('buy_direct_auto_release_days', $settings['buy_direct_auto_release_days']) }}" required>
                                    <span class="text-muted">days after shipping</span>
                                </div>
                            </div>
                            <p class="small text-muted mb-0 mt-3"><i class="bi bi-info-circle"></i> Changes apply to orders paid or shipped from now on. Existing orders keep the deadline they were given.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title text-white mb-0"><i class="bi bi-percent me-2"></i>Commission (buyer protection fee)</h5>
                        </div>
                        <div class="card-body pt-4">
                            <p class="text-muted small">Added on top of the item price and paid by the buyer. The seller receives the full item price.</p>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <label class="form-label fw-semibold" for="rate">Standard rate</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="rate" name="buy_direct_commission_rate" class="form-control" min="0" max="50"
                                               value="{{ old('buy_direct_commission_rate', $settings['buy_direct_commission_rate']) }}" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label fw-semibold" for="threshold">High-value from</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Br</span>
                                        <input type="number" step="1" id="threshold" name="buy_direct_commission_threshold" class="form-control" min="0"
                                               value="{{ old('buy_direct_commission_threshold', $settings['buy_direct_commission_threshold']) }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <label class="form-label fw-semibold" for="rate_high">High-value rate</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" id="rate_high" name="buy_direct_commission_rate_high" class="form-control" min="0" max="50"
                                               value="{{ old('buy_direct_commission_rate_high', $settings['buy_direct_commission_rate_high']) }}" required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <p class="small text-muted mt-3 mb-0">Items priced at or above the high-value amount use the high-value rate; everything else uses the standard rate.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title text-white mb-0"><i class="bi bi-robot me-2"></i>Automation</h5>
                        </div>
                        <div class="card-body pt-4">
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="buy_direct_auto_refund" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="auto_refund" name="buy_direct_auto_refund" value="1" {{ old('buy_direct_auto_refund', $settings['buy_direct_auto_refund']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="auto_refund">Refund buyers automatically when an order is canceled</label>
                                <div class="small text-muted">Failed refunds are emailed to admin and listed under Buy Direct Refunds for a manual retry.</div>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="buy_direct_auto_payout" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="auto_payout" name="buy_direct_auto_payout" value="1" {{ old('buy_direct_auto_payout', $settings['buy_direct_auto_payout']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="auto_payout">Pay sellers automatically once the payment is released</label>
                                <div class="small text-muted">When off, released orders wait in Pending Settlements for an admin to pay out.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-5">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Save settings</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

@include('admin.layouts.footer')

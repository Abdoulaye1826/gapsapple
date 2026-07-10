<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php
    $isEchange = $sale->isEchange();
    $documentType = $isEchange ? "Bon d'échange" : 'Facture';
    $documentNumber = $isEchange ? $sale->exchange_voucher_number : ($invoice->invoice_number ?? $sale->sale_number);
  @endphp
  <title>GAPS APPLE — {{ $documentType }} {{ $documentNumber }}</title>
  <style>
    /* ============================================================
       Modèle économique en encre : pas de fonds colorés ni de
       dégradés. Uniquement du texte noir/gris sur fond blanc, avec
       une seule couleur d'accent (--ink) réservée aux bordures,
       filets et libellés clés — jamais en aplat. Imprimable en
       noir et blanc sans perte d'information.
       ============================================================ */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    @page {
      margin: 0;
    }

    :root {
      --ink: #8a6f1f;
      --accent: #8a6f1f;
      --accent-dark: #6e5718;
      --text: #1a1a2e;
      --text-muted: #5b6479;
      --line: #c7cad6;
      --line-light: #e3e5ec;
    }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      font-size: 13px;
      color: var(--text);
      background: #fff;
    }

    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      background: #fff;
      position: relative;
    }

    .top-bar { height: 7px; background: var(--accent); }

    /* ── HEADER : fond blanc, simple filet de séparation ── */
    .header {
      padding: 0;
      position: relative;
    }

    /* Table plutôt que flex : DomPDF ne centre pas fiablement les
       éléments flex verticalement, alors qu'il gère très bien
       l'alignement vertical (vertical-align) des cellules de tableau. */
    .header-table { width: 100%; border-collapse: collapse; }
    .header-table td { vertical-align: middle; }
    .header-td-logo { width: 110px; padding: 26px 0 20px 32px; }
    .header-td-doc { padding: 26px 32px 20px 0; }

    .brand-icon {
      width: 82px; height: 82px;
      border-radius: 50%;
      text-align: center;
      border: 2px solid var(--accent);
      overflow: hidden;
    }

    .brand-icon img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .header-doc { text-align: right; }
    .doc-type { color: var(--accent); font-size: 11px; letter-spacing: 3px; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; }
    .doc-number { color: var(--text); font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
    .doc-date { color: var(--text-muted); font-size: 11px; margin-top: 4px; }

    .header-divider { height: 1px; background: var(--line); }

    /* ── META BAND : encart client / dates, fond gris clair ──
       Table plutôt que CSS Grid : DomPDF ne supporte pas display:grid,
       les deux colonnes (client / dates) ne s'alignaient pas côte à côte. */
    .meta-band-wrap {
      margin: 20px 32px 0;
      padding: 18px 24px;
      background: #f7f7f9;
      border-radius: 4px;
    }
    .meta-band { width: 100%; border-collapse: collapse; }
    .meta-band td { vertical-align: top; }
    .meta-td-right { width: 190px; }

    .meta-left h4, .meta-right h4 { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--accent); margin-bottom: 6px; }
    .meta-left p { color: var(--text); font-size: 13px; line-height: 1.6; }
    .meta-left .name { font-size: 14px; font-weight: 700; color: var(--text); }

    .meta-right { text-align: right; }
    /* Table pour les lignes label/valeur : même raison, display:flex
       n'alignait pas ces lignes de façon fiable sous DomPDF. */
    .meta-rows { width: 100%; border-collapse: collapse; }
    .meta-rows td { padding-bottom: 6px; text-align: right; white-space: nowrap; }
    .meta-rows tr:last-child td { padding-bottom: 0; }
    .meta-rows .meta-label { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-muted); padding-right: 16px; width: 100%; }
    .meta-rows .meta-value { font-size: 13px; font-weight: 600; color: var(--text); }

    .status-badge {
      display: inline-block; background: var(--accent); color: #fff;
      font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
      padding: 4px 12px; border-radius: 3px;
    }

    /* ── ITEMS TABLE (facture vente) — sans fond, lignes fines ── */
    .items-section { padding: 0 32px 8px; }

    .items-table { width: 100%; border-collapse: collapse; margin-top: 18px; }
    .items-table thead tr { background: var(--text); }
    .items-table thead th {
      padding: 10px; font-size: 9.5px; font-weight: 700; letter-spacing: 1px;
      text-transform: uppercase; color: #fff; text-align: left;
    }
    .items-table thead th.num    { text-align: center; }
    .items-table thead th.amount { text-align: right; }

    .items-table tbody tr { border-bottom: 1px solid var(--line-light); }
    .items-table tbody td { padding: 9px 10px; color: var(--text); vertical-align: middle; }
    .items-table tbody td.desc { font-weight: 500; }
    .items-table tbody td.desc small { display: block; font-size: 11px; color: var(--text-muted); font-weight: 400; }
    .items-table tbody td.qty   { text-align: center; }
    .items-table tbody td.unit  { text-align: right; }
    .items-table tbody td.total { text-align: right; font-weight: 700; }

    .qty-badge { display: inline-block; border: 1px solid var(--line); border-radius: 4px; padding: 1px 8px; font-size: 12px; font-weight: 600; }

    /* ── ÉCHANGE : PRODUITS — cartes en simple encadré ──
       Table plutôt que CSS Grid : DomPDF ne supporte pas display:grid,
       les deux cartes ne se plaçaient pas correctement côte à côte. */
    .exchange-section { padding: 22px 32px 8px; }
    .exchange-table { width: 100%; border-collapse: collapse; }
    .exchange-table td { vertical-align: top; }
    .exchange-td-card { width: 46%; }
    .exchange-td-arrow { width: 8%; text-align: center; vertical-align: middle; }

    .exchange-card { border: 1px solid var(--line); border-radius: 6px; padding: 14px; }
    .exchange-card h4 { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; }
    .exchange-card .product-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .exchange-card .product-ref { font-size: 11px; color: var(--text-muted); margin-bottom: 10px; }
    .exchange-card .value-row { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--line-light); padding-top: 8px; }
    .exchange-card .value-row .label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
    .exchange-card .value-row .val   { font-size: 15px; font-weight: 700; color: var(--text); }

    /* Icône échange dessinée en CSS (cercle + flèche double reliée par un
       trait) plutôt qu'un caractère Unicode : plus jolie et fiable sous
       DomPDF (les glyphes flèche du sous-ensemble de police intégré ne
       s'affichaient pas). padding-top pousse le cercle au niveau vertical
       des cartes (vertical-align:middle seul ne suffisait pas sous DomPDF). */
    .exchange-td-arrow { padding-top: 56px; }
    .exchange-arrow-circle {
      width: 42px; height: 42px;
      border: 2px solid var(--accent);
      border-radius: 50%;
      position: relative;
      margin: 0 auto;
      background: #fff;
    }
    .arrow-line {
      position: absolute; left: 10px; right: 10px; top: 20px;
      height: 2px;
      background: var(--accent);
    }
    .arrow-tri {
      position: absolute; top: 15px;
      width: 0; height: 0;
      border-top: 5px solid transparent;
      border-bottom: 5px solid transparent;
    }
    .arrow-tri-left  { left: 7px;  border-right: 7px solid var(--accent); }
    .arrow-tri-right { right: 7px; border-left: 7px solid var(--accent); }

    .items-list { margin-top: 8px; }
    .items-list .item-row { display: flex; justify-content: space-between; font-size: 12px; color: var(--text); padding: 2px 0; }
    .items-list .item-row .qty { color: var(--text-muted); }

    /* ── TOTAUX ──
       Même bloc pour le « Total final » (vente) et le « Montant ajouté par le
       client » (échange) : un simple encadré à double filet, sans aplat. */
    /* margin-left:auto plutôt que flex justify-content:flex-end :
       DomPDF ne poussait pas le bloc des totaux à droite, il restait à
       gauche. Les marges automatiques sur un bloc de largeur fixe sont
       fiables sous DomPDF. */
    .totals-row { padding: 14px 32px 8px; }
    .totals-box { width: 290px; margin-left: auto; }

    .totals-line { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--line-light); font-size: 13px; color: var(--text-muted); }
    .totals-line:last-of-type { border-bottom: none; }
    .totals-line .label { font-weight: 500; }
    .totals-line .val   { font-weight: 600; color: var(--text); }

    .totals-grand {
      display: flex; justify-content: space-between; align-items: center;
      border-top: 2px solid var(--text); border-bottom: 2px solid var(--text);
      padding: 10px 4px; margin-top: 8px;
    }
    .totals-grand .label { font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: var(--text); }
    .totals-grand .val   { font-size: 18px; font-weight: 700; color: var(--text); }

    /* ── MONTANT EN LETTRES — filet gauche, pas de fond ── */
    .amount-words {
      margin: 8px 32px 0; border-left: 2px solid var(--text);
      padding: 6px 14px; font-size: 11.5px; color: var(--text-muted);
    }
    .amount-words span { font-weight: 700; color: var(--text); }

    /* ── GARANTIE — simple encadré ── */
    .remarks-section { padding: 16px 32px; }

    .info-card { border: 1px solid var(--line); border-radius: 6px; padding: 12px 14px; }
    .info-card h4 { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; }
    .info-card p { font-size: 13px; color: var(--text); font-weight: 600; }
    .remarks-text { font-size: 11px; color: var(--text-muted); line-height: 1.6; }

    /* ── SIGNATURE / CACHET ──
       Grand espace blanc réservé (marge haute) pour que le client et le
       magasin puissent signer/tamponner le document imprimé. */
    /* Table plutôt que flex : les deux blocs signature se chevauchaient
       sous DomPDF avec justify-content:space-between. */
    .signature-section { width: 100%; border-collapse: collapse; margin-top: 110px; }
    .signature-td { width: 50%; vertical-align: top; padding: 0 32px; }
    .signature-block { text-align: center; }
    .signature-line { border-top: 1px solid var(--line); margin-bottom: 8px; }
    .signature-block p { font-size: 11px; color: var(--text-muted); }

    .section-divider { border-top: 1px solid var(--line); margin: 0 32px 12px; }

    /* ── CONDITIONS DE PAIEMENT — texte simple, sans encadré ── */
    .conditions-section { padding: 0 32px; }
    .conditions-section h4 {
      font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
      color: var(--accent); margin-bottom: 8px;
    }
    .conditions-text { font-size: 11px; color: var(--text-muted); line-height: 1.6; }

    /* ── FOOTER ──
       Les conditions de paiement et les coordonnées de la boutique forment
       ensemble le pied de page : elles doivent rester collées au bas de la
       page. En flux normal elles se retrouvaient plus haut si le contenu
       était court. */
    .footer { padding: 12px 32px 0; text-align: center; }
    .footer-line { color: var(--text-muted); font-size: 10.5px; }
    .footer-line strong { color: var(--text); }
    .footer-legal { color: var(--text-muted); font-size: 9.5px; margin-top: 4px; }

    /* Collé au bas de la page elle-même (position:absolute par rapport à
       .page, qui est position:relative) — pas au bas de la fenêtre du
       navigateur. Avec position:fixed le footer suivait le défilement à
       l'écran au lieu de rester ancré au document. */
    .page-footer { position: absolute; left: 0; right: 0; bottom: 36px; }

    @media print {
      html, body { margin: 0; padding: 0; background: #fff; }
      .page { width: 100%; min-height: 100vh; margin: 0; box-shadow: none; border-radius: 0; }
      .no-print { display: none !important; }
    }

    @media screen {
      body { padding: 20px 0 40px; background: #f0f1f4; }
      .page { box-shadow: 0 4px 30px rgba(138,111,31,0.10); border-radius: 4px; }
    }
  </style>
</head>
<body>

@php
  // Le logo doit être intégré en base64 (et non via asset()/une URL http) :
  // DomPDF ne va pas chercher les images distantes par défaut
  // (config dompdf.enable_remote = false), donc le <img src="http://..."/>
  // restait vide dans le PDF envoyé/téléchargé, alors qu'il s'affichait
  // normalement dans l'aperçu navigateur (simple HTML, pas de DomPDF).
  // Le data URI fonctionne à l'identique dans les deux contextes.
  $logoPath = public_path('images/profil.jpeg');
  $logoSrc = is_file($logoPath)
      ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath))
      : asset('images/profil.jpeg');
@endphp

<div class="no-print" style="display:flex;justify-content:center;gap:12px;margin-bottom:16px;">
  <a href="{{ url()->previous() }}" class="btn btn-outline-secondary" style="padding:10px 28px;border-radius:8px;font-size:13px;font-weight:600;">
    🔙 Retour
  </a>
  <button onclick="window.print()" style="background:#8a6f1f;color:#fff;border:none;padding:10px 28px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;">
    🖨️ Imprimer
  </button>
  @if(!empty($downloadUrl))
    <a href="{{ $downloadUrl }}" style="background:#6e5718;color:#fff;border:none;padding:10px 28px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
      ⬇️ Télécharger PDF
    </a>
  @endif
</div>

<div class="page">
  <div class="top-bar"></div>

  {{-- ── EN-TÊTE ── logo et infos alignés via un tableau (fiable sous DomPDF) --}}
  <div class="header">
    <table class="header-table">
      <tr>
        <td class="header-td-logo">
          <div class="brand-icon">
            <img src="{{ $logoSrc }}" alt="GAPS APPLE">
          </div>
        </td>
        <td class="header-td-doc">
          <div class="header-doc">
            <div class="doc-type">{{ $documentType }}</div>
            <div class="doc-number">#{{ $documentNumber }}</div>
            @php $headerDate = $isEchange ? $sale->sale_date : ($invoice->issued_at ?? $sale->sale_date); @endphp
            <div class="doc-date">{{ $headerDate->translatedFormat('d F Y') }}</div>
          </div>
        </td>
      </tr>
    </table>
  </div>
  <div class="header-divider"></div>

  {{-- ── MÉTA : Client / Dates ── alignés via un tableau (fiable sous DomPDF) --}}
  <div class="meta-band-wrap">
    <table class="meta-band">
      <tr>
        <td class="meta-left">
          <h4>Facturé à</h4>
          @if($sale->customer)
            <p class="name">{{ $sale->customer->full_name }}</p>
            @if($sale->customer->phone)
              <p>{{ $sale->customer->phone }}</p>
            @endif
            @if(!$isEchange && $sale->customer->email)
              <p>{{ $sale->customer->email }}</p>
            @endif
            @if(!$isEchange && $sale->customer->address)
              <p>{{ $sale->customer->address }}</p>
            @endif
          @else
            <p class="name">Client anonyme</p>
          @endif
        </td>

        <td class="meta-td-right">
          @if(!$isEchange)
            <div class="meta-right">
              <table class="meta-rows">
                @if($invoice)
                  <tr>
                    <td class="meta-label">Statut</td>
                    <td class="meta-value"><span class="status-badge">{{ $invoice->status->label() }}</span></td>
                  </tr>
                @endif
                <tr>
                  <td class="meta-label">Vente</td>
                  <td class="meta-value">{{ $sale->sale_number }}</td>
                </tr>
              </table>
            </div>
          @endif
        </td>
      </tr>
    </table>
  </div>

  @if($isEchange)
    {{-- ── ÉCHANGE : PRODUIT APPORTÉ / PRODUIT REMIS ── --}}
    @php
      $exchangeDetails = $sale->exchange_details ?? [];
      $broughtQuantity = (int) ($exchangeDetails['quantity'] ?? 1);
      $givenQuantity = (int) $sale->items->sum('quantity');
      $addedAmount = (float) ($exchangeDetails['added_amount'] ?? 0);
    @endphp

    <div class="exchange-section">
      <table class="exchange-table">
        <tr>
          <td class="exchange-td-card">
            <div class="exchange-card">
              <h4>Produit apporté par le client</h4>
              <div class="product-name">{{ $exchangeDetails['name'] ?? '—' }}</div>
              <div class="product-ref">
                {{ $exchangeDetails['reference'] ?? '' }}
                @if(!empty($exchangeDetails['brand'])) — {{ $exchangeDetails['brand'] }} @endif
              </div>
              @if(!empty($exchangeDetails['imei']))
                <div class="product-ref">IMEI : {{ $exchangeDetails['imei'] }}</div>
              @endif
              <div class="value-row">
                <span class="label">Quantité apportée</span>
                <span class="val">{{ $broughtQuantity }}</span>
              </div>
            </div>
          </td>
          <td class="exchange-td-arrow">
            <div class="exchange-arrow-circle">
              <span class="arrow-line"></span>
              <span class="arrow-tri arrow-tri-left"></span>
              <span class="arrow-tri arrow-tri-right"></span>
            </div>
          </td>
          <td class="exchange-td-card">
            <div class="exchange-card">
              <h4>Produit remis par le magasin</h4>
              <div class="items-list">
                @forelse($sale->items as $item)
                  <div class="item-row">
                    <span>
                      {{ $item->product?->name ?? '—' }}
                      @if($item->productImei)
                        <br><small>IMEI : {{ $item->productImei->imei }}</small>
                      @endif
                    </span>
                    <span class="qty">x{{ $item->quantity }}</span>
                  </div>
                @empty
                  <div class="item-row"><span>—</span></div>
                @endforelse
              </div>
              <div class="value-row">
                <span class="label">Quantité remise</span>
                <span class="val">{{ $givenQuantity }}</span>
              </div>
            </div>
          </td>
        </tr>
      </table>
    </div>

    {{-- ── MONTANT AJOUTÉ : seul montant financier affiché, au même emplacement
         et avec le même style que le « Total final » des factures de vente ── --}}
    <div class="totals-row">
      <div class="totals-box">
        <div class="totals-grand">
          <span class="label">Montant ajouté par le client</span>
          <span class="val">{{ number_format($addedAmount, 0, ',', ' ') }} FCFA</span>
        </div>
        @if($invoice && !$invoice->isFullyPaid())
          <div class="totals-line">
            <span class="label">Payé</span>
            <span class="val">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} FCFA</span>
          </div>
          <div class="totals-line">
            <span class="label">Reste à payer</span>
            <span class="val">{{ number_format($invoice->remaining_amount, 0, ',', ' ') }} FCFA</span>
          </div>
        @endif
      </div>
    </div>
  @else
    {{-- ── VENTE : TABLEAU DES ARTICLES ── --}}
    <div class="items-section">
      <table class="items-table">
        <thead>
          <tr>
            <th style="width:5%;text-align:center;">#</th>
            <th style="width:45%;text-align:left;">Désignation</th>
            <th class="num" style="width:15%;">Qté</th>
            <th class="amount" style="width:17%;">P. Unitaire</th>
            <th class="amount" style="width:18%;">Total</th>
          </tr>
        </thead>
        <tbody>
          @forelse($sale->items as $index => $item)
            <tr>
              <td style="text-align:center;color:#c2b280;font-size:11px;">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
              <td class="desc">
                {{ $item->product?->name ?? '—' }}
                @if($item->productImei)
                  <small>IMEI : {{ $item->productImei->imei }}</small>
                @endif
              </td>
              <td class="qty"><span class="qty-badge">{{ $item->quantity }}</span></td>
              <td class="unit">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
              <td class="total">{{ number_format($item->line_total ?? ($item->quantity * $item->unit_price), 0, ',', ' ') }} FCFA</td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="text-align:center;padding:30px;color:#c2b280;">Aucun article</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- ── TOTAUX ── --}}
    <div class="totals-row">
      <div class="totals-box">
        @php
          $discount = (float) ($sale->discount_amount ?? 0);
          $total = (float) $sale->total_ttc;
          $subtotal = $total + $discount;
        @endphp
        <div class="totals-line">
          <span class="label">Sous-total</span>
          <span class="val">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</span>
        </div>
        @if($discount > 0)
          <div class="totals-line">
            <span class="label">Remise</span>
            <span class="val">-{{ number_format($discount, 0, ',', ' ') }} FCFA</span>
          </div>
        @endif
        <div class="totals-grand">
          <span class="label">Total final</span>
          <span class="val">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
        </div>
        @if($invoice && !$invoice->isFullyPaid())
          <div class="totals-line">
            <span class="label">Payé</span>
            <span class="val">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} FCFA</span>
          </div>
          <div class="totals-line">
            <span class="label">Reste à payer</span>
            <span class="val">{{ number_format($invoice->remaining_amount, 0, ',', ' ') }} FCFA</span>
          </div>
        @endif
      </div>
    </div>

    {{-- ── MONTANT EN LETTRES ── --}}
    <div class="amount-words">
      Arrêtée la présente facture à la somme de : <span>{{ \App\Helpers\NumberHelper::toWords($total) ?? number_format($total, 0, ',', ' ') . ' Francs CFA' }}</span>
    </div>
  @endif

  {{-- ── GARANTIE ── durée choisie à la vente, propre à chaque transaction --}}
  @if($sale->warranty_duration && $sale->warranty_duration->value !== 'none')
    <div class="remarks-section" style="padding-bottom:0;">
      <div class="info-card">
        <h4>Garantie</h4>
        <p style="font-size:13px;color:var(--text);font-weight:600;margin-bottom:2px;">{{ $sale->warranty_duration->label() }}</p>
        @if($sale->warranty_end_date)
          <p class="remarks-text">Valable jusqu'au {{ $sale->warranty_end_date->format('d/m/Y') }}</p>
        @endif
      </div>
    </div>
  @endif

  {{-- ── SIGNATURE / CACHET ── espace réservé pour la signature du client et le cachet du magasin --}}
  <table class="signature-section">
    <tr>
      <td class="signature-td">
        <div class="signature-block">
          <div class="signature-line"></div>
          <p>Date et Signature Client</p>
        </div>
      </td>
      <td class="signature-td">
        <div class="signature-block">
          <div class="signature-line"></div>
          <p>Pour {{ config('company.name') }}</p>
        </div>
      </td>
    </tr>
  </table>

  {{-- ── PIED DE PAGE ── conditions de paiement + coordonnées boutique,
       collé au bas de la page (position fixe à l'impression/PDF).
       Identique sur toutes les factures de vente et tous les bons
       d'échange. Aucune date/heure de génération. --}}
  <div class="page-footer">
    <div class="section-divider"></div>

    <div class="conditions-section">
      <h4>Conditions de paiement</h4>
      <p class="conditions-text">
        @php $remarksText = $invoice?->notes ?? $sale->notes; @endphp
        @if($remarksText)
          {{ $remarksText }}
        @else
          Le service après-vente peut durer une semaine maximum si la garantie n'a pas expiré. Nous ne remboursons pas — nous réparons ou remplaçons.
        @endif
      </p>
    </div>

    <div class="footer">
      <div class="footer-line">
        Tél: <strong>{{ config('company.phone') }}</strong>
        &nbsp;&nbsp;·&nbsp;&nbsp;Email: {{ config('company.email') }}
        &nbsp;&nbsp;·&nbsp;&nbsp;{{ config('company.address_line1') }}, {{ config('company.address_line2') }}
      </div>
      <div class="footer-legal">
        Ninea : {{ config('company.ninea') }} — RC : {{ config('company.rc') }}
      </div>
    </div>
  </div>

</div>

</body>
</html>

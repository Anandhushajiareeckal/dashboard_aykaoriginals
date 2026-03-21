<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 13px; color: #1a1a1a; background: #fff; padding: 48px; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 48px; }
  .logo-text { font-size: 22px; font-weight: 700; color: #0B132B; letter-spacing: 0.5px; }
  .logo-sub { font-size: 9px; letter-spacing: 3px; text-transform: uppercase; color: #C9A96E; margin-top: 3px; }
  .inv-label { font-size: 11px; color: #8A8880; text-transform: uppercase; letter-spacing: 1px; }
  .inv-number { font-size: 24px; font-weight: 700; color: #0B132B; margin-top: 4px; }
  .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; margin-top: 6px;
    background: {{ $invoice->status === 'Paid' ? '#E8F5E9' : ($invoice->status === 'Overdue' ? '#FEEBEE' : '#FFF8E1') }};
    color: {{ $invoice->status === 'Paid' ? '#2D6A4F' : ($invoice->status === 'Overdue' ? '#C0392B' : '#D4860A') }};
  }
  .divider { border: none; border-top: 1px solid #E8E6E0; margin: 32px 0; }
  .two-col { display: flex; gap: 48px; margin-bottom: 40px; }
  .col-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: #8A8880; margin-bottom: 8px; }
  .col-val { font-size: 14px; font-weight: 500; color: #0B132B; line-height: 1.6; }
  .col-val small { font-size: 12px; color: #5E6472; font-weight: 400; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
  thead tr { background: #0B132B; color: white; }
  th { padding: 10px 14px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; text-align: left; }
  td { padding: 12px 14px; border-bottom: 1px solid #F4F3EF; font-size: 13px; }
  tr:last-child td { border-bottom: none; }
  .totals { margin-left: auto; width: 280px; }
  .total-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; color: #5E6472; }
  .total-row.grand { border-top: 2px solid #0B132B; margin-top: 8px; padding-top: 12px; color: #0B132B; font-weight: 700; font-size: 16px; }
  .footer { margin-top: 64px; border-top: 1px solid #E8E6E0; padding-top: 20px; text-align: center; font-size: 11px; color: #8A8880; }
  .accent { color: #C9A96E; }
</style>
</head>
<body>

<div class="header">
  <div>
    <div class="logo-text">Ayka Originals</div>
    <div class="logo-sub">Talent & Production Management</div>
  </div>
  <div style="text-align:right">
    <div class="inv-label">Invoice</div>
    <div class="inv-number">{{ $invoice->invoice_number }}</div>
    <div class="status-badge">{{ $invoice->status }}</div>
  </div>
</div>

<div class="two-col">
  <div style="flex:1">
    <div class="col-label">Billed To</div>
    <div class="col-val">
      {{ $invoice->brand?->name ?? 'Client' }}<br>
      @if($invoice->brand?->contact_name)<small>Attn: {{ $invoice->brand->contact_name }}</small><br>@endif
      @if($invoice->brand?->location)<small>{{ $invoice->brand->location }}</small><br>@endif
      @if($invoice->brand?->email)<small class="accent">{{ $invoice->brand->email }}</small>@endif
    </div>
  </div>
  <div>
    <div class="col-label">Invoice Details</div>
    <div class="col-val">
      <small style="color:#8A8880">Date Issued</small><br>
      {{ $invoice->created_at->format('d M Y') }}<br><br>
      <small style="color:#8A8880">Due Date</small><br>
      {{ $invoice->due_date?->format('d M Y') ?? '—' }}
      @if($invoice->paid_date)
      <br><br><small style="color:#8A8880">Paid On</small><br>{{ $invoice->paid_date->format('d M Y') }}
      @endif
    </div>
  </div>
  @if($invoice->project)
  <div>
    <div class="col-label">Project</div>
    <div class="col-val">{{ $invoice->project->title }}<br><small>{{ $invoice->project->category }}</small></div>
  </div>
  @endif
</div>

<hr class="divider">

<table>
  <thead>
    <tr>
      <th>Description</th>
      <th style="text-align:right">Amount (AED)</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        Professional services — {{ $invoice->project?->title ?? 'Production Services' }}
        @if($invoice->notes)<br><small style="color:#8A8880">{{ $invoice->notes }}</small>@endif
      </td>
      <td style="text-align:right;font-weight:600">{{ number_format($invoice->amount, 2) }}</td>
    </tr>
  </tbody>
</table>

<div class="totals">
  <div class="total-row">
    <span>Subtotal</span>
    <span>AED {{ number_format($invoice->amount, 2) }}</span>
  </div>
  @if($invoice->tax > 0)
  <div class="total-row">
    <span>VAT ({{ $invoice->tax }}%)</span>
    <span>AED {{ number_format($invoice->total - $invoice->amount, 2) }}</span>
  </div>
  @endif
  <div class="total-row grand">
    <span>Total Due</span>
    <span>AED {{ number_format($invoice->total, 2) }}</span>
  </div>
</div>

<div class="footer">
  Ayka Originals · Dubai, UAE · <span class="accent">hello@aykaoriginals.com</span> · aykaoriginals.com<br>
  Thank you for your business.
</div>

</body>
</html>

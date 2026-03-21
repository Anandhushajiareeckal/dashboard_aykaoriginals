<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Invoice, Brand, Project};
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
class InvoiceController extends Controller {
    public function index(Request $req) {
        $q = Invoice::with('brand', 'project');
        if ($req->status) $q->where('status', $req->status);
        $invoices = $q->latest()->paginate(20)->withQueryString();
        $summary = [
            'total'   => Invoice::sum('total'),
            'paid'    => Invoice::where('status', 'Paid')->sum('total'),
            'pending' => Invoice::whereIn('status', ['Sent', 'Overdue'])->sum('total'),
        ];
        return view('invoices.index', compact('invoices', 'summary'));
    }
    public function create() {
        return view('invoices.create', [
            'brands'     => Brand::orderBy('name')->get(),
            'projects'   => Project::orderBy('title')->get(),
            'nextNumber' => 'INV-'.str_pad(Invoice::withTrashed()->count()+1, 4, '0', STR_PAD_LEFT),
        ]);
    }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            'invoice_number' => 'required|unique:invoices', 'brand_id' => 'nullable|exists:brands,id',
            'project_id' => 'nullable|exists:projects,id', 'amount' => 'required|numeric|min:0',
            'tax' => 'numeric|min:0', 'due_date' => 'nullable|date', 'notes' => 'nullable',
        ]);
        $data['total']  = $data['amount'] + ($data['amount'] * (($data['tax'] ?? 0) / 100));
        $data['status'] = 'Draft';
        $invoice = Invoice::create($data);
        $logger->created('Invoice', $invoice->id, $invoice->invoice_number);
        return redirect()->route('invoices.index')->with('success', 'Invoice created.');
    }
    public function pdf(Invoice $invoice, ActivityLogger $logger) {
        $invoice->load('brand', 'project');
        $logger->exported('Invoice', $invoice->invoice_number);
        return Pdf::loadView('invoices.pdf', compact('invoice'))->download("invoice-{$invoice->invoice_number}.pdf");
    }
    public function updateStatus(Request $req, Invoice $invoice, ActivityLogger $logger) {
        $req->validate(['status' => 'required|in:Draft,Sent,Paid,Overdue,Cancelled']);
        $old = $invoice->status;
        $invoice->update(['status' => $req->status]);
        if ($req->status === 'Paid') $invoice->update(['paid_date' => now()]);
        $logger->statusChanged('Invoice', $invoice->id, $invoice->invoice_number, "{$old} to {$req->status}");
        return back()->with('success', 'Invoice status updated.');
    }
}

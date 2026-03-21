# ============================================================
#  AYKA ORIGINALS - Activity Log System Update v3
#  Run: powershell -ExecutionPolicy Bypass -File ayka_v3_update.ps1
# ============================================================

$ProjectPath = "C:\laragon\www\ayka-originals"

Write-Host ""
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host "  AYKA ORIGINALS - Installing Activity Log System" -ForegroundColor Cyan
Write-Host "======================================================" -ForegroundColor Cyan
Write-Host ""

function Write-File($RelativePath, $Content) {
    $FullPath = Join-Path $ProjectPath $RelativePath
    $Dir = Split-Path $FullPath -Parent
    if (!(Test-Path $Dir)) {
        New-Item -ItemType Directory -Path $Dir -Force | Out-Null
    }
    [System.IO.File]::WriteAllText($FullPath, $Content, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] $RelativePath" -ForegroundColor Green
}

# -- 1. Migration ----------------------------------------------
Write-Host "[1/10] Creating migration..." -ForegroundColor Yellow
Write-File "database\migrations\2024_01_01_000009_create_activity_logs_table.php" ('<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::dropIfExists(''activity_logs'');
        Schema::create(''activity_logs'', function (Blueprint $table) {
            $table->id();
            $table->foreignId(''user_id'')->nullable()->constrained()->nullOnDelete();
            $table->string(''action'');
            $table->string(''module'')->nullable();
            $table->unsignedBigInteger(''entity_id'')->nullable();
            $table->string(''entity_label'')->nullable();
            $table->text(''description'')->nullable();
            $table->string(''ip_address'', 45)->nullable();
            $table->text(''user_agent'')->nullable();
            $table->string(''url'')->nullable();
            $table->string(''method'', 10)->nullable();
            $table->timestamps();
            $table->index(''user_id'');
            $table->index(''action'');
            $table->index(''module'');
            $table->index(''created_at'');
        });
    }
    public function down(): void { Schema::dropIfExists(''activity_logs''); }
};
')

# -- 2. ActivityLog Model --------------------------------------
Write-Host "[2/10] Creating ActivityLog model..." -ForegroundColor Yellow
Write-File "app\Models\ActivityLog.php" ('<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActivityLog extends Model {
    protected $fillable = [
        ''user_id'', ''action'', ''module'', ''entity_id'',
        ''entity_label'', ''description'', ''ip_address'',
        ''user_agent'', ''url'', ''method'',
    ];
    public function user() { return $this->belongsTo(User::class); }
    public function getColorAttribute(): string {
        return match($this->action) {
            ''login''    => ''bg-blue-50 text-blue-700'',
            ''logout''   => ''bg-gray-100 text-gray-600'',
            ''created''  => ''bg-green-50 text-green-700'',
            ''updated''  => ''bg-amber-50 text-amber-700'',
            ''deleted''  => ''bg-red-50 text-red-600'',
            ''status''   => ''bg-purple-50 text-purple-700'',
            ''exported'' => ''bg-indigo-50 text-indigo-700'',
            default    => ''bg-gray-100 text-gray-600'',
        };
    }
}
')

# -- 3. ActivityLogger Service ---------------------------------
Write-Host "[3/10] Creating ActivityLogger service..." -ForegroundColor Yellow
$servicesDir = Join-Path $ProjectPath "app\Services"
if (!(Test-Path $servicesDir)) { New-Item -ItemType Directory -Path $servicesDir -Force | Out-Null }
Write-File "app\Services\ActivityLogger.php" ('<?php
namespace App\Services;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class ActivityLogger {
    protected Request $request;
    public function __construct(Request $request) { $this->request = $request; }
    public function log(string $action, ?string $module = null, ?int $entityId = null, ?string $entityLabel = null, ?string $description = null): ActivityLog {
        return ActivityLog::create([
            ''user_id''      => Auth::id(),
            ''action''       => $action,
            ''module''       => $module,
            ''entity_id''    => $entityId,
            ''entity_label'' => $entityLabel,
            ''description''  => $description ?? $this->buildDescription($action, $module, $entityLabel),
            ''ip_address''   => $this->getIp(),
            ''user_agent''   => $this->request->userAgent(),
            ''url''          => $this->request->fullUrl(),
            ''method''       => $this->request->method(),
        ]);
    }
    public function login(): void {
        $user = Auth::user();
        $this->log(''login'', ''Auth'', $user->id, $user->name, "User \"{$user->name}\" logged in");
    }
    public function logout(): void {
        $user = Auth::user();
        if ($user) $this->log(''logout'', ''Auth'', $user->id, $user->name, "User \"{$user->name}\" logged out");
    }
    public function created(string $module, int $id, string $label): void {
        $this->log(''created'', $module, $id, $label, "Created {$module} \"{$label}\"");
    }
    public function updated(string $module, int $id, string $label): void {
        $this->log(''updated'', $module, $id, $label, "Updated {$module} \"{$label}\"");
    }
    public function deleted(string $module, int $id, string $label): void {
        $this->log(''deleted'', $module, $id, $label, "Deleted {$module} \"{$label}\"");
    }
    public function statusChanged(string $module, int $id, string $label, string $status): void {
        $this->log(''status'', $module, $id, $label, "Changed {$module} \"{$label}\" status to \"{$status}\"");
    }
    public function exported(string $module, string $label): void {
        $this->log(''exported'', $module, null, $label, "Exported {$label}");
    }
    protected function getIp(): string {
        foreach ([''HTTP_CF_CONNECTING_IP'', ''HTTP_X_FORWARDED_FOR'', ''HTTP_X_REAL_IP'', ''REMOTE_ADDR''] as $key) {
            $val = $this->request->server($key);
            if ($val) return trim(explode('','', $val)[0]);
        }
        return $this->request->ip() ?? ''127.0.0.1'';
    }
    protected function buildDescription(string $action, ?string $module, ?string $label): string {
        $who = Auth::user()?->name ?? ''System'';
        return match($action) {
            ''login''   => "{$who} logged in",
            ''logout''  => "{$who} logged out",
            ''created'' => "{$who} created {$module} \"{$label}\"",
            ''updated'' => "{$who} updated {$module} \"{$label}\"",
            ''deleted'' => "{$who} deleted {$module} \"{$label}\"",
            default   => "{$who} performed {$action} on {$module} \"{$label}\"",
        };
    }
}
')

# -- 4. AppServiceProvider -------------------------------------
Write-Host "[4/10] Updating AppServiceProvider..." -ForegroundColor Yellow
Write-File "app\Providers\AppServiceProvider.php" ('<?php
namespace App\Providers;
use App\Services\ActivityLogger;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
    public function register(): void {
        $this->app->singleton(ActivityLogger::class, function ($app) {
            return new ActivityLogger($app[''request'']);
        });
    }
    public function boot(): void {}
}
')

# -- 5. Controllers --------------------------------------------
Write-Host "[5/10] Writing all controllers with logging..." -ForegroundColor Yellow

Write-File "app\Http\Controllers\Auth\LoginController.php" ('<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class LoginController extends Controller {
    public function showLoginForm() { return view(''auth.login''); }
    public function login(Request $req, ActivityLogger $logger) {
        $credentials = $req->validate([''email'' => ''required|email'', ''password'' => ''required'']);
        if (auth()->attempt($credentials, $req->boolean(''remember''))) {
            $req->session()->regenerate();
            $logger->login();
            return redirect()->intended(route(''dashboard''));
        }
        return back()->withErrors([''email'' => ''Invalid credentials.''])->onlyInput(''email'');
    }
    public function logout(Request $req, ActivityLogger $logger) {
        $logger->logout();
        auth()->logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();
        return redirect()->route(''login'');
    }
}
')

Write-File "app\Http\Controllers\Web\ModelController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\TalentModel;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class ModelController extends Controller {
    public function index(Request $req) {
        $q = TalentModel::query();
        if ($req->name)     $q->where(''name'', ''like'', ''%''.$req->name.''%'');
        if ($req->location) $q->where(''location'', ''like'', ''%''.$req->location.''%'');
        if ($req->status)   $q->where(''status'', $req->status);
        if ($req->inhouse)  $q->where(''is_inhouse'', true);
        if ($req->category) $q->whereJsonContains(''categories'', $req->category);
        return view(''models.index'', [''models'' => $q->latest()->paginate(12)->withQueryString()]);
    }
    public function create() { return view(''models.create''); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''age'' => ''nullable|integer'', ''categories'' => ''nullable|array'',
            ''email'' => ''nullable|email'', ''phone'' => ''nullable'', ''location'' => ''nullable'',
            ''about'' => ''nullable'', ''budget'' => ''nullable|numeric'', ''is_inhouse'' => ''boolean'',
            ''status'' => ''in:Active,Inactive,On Leave,Unavailable'',
        ]);
        $model = TalentModel::create($data);
        if ($req->hasFile(''images''))
            foreach ($req->file(''images'') as $img) $model->addMedia($img)->toMediaCollection(''portfolio'');
        $logger->created(''Model'', $model->id, $model->name);
        return redirect()->route(''models.show'', $model)->with(''success'', ''Model added.'');
    }
    public function show(TalentModel $model) { $model->load(''projects''); return view(''models.show'', compact(''model'')); }
    public function edit(TalentModel $model) { return view(''models.edit'', compact(''model'')); }
    public function update(Request $req, TalentModel $model, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''age'' => ''nullable|integer'', ''categories'' => ''nullable|array'',
            ''email'' => ''nullable|email'', ''phone'' => ''nullable'', ''location'' => ''nullable'',
            ''about'' => ''nullable'', ''budget'' => ''nullable|numeric'', ''is_inhouse'' => ''boolean'',
            ''status'' => ''in:Active,Inactive,On Leave,Unavailable'',
        ]);
        $model->update($data);
        $logger->updated(''Model'', $model->id, $model->name);
        return redirect()->route(''models.show'', $model)->with(''success'', ''Model updated.'');
    }
    public function destroy(TalentModel $model, ActivityLogger $logger) {
        $logger->deleted(''Model'', $model->id, $model->name);
        $model->delete();
        return redirect()->route(''models.index'')->with(''success'', ''Model archived.'');
    }
}
')

Write-File "app\Http\Controllers\Web\ProjectController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Project, Brand, TalentModel, Crew};
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class ProjectController extends Controller {
    public function index(Request $req) {
        $q = Project::with(''brand'');
        if ($req->status)   $q->where(''status'', $req->status);
        if ($req->brand_id) $q->where(''brand_id'', $req->brand_id);
        return view(''projects.index'', [
            ''projects'' => $q->latest()->paginate(15)->withQueryString(),
            ''brands''   => Brand::orderBy(''name'')->get(),
        ]);
    }
    public function create() {
        return view(''projects.create'', [
            ''brands'' => Brand::orderBy(''name'')->get(),
            ''models'' => TalentModel::where(''status'', ''Active'')->orderBy(''name'')->get(),
            ''crew''   => Crew::orderBy(''name'')->get(),
        ]);
    }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''title'' => ''required'', ''brand_id'' => ''nullable|exists:brands,id'',
            ''category'' => ''nullable'', ''budget'' => ''nullable|numeric'',
            ''start_date'' => ''nullable|date'', ''end_date'' => ''nullable|date'',
            ''status'' => ''in:Planning,Active,Review,Completed,Cancelled'', ''notes'' => ''nullable'',
        ]);
        $project = Project::create($data);
        if ($req->model_ids) $project->models()->sync($req->model_ids);
        if ($req->crew_ids)  $project->crew()->sync($req->crew_ids);
        $logger->created(''Project'', $project->id, $project->title);
        return redirect()->route(''projects.show'', $project)->with(''success'', ''Project created.'');
    }
    public function show(Project $project) {
        $project->load(''brand'', ''models'', ''crew'', ''invoices'', ''meetings'');
        return view(''projects.show'', compact(''project''));
    }
    public function edit(Project $project) {
        return view(''projects.edit'', [
            ''project'' => $project, ''brands'' => Brand::orderBy(''name'')->get(),
            ''models'' => TalentModel::orderBy(''name'')->get(), ''crew'' => Crew::orderBy(''name'')->get(),
        ]);
    }
    public function update(Request $req, Project $project, ActivityLogger $logger) {
        $data = $req->validate([
            ''title'' => ''required'', ''brand_id'' => ''nullable|exists:brands,id'',
            ''category'' => ''nullable'', ''budget'' => ''nullable|numeric'',
            ''start_date'' => ''nullable|date'', ''end_date'' => ''nullable|date'',
            ''status'' => ''in:Planning,Active,Review,Completed,Cancelled'',
            ''progress'' => ''integer|min:0|max:100'', ''notes'' => ''nullable'',
        ]);
        $project->update($data);
        if ($req->has(''model_ids'')) $project->models()->sync($req->model_ids ?? []);
        if ($req->has(''crew_ids''))  $project->crew()->sync($req->crew_ids ?? []);
        $logger->updated(''Project'', $project->id, $project->title);
        return redirect()->route(''projects.show'', $project)->with(''success'', ''Project updated.'');
    }
    public function destroy(Project $project, ActivityLogger $logger) {
        $logger->deleted(''Project'', $project->id, $project->title);
        $project->delete();
        return redirect()->route(''projects.index'')->with(''success'', ''Project deleted.'');
    }
}
')

Write-File "app\Http\Controllers\Web\BrandController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class BrandController extends Controller {
    public function index(Request $req) {
        $q = Brand::withCount(''projects'');
        if ($req->name) $q->where(''name'', ''like'', ''%''.$req->name.''%'');
        return view(''brands.index'', [''brands'' => $q->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view(''brands.create''); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''location'' => ''nullable'',
            ''contact_name'' => ''nullable'', ''contact_designation'' => ''nullable'',
            ''email'' => ''nullable|email'', ''phone'' => ''nullable'',
            ''website'' => ''nullable|url'', ''description'' => ''nullable'',
            ''next_followup_date'' => ''nullable|date'',
        ]);
        $brand = Brand::create($data);
        $logger->created(''Brand'', $brand->id, $brand->name);
        return redirect()->route(''brands.index'')->with(''success'', ''Brand added.'');
    }
    public function show(Brand $brand) {
        $brand->load(''projects'', ''followups.user'', ''invoices'', ''meetings'');
        return view(''brands.show'', compact(''brand''));
    }
    public function edit(Brand $brand) { return view(''brands.edit'', compact(''brand'')); }
    public function update(Request $req, Brand $brand, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''location'' => ''nullable'',
            ''contact_name'' => ''nullable'', ''contact_designation'' => ''nullable'',
            ''email'' => ''nullable|email'', ''phone'' => ''nullable'',
            ''website'' => ''nullable|url'', ''description'' => ''nullable'',
            ''next_followup_date'' => ''nullable|date'',
        ]);
        $brand->update($data);
        $logger->updated(''Brand'', $brand->id, $brand->name);
        return redirect()->route(''brands.show'', $brand)->with(''success'', ''Brand updated.'');
    }
    public function destroy(Brand $brand, ActivityLogger $logger) {
        $logger->deleted(''Brand'', $brand->id, $brand->name);
        $brand->delete();
        return redirect()->route(''brands.index'')->with(''success'', ''Brand removed.'');
    }
    public function addFollowup(Request $req, Brand $brand, ActivityLogger $logger) {
        $req->validate([''note'' => ''required'']);
        $brand->followups()->create([''user_id'' => auth()->id(), ''note'' => $req->note, ''followup_date'' => $req->followup_date]);
        $logger->log(''created'', ''Follow-up'', $brand->id, $brand->name, "Added follow-up for brand \"{$brand->name}\"");
        return back()->with(''success'', ''Follow-up logged.'');
    }
}
')

Write-File "app\Http\Controllers\Web\MeetingController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Meeting, Brand, Project, Employee};
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class MeetingController extends Controller {
    public function index() {
        $meetings = Meeting::with(''brand'', ''project'', ''employee'')->orderBy(''meeting_at'')->paginate(20);
        $upcoming = Meeting::with(''brand'')->where(''meeting_at'', ''>'', now())->orderBy(''meeting_at'')->take(10)->get();
        return view(''meetings.index'', compact(''meetings'', ''upcoming''));
    }
    public function create() {
        return view(''meetings.create'', [
            ''brands''    => Brand::orderBy(''name'')->get(),
            ''projects''  => Project::orderBy(''title'')->get(),
            ''employees'' => Employee::where(''status'', ''Active'')->orderBy(''name'')->get(),
        ]);
    }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''title'' => ''required'', ''brand_id'' => ''nullable|exists:brands,id'',
            ''project_id'' => ''nullable|exists:projects,id'', ''employee_id'' => ''nullable|exists:employees,id'',
            ''meeting_at'' => ''required|date'', ''duration_minutes'' => ''integer|min:5'',
            ''mode'' => ''in:Online,In-person,Hybrid'', ''notes'' => ''nullable'',
        ]);
        $meeting = Meeting::create($data);
        $logger->created(''Meeting'', $meeting->id, $meeting->title);
        return redirect()->route(''meetings.index'')->with(''success'', ''Meeting scheduled.'');
    }
    public function destroy(Meeting $meeting, ActivityLogger $logger) {
        $logger->deleted(''Meeting'', $meeting->id, $meeting->title);
        $meeting->delete();
        return redirect()->route(''meetings.index'')->with(''success'', ''Meeting removed.'');
    }
}
')

Write-File "app\Http\Controllers\Web\InvoiceController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{Invoice, Brand, Project};
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
class InvoiceController extends Controller {
    public function index(Request $req) {
        $q = Invoice::with(''brand'', ''project'');
        if ($req->status) $q->where(''status'', $req->status);
        $invoices = $q->latest()->paginate(20)->withQueryString();
        $summary = [
            ''total''   => Invoice::sum(''total''),
            ''paid''    => Invoice::where(''status'', ''Paid'')->sum(''total''),
            ''pending'' => Invoice::whereIn(''status'', [''Sent'', ''Overdue''])->sum(''total''),
        ];
        return view(''invoices.index'', compact(''invoices'', ''summary''));
    }
    public function create() {
        return view(''invoices.create'', [
            ''brands''     => Brand::orderBy(''name'')->get(),
            ''projects''   => Project::orderBy(''title'')->get(),
            ''nextNumber'' => ''INV-''.str_pad(Invoice::withTrashed()->count()+1, 4, ''0'', STR_PAD_LEFT),
        ]);
    }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''invoice_number'' => ''required|unique:invoices'', ''brand_id'' => ''nullable|exists:brands,id'',
            ''project_id'' => ''nullable|exists:projects,id'', ''amount'' => ''required|numeric|min:0'',
            ''tax'' => ''numeric|min:0'', ''due_date'' => ''nullable|date'', ''notes'' => ''nullable'',
        ]);
        $data[''total'']  = $data[''amount''] + ($data[''amount''] * (($data[''tax''] ?? 0) / 100));
        $data[''status''] = ''Draft'';
        $invoice = Invoice::create($data);
        $logger->created(''Invoice'', $invoice->id, $invoice->invoice_number);
        return redirect()->route(''invoices.index'')->with(''success'', ''Invoice created.'');
    }
    public function pdf(Invoice $invoice, ActivityLogger $logger) {
        $invoice->load(''brand'', ''project'');
        $logger->exported(''Invoice'', $invoice->invoice_number);
        return Pdf::loadView(''invoices.pdf'', compact(''invoice''))->download("invoice-{$invoice->invoice_number}.pdf");
    }
    public function updateStatus(Request $req, Invoice $invoice, ActivityLogger $logger) {
        $req->validate([''status'' => ''required|in:Draft,Sent,Paid,Overdue,Cancelled'']);
        $old = $invoice->status;
        $invoice->update([''status'' => $req->status]);
        if ($req->status === ''Paid'') $invoice->update([''paid_date'' => now()]);
        $logger->statusChanged(''Invoice'', $invoice->id, $invoice->invoice_number, "{$old} to {$req->status}");
        return back()->with(''success'', ''Invoice status updated.'');
    }
}
')

Write-File "app\Http\Controllers\Web\CrewController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class CrewController extends Controller {
    public function index(Request $req) {
        $q = Crew::query();
        if ($req->name)   $q->where(''name'', ''like'', ''%''.$req->name.''%'');
        if ($req->role)   $q->where(''role'', ''like'', ''%''.$req->role.''%'');
        if ($req->status) $q->where(''status'', $req->status);
        return view(''crew.index'', [''crew'' => $q->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view(''crew.create''); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''role'' => ''required'', ''email'' => ''nullable|email'',
            ''phone'' => ''nullable'', ''location'' => ''nullable'', ''status'' => ''in:Available,On Project,Inactive'',
        ]);
        $crew = Crew::create($data);
        $logger->created(''Crew'', $crew->id, $crew->name);
        return redirect()->route(''crew.index'')->with(''success'', ''Crew member added.'');
    }
    public function show(Crew $crew) { $crew->load(''projects''); return view(''crew.show'', compact(''crew'')); }
    public function edit(Crew $crew) { return view(''crew.edit'', compact(''crew'')); }
    public function update(Request $req, Crew $crew, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''role'' => ''required'', ''email'' => ''nullable|email'',
            ''phone'' => ''nullable'', ''location'' => ''nullable'', ''status'' => ''in:Available,On Project,Inactive'',
        ]);
        $crew->update($data);
        $logger->updated(''Crew'', $crew->id, $crew->name);
        return redirect()->route(''crew.index'')->with(''success'', ''Crew member updated.'');
    }
    public function destroy(Crew $crew, ActivityLogger $logger) {
        $logger->deleted(''Crew'', $crew->id, $crew->name);
        $crew->delete();
        return redirect()->route(''crew.index'')->with(''success'', ''Crew member removed.'');
    }
}
')

Write-File "app\Http\Controllers\Web\EmployeeController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
class EmployeeController extends Controller {
    public function index(Request $req) {
        $q = Employee::query();
        if ($req->name)       $q->where(''name'', ''like'', ''%''.$req->name.''%'');
        if ($req->department) $q->where(''department'', $req->department);
        if ($req->status)     $q->where(''status'', $req->status);
        return view(''employees.index'', [''employees'' => $q->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view(''employees.create''); }
    public function store(Request $req, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''department'' => ''nullable'', ''email'' => ''nullable|email'',
            ''phone'' => ''nullable'', ''salary'' => ''nullable|numeric'',
            ''joining_date'' => ''nullable|date'', ''status'' => ''in:Active,On Leave,Resigned'',
        ]);
        $emp = Employee::create($data);
        $logger->created(''Employee'', $emp->id, $emp->name);
        return redirect()->route(''employees.index'')->with(''success'', ''Employee added.'');
    }
    public function show(Employee $employee) { $employee->load(''user'', ''meetings''); return view(''employees.show'', compact(''employee'')); }
    public function edit(Employee $employee) { return view(''employees.edit'', compact(''employee'')); }
    public function update(Request $req, Employee $employee, ActivityLogger $logger) {
        $data = $req->validate([
            ''name'' => ''required'', ''department'' => ''nullable'', ''email'' => ''nullable|email'',
            ''phone'' => ''nullable'', ''salary'' => ''nullable|numeric'',
            ''joining_date'' => ''nullable|date'', ''status'' => ''in:Active,On Leave,Resigned'',
        ]);
        $employee->update($data);
        $logger->updated(''Employee'', $employee->id, $employee->name);
        return redirect()->route(''employees.show'', $employee)->with(''success'', ''Employee updated.'');
    }
    public function destroy(Employee $employee, ActivityLogger $logger) {
        $logger->deleted(''Employee'', $employee->id, $employee->name);
        $employee->delete();
        return redirect()->route(''employees.index'')->with(''success'', ''Employee removed.'');
    }
}
')

# -- 6. ActivityLogController ----------------------------------
Write-Host "[6/10] Creating ActivityLogController..." -ForegroundColor Yellow
Write-File "app\Http\Controllers\Web\ActivityLogController.php" ('<?php
namespace App\Http\Controllers\Web;
use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, User};
use Illuminate\Http\Request;
class ActivityLogController extends Controller {
    public function index(Request $req) {
        $q = ActivityLog::with(''user'')->latest();
        if ($req->user_id)   $q->where(''user_id'', $req->user_id);
        if ($req->action)    $q->where(''action'', $req->action);
        if ($req->module)    $q->where(''module'', $req->module);
        if ($req->ip)        $q->where(''ip_address'', ''like'', ''%''.$req->ip.''%'');
        if ($req->date_from) $q->whereDate(''created_at'', ''>='', $req->date_from);
        if ($req->date_to)   $q->whereDate(''created_at'', ''<='', $req->date_to);
        if ($req->search)    $q->where(''description'', ''like'', ''%''.$req->search.''%'');
        $logs       = $q->paginate(50)->withQueryString();
        $users      = User::orderBy(''name'')->get();
        $actions    = ActivityLog::select(''action'')->distinct()->pluck(''action'');
        $modules    = ActivityLog::select(''module'')->whereNotNull(''module'')->distinct()->pluck(''module'');
        $todayCount = ActivityLog::whereDate(''created_at'', today())->count();
        $loginCount = ActivityLog::where(''action'', ''login'')->whereDate(''created_at'', today())->count();
        $totalCount = ActivityLog::count();
        $uniqueIps  = ActivityLog::whereDate(''created_at'', today())->distinct(''ip_address'')->count(''ip_address'');
        return view(''activity-log.index'', compact(''logs'', ''users'', ''actions'', ''modules'', ''todayCount'', ''loginCount'', ''totalCount'', ''uniqueIps''));
    }
}
')

# -- 7. Routes -------------------------------------------------
Write-Host "[7/10] Writing routes/web.php..." -ForegroundColor Yellow
Write-File "routes\web.php" ('<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ModelController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\CrewController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\InvoiceController;
use App\Http\Controllers\Web\MeetingController;
use App\Http\Controllers\Web\SearchController;
use App\Http\Controllers\Web\ActivityLogController;

Route::middleware(''guest'')->group(function () {
    Route::get(''/login'', [LoginController::class, ''showLoginForm''])->name(''login'');
    Route::post(''/login'', [LoginController::class, ''login'']);
});

Route::middleware(''auth'')->group(function () {
    Route::post(''/logout'', [LoginController::class, ''logout''])->name(''logout'');
    Route::get(''/'', DashboardController::class)->name(''dashboard'');
    Route::resource(''models'', ModelController::class);
    Route::resource(''projects'', ProjectController::class);
    Route::resource(''brands'', BrandController::class);
    Route::post(''brands/{brand}/followup'', [BrandController::class, ''addFollowup''])->name(''brands.followup'');
    Route::resource(''crew'', CrewController::class);
    Route::resource(''employees'', EmployeeController::class);
    Route::resource(''invoices'', InvoiceController::class);
    Route::get(''invoices/{invoice}/pdf'', [InvoiceController::class, ''pdf''])->name(''invoices.pdf'');
    Route::patch(''invoices/{invoice}/status'', [InvoiceController::class, ''updateStatus''])->name(''invoices.status'');
    Route::resource(''meetings'', MeetingController::class);
    Route::get(''/search'', [SearchController::class, ''__invoke''])->name(''search'');
    Route::get(''/activity-log'', [ActivityLogController::class, ''index''])->name(''activity-log.index'');
});
')

# -- 8. Activity Log View --------------------------------------
Write-Host "[8/10] Creating activity log view..." -ForegroundColor Yellow
$viewDir = Join-Path $ProjectPath "resources\views\activity-log"
if (!(Test-Path $viewDir)) { New-Item -ItemType Directory -Path $viewDir -Force | Out-Null }

Write-File "resources\views\activity-log\index.blade.php" ('@extends(''layouts.app'')
@section(''title'', ''Activity Log'')
@section(''content'')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="font-display text-xl font-bold">Activity Log</h2>
        <p class="text-sm text-gray-400">Full audit trail with IP address and device tracking</p>
    </div>
</div>
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold">{{ number_format($totalCount) }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Total Events</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold text-[#0B132B]">{{ $todayCount }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Today</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold text-blue-600">{{ $loginCount }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Logins Today</p>
    </div>
    <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
        <p class="font-display text-2xl font-bold text-[#C9A96E]">{{ $uniqueIps }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Unique IPs Today</p>
    </div>
</div>
<form method="GET" class="bg-white border border-gray-100 rounded-xl p-4 mb-5">
    <div class="grid grid-cols-6 gap-3 mb-3">
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">User</label>
            <select name="user_id" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request(''user_id'')==$u->id ? ''selected'' : '''' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Action</label>
            <select name="action" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All actions</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request(''action'')===$a ? ''selected'' : '''' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">Module</label>
            <select name="module" class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
                <option value="">All modules</option>
                @foreach($modules as $m)
                    <option value="{{ $m }}" {{ request(''module'')===$m ? ''selected'' : '''' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">IP Address</label>
            <input name="ip" value="{{ request(''ip'') }}" placeholder="e.g. 192.168."
                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">From</label>
            <input name="date_from" type="date" value="{{ request(''date_from'') }}"
                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
        </div>
        <div>
            <label class="block text-xs text-gray-400 uppercase tracking-wide mb-1">To</label>
            <input name="date_to" type="date" value="{{ request(''date_to'') }}"
                class="w-full border border-gray-200 rounded-lg px-2.5 py-2 text-sm outline-none focus:border-[#0B132B]">
        </div>
    </div>
    <div class="flex gap-3">
        <input name="search" value="{{ request(''search'') }}" placeholder="Search descriptions..."
            class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#0B132B]">
        <button type="submit" class="bg-[#0B132B] text-white text-sm px-5 py-2 rounded-lg hover:bg-[#1a2a4a]">Filter</button>
        <a href="{{ route(''activity-log.index'') }}" class="text-sm text-gray-400 hover:text-gray-700 py-2 px-2">Clear</a>
    </div>
</form>
<div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-100 bg-gray-50">
            <tr>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Time</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">User</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Action</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Module</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Description</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">IP Address</th>
                <th class="text-left px-4 py-3 text-xs text-gray-400 font-medium uppercase tracking-wide">Device</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($logs as $log)
            @php
                $color = match($log->action) {
                    ''login''    => ''bg-blue-50 text-blue-700'',
                    ''logout''   => ''bg-gray-100 text-gray-500'',
                    ''created''  => ''bg-green-50 text-green-700'',
                    ''updated''  => ''bg-amber-50 text-amber-700'',
                    ''deleted''  => ''bg-red-50 text-red-600'',
                    ''status''   => ''bg-purple-50 text-purple-700'',
                    ''exported'' => ''bg-indigo-50 text-indigo-700'',
                    default    => ''bg-gray-100 text-gray-600'',
                };
                $ua = $log->user_agent ?? '''';
                if (str_contains($ua, ''Chrome'') && !str_contains($ua, ''Edge'')) $browser = ''Chrome'';
                elseif (str_contains($ua, ''Firefox'')) $browser = ''Firefox'';
                elseif (str_contains($ua, ''Safari'') && !str_contains($ua, ''Chrome'')) $browser = ''Safari'';
                elseif (str_contains($ua, ''Edge'')) $browser = ''Edge'';
                else $browser = ''Unknown'';
                $device = str_contains($ua, ''Mobile'') ? ''Mobile'' : ''Desktop'';
            @endphp
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="text-xs text-gray-500">{{ $log->created_at->format(''d M Y'') }}</div>
                    <div class="text-xs font-semibold text-gray-800">{{ $log->created_at->format(''H:i:s'') }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }}</div>
                </td>
                <td class="px-4 py-3">
                    @if($log->user)
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-[#0B132B] flex items-center justify-center text-[#C9A96E] text-[9px] font-bold flex-shrink-0">
                            {{ strtoupper(substr($log->user->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-gray-800">{{ $log->user->name }}</div>
                            <div class="text-[10px] text-gray-400">{{ $log->user->email }}</div>
                        </div>
                    </div>
                    @else
                        <span class="text-xs text-gray-400 italic">System</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium {{ $color }}">
                        {{ ucfirst($log->action) }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @if($log->module)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-[#0B132B]/5 text-[#0B132B] font-medium">{{ $log->module }}</span>
                    @else
                        <span class="text-gray-300 text-xs">-</span>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <p class="text-sm text-gray-700">{{ $log->description }}</p>
                    @if($log->entity_label && $log->module !== ''Auth'')
                        <p class="text-[10px] text-gray-400 mt-0.5">ID #{{ $log->entity_id }} &middot; {{ $log->entity_label }}</p>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="font-mono text-xs text-gray-800 bg-gray-50 border border-gray-100 px-2 py-1 rounded-lg inline-block">
                        {{ $log->ip_address ?? ''-'' }}
                    </div>
                    @if($log->url)
                        <div class="text-[10px] text-gray-400 mt-1">{{ $log->method }} {{ parse_url($log->url, PHP_URL_PATH) }}</div>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <div class="text-xs font-medium text-gray-700">{{ $device }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $browser }}</div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-16 text-center text-gray-400">
                    <p class="text-3xl mb-2 font-display">0</p>
                    <p class="font-medium">No activity recorded yet</p>
                    <p class="text-xs mt-1">Events appear here after users log in and take actions.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5 flex items-center justify-between">
    <p class="text-xs text-gray-400">
        Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }} events
    </p>
    <div>{{ $logs->links() }}</div>
</div>
@endsection
')

# -- 9. Patch sidebar ------------------------------------------
Write-Host "[9/10] Patching sidebar..." -ForegroundColor Yellow
$layoutPath = Join-Path $ProjectPath "resources\views\layouts\app.blade.php"
$layout = [System.IO.File]::ReadAllText($layoutPath)
if ($layout -notmatch 'activity-log') {
    $link  = "`r`n    <p class=`"px-3 pt-4 pb-1 text-[9px] tracking-widest uppercase text-white/20`">System</p>"
    $link += "`r`n    <a href=`"{{ route('activity-log.index') }}`" class=`"sidebar-link flex items-center gap-2.5 px-3 py-2 rounded text-white/55 hover:bg-white/10 hover:text-white/90 text-sm transition-all {{ request()->routeIs('activity-log.*') ? 'active' : '' }}`">"
    $link += "`r`n      <span>&#9675;</span> Activity Log"
    $link += "`r`n    </a>"
    $layout = $layout -replace '(</nav>)', "$link`r`n  `$1"
    [System.IO.File]::WriteAllText($layoutPath, $layout, [System.Text.UTF8Encoding]::new($false))
    Write-Host "  [OK] Sidebar updated" -ForegroundColor Green
} else {
    Write-Host "  [SKIP] Already present" -ForegroundColor Gray
}

# -- 10. Migrate & clear ---------------------------------------
Write-Host "[10/10] Running migration and clearing caches..." -ForegroundColor Yellow
Set-Location $ProjectPath
$phpExe = "php"
$found = Get-ChildItem "C:\laragon\bin\php" -Filter "php.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
if ($found) { $phpExe = $found.FullName }
Write-Host "  Using PHP: $phpExe" -ForegroundColor Gray
& $phpExe artisan migrate --force
& $phpExe artisan view:clear
& $phpExe artisan config:clear
& $phpExe artisan cache:clear

Write-Host ""
Write-Host "======================================================" -ForegroundColor Green
Write-Host "  DONE! Activity Log system installed successfully." -ForegroundColor Green
Write-Host "======================================================" -ForegroundColor Green
Write-Host ""
Write-Host "  Visit: http://localhost:8000/activity-log" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Tracked events:" -ForegroundColor White
Write-Host "    Login / Logout  - with time, IP, browser" -ForegroundColor Gray
Write-Host "    Created         - models, brands, projects, crew, employees, invoices, meetings" -ForegroundColor Gray
Write-Host "    Updated         - any record changes" -ForegroundColor Gray
Write-Host "    Deleted         - soft deletes with record name" -ForegroundColor Gray
Write-Host "    Status change   - invoice paid, overdue etc" -ForegroundColor Gray
Write-Host "    PDF export      - invoice downloads" -ForegroundColor Gray
Write-Host "    Follow-ups      - brand interaction logs" -ForegroundColor Gray
Write-Host ""
Write-Host "  Filters: user / action / module / IP / date range / keyword" -ForegroundColor Gray
Write-Host ""

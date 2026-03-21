<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'ayka_originals');
define('DB_USER', 'root');
define('DB_PASS', '');
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change_me_in_production');

function db(): PDO {
    static $pdo;
    if (!$pdo) $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    return $pdo;
}

function jwt_create(array $payload): string {
    $header  = base64url(json_encode(['alg'=>'HS256','typ'=>'JWT']));
    $payload = base64url(json_encode($payload));
    $sig     = base64url(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$sig";
}

function jwt_verify(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$h, $p, $s] = $parts;
    $expected = base64url(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
    if (!hash_equals($expected, $s)) return null;
    $payload = json_decode(base64_decode(strtr($p, '-_', '+/')), true);
    return ($payload['exp'] ?? 0) > time() ? $payload : null;
}

function base64url(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function auth_required(): array {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/', $auth, $m)) json_die(['error'=>'Unauthorized'], 401);
    $payload = jwt_verify($m[1]);
    if (!$payload) json_die(['error'=>'Invalid or expired token'], 401);
    return $payload;
}

function json_die(array $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function paginate(PDOStatement $stmt, int $page = 1, int $per = 20): array {
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($all);
    $items = array_slice($all, ($page-1)*$per, $per);
    return ['data'=>$items,'total'=>$total,'page'=>$page,'per_page'=>$per,'last_page'=>(int)ceil($total/$per)];
}

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = preg_replace('#^/mobile-api#', '', $uri);
$page   = (int)($_GET['page'] ?? 1);

// ── Routes ───────────────────────────────────────────────────
match(true) {
    $method==='POST' && $uri==='/login' => (function() {
        $body = json_decode(file_get_contents('php://input'), true);
        $email = $body['email'] ?? ''; $pass = $body['password'] ?? '';
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user || !password_verify($pass, $user['password'])) json_die(['error'=>'Invalid credentials'], 401);
        $token = jwt_create(['sub'=>$user['id'],'email'=>$user['email'],'exp'=>time()+86400*7]);
        json_die(['token'=>$token,'user'=>['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email']]]);
    })(),

    $method==='GET' && $uri==='/models' => (function() use ($page) {
        auth_required();
        $q = 'SELECT * FROM talent_models WHERE deleted_at IS NULL';
        $params = [];
        if ($_GET['name'] ?? false) { $q.=' AND name LIKE ?'; $params[]="%{$_GET['name']}%"; }
        if ($_GET['status'] ?? false) { $q.=' AND status=?'; $params[]=$_GET['status']; }
        if ($_GET['location'] ?? false) { $q.=' AND location LIKE ?'; $params[]="%{$_GET['location']}%"; }
        $stmt = db()->prepare($q); $stmt->execute($params);
        json_die(paginate($stmt, $page));
    })(),

    $method==='GET' && preg_match('#^/models/(\d+)$#', $uri, $m) => (function() use ($m) {
        auth_required();
        $stmt = db()->prepare('SELECT * FROM talent_models WHERE id=? AND deleted_at IS NULL');
        $stmt->execute([$m[1]]);
        $model = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$model) json_die(['error'=>'Not found'], 404);
        json_die($model);
    })(),

    $method==='GET' && $uri==='/projects' => (function() use ($page) {
        auth_required();
        $q = 'SELECT p.*, b.name as brand_name FROM projects p LEFT JOIN brands b ON b.id=p.brand_id WHERE p.deleted_at IS NULL';
        $params = [];
        if ($_GET['status'] ?? false) { $q.=' AND p.status=?'; $params[]=$_GET['status']; }
        $q .= ' ORDER BY p.created_at DESC';
        $stmt = db()->prepare($q); $stmt->execute($params);
        json_die(paginate($stmt, $page));
    })(),

    $method==='GET' && $uri==='/meetings' => (function() use ($page) {
        auth_required();
        $stmt = db()->prepare('SELECT m.*, b.name as brand_name FROM meetings m LEFT JOIN brands b ON b.id=m.brand_id WHERE m.deleted_at IS NULL AND m.meeting_at >= NOW() ORDER BY m.meeting_at ASC');
        $stmt->execute();
        json_die(paginate($stmt, $page));
    })(),

    $method==='GET' && $uri==='/accounts/summary' => (function() {
        auth_required();
        $revenue = db()->query("SELECT SUM(total) FROM invoices WHERE status='Paid' AND deleted_at IS NULL")->fetchColumn();
        $pending = db()->query("SELECT SUM(total) FROM invoices WHERE status IN ('Sent','Overdue') AND deleted_at IS NULL")->fetchColumn();
        $expense = db()->query("SELECT SUM(amount) FROM expenses WHERE deleted_at IS NULL")->fetchColumn();
        json_die(['total_revenue'=>(float)$revenue,'pending_payments'=>(float)$pending,'total_expenses'=>(float)$expense,'net_profit'=>(float)$revenue-(float)$expense]);
    })(),

    $method==='GET' && $uri==='/accounts/invoices' => (function() use ($page) {
        auth_required();
        $stmt = db()->prepare('SELECT i.*, b.name as brand_name FROM invoices i LEFT JOIN brands b ON b.id=i.brand_id WHERE i.deleted_at IS NULL ORDER BY i.created_at DESC');
        $stmt->execute();
        json_die(paginate($stmt, $page));
    })(),

    $method==='POST' && $uri==='/logout' => (function() {
        auth_required();
        json_die(['message'=>'Logged out']);
    })(),

    default => json_die(['error'=>'Not found'], 404),
};

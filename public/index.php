<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Floristeria\Infrastructure\Database;
use Floristeria\Infrastructure\Repository;
use Floristeria\Domain\ProductRules;
use Floristeria\Domain\SaleRules;

session_start();
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

function h(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function csrf(): string {
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifyCsrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Solicitud no válida. Recarga la página.');
    }
}
function page(string $title, string $content): void {
    $user = $_SESSION['user'] ?? null;
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.h($title).' | Floristería Darcy</title><link rel="stylesheet" href="assets/app.css"></head><body>';
    if ($user) {
        echo '<aside class="sidebar"><div class="brand">✿ Darcy</div><nav><a href="?r=dashboard">Panel</a><a href="?r=products">Productos</a><a href="?r=categories">Categorías</a><a href="?r=customers">Clientes</a><a href="?r=sales">Ventas</a></nav><form method="post" action="?r=logout"><input type="hidden" name="csrf" value="'.h(csrf()).'"><button class="logout">Cerrar sesión</button></form></aside>';
    }
    echo '<main class="'.($user ? 'main' : 'auth-main').'"><header><span>Floristería Darcy</span>'.($user ? '<small>'.h($user['name']).'</small>' : '').'</header>'.$content.'</main></body></html>';
}
function flash(string $message, string $type = 'success'): string {
    $_SESSION['flash'] = [$message, $type]; return '';
}
function showFlash(): string {
    if (!isset($_SESSION['flash'])) return '';
    [$message, $type] = $_SESSION['flash']; unset($_SESSION['flash']);
    return '<div class="alert '.h($type).'">'.h($message).'</div>';
}

try {
    $db = Database::connect();
    $repo = new Repository($db);
    $repo->createAdmin(
        getenv('ADMIN_NAME') ?: 'Administradora',
        getenv('ADMIN_EMAIL') ?: 'admin@floristeria.local',
        getenv('ADMIN_PASSWORD') ?: 'ChangeMe123!'
    );
} catch (Throwable $error) {
    page('Configuración requerida', '<section class="panel"><h1>Configuración de base de datos</h1><p>No fue posible conectar con PostgreSQL. Verifica el archivo <code>.env</code>, crea la base de datos e importa <code>database/schema.sql</code>.</p><p class="muted">'.h($error->getMessage()).'</p></section>');
    exit;
}

$route = $_GET['r'] ?? (isset($_SESSION['user']) ? 'dashboard' : 'login');

if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $user = $repo->findUserByEmail(trim($_POST['email'] ?? ''));
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name']];
        header('Location: ?r=dashboard'); exit;
    }
    flash('Correo o contraseña incorrectos.', 'error');
    header('Location: ?r=login'); exit;
}
if ($route === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf(); $_SESSION = []; session_destroy(); header('Location: ?r=login'); exit;
}
if (!isset($_SESSION['user']) && $route !== 'login') { header('Location: ?r=login'); exit; }

if ($route === 'login') {
    page('Iniciar sesión', showFlash().'<section class="login-card"><div class="flower">✿</div><h1>Bienvenida a Darcy</h1><p>Ingresa al panel administrativo</p><form method="post"><input type="hidden" name="csrf" value="'.h(csrf()).'"><label>Correo electrónico</label><input type="email" name="email" required><label>Contraseña</label><input type="password" name="password" required><button>Ingresar</button></form><small>Configura tus credenciales en el archivo .env.</small></section>'); exit;
}

$content = showFlash();
if ($route === 'dashboard') {
    $stats = $repo->dashboard();
    $content .= '<h1>Panel principal</h1><p class="muted">Resumen general de tu floristería.</p><div class="cards"><article><span>Productos activos</span><strong>'.$stats['products'].'</strong></article><article><span>Clientes</span><strong>'.$stats['customers'].'</strong></article><article><span>Productos por agotarse</span><strong>'.$stats['low_stock'].'</strong></article><article><span>Ventas de hoy</span><strong>$'.number_format($stats['sales_today'], 2, ',', '.').'</strong></article></div><section class="panel"><h2>Bienvenida</h2><p>Administra el inventario, los clientes y las ventas desde el menú lateral.</p></section>';
} elseif ($route === 'categories') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf(); $name = trim($_POST['name'] ?? '');
        if ($name === '') flash('El nombre de la categoría es obligatorio.', 'error');
        else { $repo->createCategory($name, $_POST['description'] ?? ''); flash('Categoría registrada.'); }
        header('Location: ?r=categories'); exit;
    }
    $rows = $repo->all('categories');
    $content .= '<h1>Categorías</h1><section class="panel"><form method="post"><input type="hidden" name="csrf" value="'.h(csrf()).'"><div class="form-grid"><label>Nombre<input name="name" required maxlength="100"></label><label>Descripción<input name="description" maxlength="255"></label></div><button>Guardar categoría</button></form></section><section class="panel"><h2>Categorías registradas</h2><table><tr><th>Nombre</th><th>Descripción</th></tr>';
    foreach ($rows as $row) $content .= '<tr><td>'.h($row['name']).'</td><td>'.h($row['description']).'</td></tr>';
    $content .= '</table></section>';
} elseif ($route === 'products') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        try {
            $valid = ProductRules::validate($_POST['name'] ?? '', $_POST['category_name'] ?? '', (int) ($_POST['quantity'] ?? -1), (float) ($_POST['price'] ?? 0), (int) ($_POST['min_stock'] ?? 5));
            $category = $db->prepare('SELECT id FROM categories WHERE name = :name'); $category->execute(['name' => $valid['category']]); $categoryId = $category->fetchColumn();
            if (!$categoryId) throw new RuntimeException('Primero registra la categoría indicada.');
            $repo->createProduct(['category_id' => $categoryId, 'name' => $valid['name'], 'description' => trim($_POST['description'] ?? ''), 'quantity' => $valid['quantity'], 'price' => $valid['price'], 'min_stock' => $valid['min_stock']]);
            flash('Producto registrado correctamente.');
        } catch (Throwable $error) { flash($error->getMessage(), 'error'); }
        header('Location: ?r=products'); exit;
    }
    $rows = $db->query('SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id WHERE p.active = TRUE ORDER BY p.id DESC')->fetchAll();
    $content .= '<h1>Productos e inventario</h1><section class="panel"><form method="post"><input type="hidden" name="csrf" value="'.h(csrf()).'"><div class="form-grid"><label>Nombre<input name="name" required maxlength="150"></label><label>Categoría<input name="category_name" required placeholder="Flores, Decoración..."></label><label>Existencias<input type="number" name="quantity" min="0" value="0" required></label><label>Precio unitario<input type="number" name="price" min="0.01" step="0.01" required></label><label>Stock mínimo<input type="number" name="min_stock" min="0" value="5" required></label><label>Descripción<input name="description"></label></div><button>Registrar producto</button></form></section><section class="panel"><h2>Inventario</h2><table><tr><th>Producto</th><th>Categoría</th><th>Existencias</th><th>Precio</th><th>Estado</th></tr>';
    foreach ($rows as $row) { $low = (int) $row['quantity'] <= (int) $row['min_stock']; $content .= '<tr><td>'.h($row['name']).'</td><td>'.h($row['category']).'</td><td>'.h($row['quantity']).'</td><td>$'.number_format((float) $row['price'],2,',','.').'</td><td><span class="pill '.($low?'warning':'ok').'">'.($low?'Stock bajo':'Disponible').'</span></td></tr>'; }
    $content .= '</table></section>';
} elseif ($route === 'customers') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf(); $name = trim($_POST['name'] ?? '');
        if ($name === '') flash('El nombre del cliente es obligatorio.', 'error');
        else { $repo->createCustomer(['name'=>$name,'phone'=>trim($_POST['phone']??''),'email'=>trim($_POST['email']??''),'address'=>trim($_POST['address']??'')]); flash('Cliente registrado.'); }
        header('Location: ?r=customers'); exit;
    }
    $rows = $repo->all('customers');
    $content .= '<h1>Clientes</h1><section class="panel"><form method="post"><input type="hidden" name="csrf" value="'.h(csrf()).'"><div class="form-grid"><label>Nombre<input name="name" required></label><label>Teléfono<input name="phone"></label><label>Correo<input type="email" name="email"></label><label>Dirección<input name="address"></label></div><button>Registrar cliente</button></form></section><section class="panel"><h2>Directorio de clientes</h2><table><tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Dirección</th></tr>';
    foreach ($rows as $row) $content .= '<tr><td>'.h($row['name']).'</td><td>'.h($row['phone']).'</td><td>'.h($row['email']).'</td><td>'.h($row['address']).'</td></tr>';
    $content .= '</table></section>';
} elseif ($route === 'sales') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        try {
            $productIds = $_POST['product_id'] ?? []; $quantities = $_POST['quantity'] ?? [];
            $items = []; $ids = [];
            foreach ($productIds as $index => $id) {
                $requestedQuantity = (int) ($quantities[$index] ?? 0);
                if ($requestedQuantity === 0) {
                    continue;
                }
                $stmt = $db->prepare('SELECT id, quantity AS stock, price FROM products WHERE id = :id AND active = TRUE');
                $stmt->execute(['id' => (int) $id]); $product = $stmt->fetch();
                if (!$product) throw new RuntimeException('Uno de los productos seleccionados no existe.');
                $items[] = ['price'=>(float)$product['price'],'stock'=>(int)$product['stock'],'quantity'=>$requestedQuantity];
                $ids[] = (int)$id;
            }
            $calc = SaleRules::calculate($items, (float)($_POST['discount']??0));
            $customerId = ($_POST['customer_id']??'') !== '' ? (int)$_POST['customer_id'] : null;
            $saleId = $repo->createSale($customerId, (int)$_SESSION['user']['id'], $calc, $ids, trim($_POST['notes']??''));
            flash('Venta registrada. Número de operación: '.$saleId);
        } catch (Throwable $error) { flash($error->getMessage(), 'error'); }
        header('Location: ?r=sales'); exit;
    }
    $products = $db->query('SELECT id,name,quantity,price FROM products WHERE active=TRUE AND quantity>0 ORDER BY name')->fetchAll();
    $customers = $repo->all('customers','name ASC');
    $sales = $repo->all('sales');
    $content .= '<h1>Ventas</h1><section class="panel"><h2>Registrar venta</h2><form method="post"><input type="hidden" name="csrf" value="'.h(csrf()).'"><label>Cliente<select name="customer_id"><option value="">Venta mostrador</option>';
    foreach ($customers as $c) $content .= '<option value="'.h($c['id']).'">'.h($c['name']).'</option>';
    $content .= '</select></label><div class="form-grid">';
    foreach ($products as $p) $content .= '<label>'.h($p['name']).' · Stock '.h($p['quantity']).' · $'.h($p['price']).'<input type="hidden" name="product_id[]" value="'.h($p['id']).'"><input type="number" name="quantity[]" min="0" max="'.h($p['quantity']).'" value="0"></label>';
    $content .= '</div><label>Descuento (%)<input type="number" name="discount" min="0" max="100" step="0.01" value="0"></label><label>Notas<textarea name="notes"></textarea></label><button>Registrar venta</button></form></section><section class="panel"><h2>Historial de ventas</h2><table><tr><th>Número</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>';
    foreach ($sales as $s) $content .= '<tr><td>'.h($s['id']).'</td><td>'.h($s['created_at']).'</td><td>$'.number_format((float)$s['total'],2,',','.').'</td><td>'.h($s['status']).'</td></tr>';
    $content .= '</table></section>';
} else {
    http_response_code(404); $content .= '<section class="panel"><h1>Página no encontrada</h1></section>';
}
page('Administración', $content);

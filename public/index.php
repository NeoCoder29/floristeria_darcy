<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Floristeria\Infrastructure\Database;
use Floristeria\Infrastructure\Repository;
use Floristeria\Domain\ProductRules;

// Session hardening must happen before session_start().
date_default_timezone_set('America/Bogota');
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
    'path' => '/',
]);
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf(): string
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('La sesión del formulario expiró. Recarga la página e inténtalo nuevamente.');
    }
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = [$message, $type];
}

function showFlash(): string
{
    if (!isset($_SESSION['flash'])) {
        return '';
    }
    [$message, $type] = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $class = $type === 'error' ? 'error' : 'success';
    return '<div class="alert ' . $class . '" role="status">' . h($message) . '</div>';
}

function redirect(string $route): never
{
    header('Location: ?r=' . rawurlencode($route));
    exit;
}

function page(string $title, string $content): void
{
    $user = $_SESSION['user'] ?? null;
    $nav = [
        'dashboard' => ['Panel', '⌂'],
        'products' => ['Productos', '✿'],
        'inventory' => ['Inventario', '▤'],
        'categories' => ['Categorías', '◇'],
        'customers' => ['Clientes', '♙'],
        'sales' => ['Ventas', '＄'],
    ];
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="theme-color" content="#244c3b"><title>' . h($title) . ' | Floristería Darcy</title>';
    echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
    echo '<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">';
    echo '<link rel="stylesheet" href="assets/app.css"></head><body>';
    if ($user) {
        echo '<aside class="sidebar"><a class="brand" href="?r=dashboard"><span class="brand-mark">✿</span><span>Darcy<small>FLORISTERÍA</small></span></a>';
        echo '<div class="nav-caption">GESTIÓN</div><nav>';
        foreach ($nav as $key => [$label, $icon]) {
            $active = ($_GET['r'] ?? 'dashboard') === $key ? ' active' : '';
            echo '<a class="nav-link' . $active . '" href="?r=' . h($key) . '"><span class="nav-icon">' . h($icon) . '</span>' . h($label) . '</a>';
        }
        echo '</nav><div class="sidebar-bottom"><div class="user-chip"><span class="avatar">' . h(firstCharacter((string) $user['name'])) . '</span><span><strong>' . h($user['name']) . '</strong><small>Administración</small></span></div>';
        echo '<form method="post" action="?r=logout"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><button class="logout" type="submit">↪ Cerrar sesión</button></form></div></aside>';
    }
    echo '<main class="' . ($user ? 'main' : 'auth-main') . '">';
    if ($user) {
        echo '<header class="topbar"><div><span class="eyebrow">ESPACIO DE TRABAJO</span><strong>' . h($title) . '</strong></div><div class="topbar-user">' . h($user['name']) . '<span class="online-dot"></span></div></header>';
    }
    echo '<div class="page-content">' . $content . '</div></main></body></html>';
}

function positiveInt(mixed $value, string $label, bool $allowZero = false): int
{
    $parsed = filter_var($value, FILTER_VALIDATE_INT);
    if ($parsed === false || $parsed < ($allowZero ? 0 : 1)) {
        throw new InvalidArgumentException($label . ' no es válido.');
    }
    return $parsed;
}

function textLength(string $value): int
{
    $length = preg_match_all('/./us', $value);
    if ($length === false) {
        throw new InvalidArgumentException('El texto contiene una codificación inválida.');
    }
    return $length;
}

function firstCharacter(string $value): string
{
    return preg_match('/^./us', $value, $matches) === 1 ? $matches[0] : '?';
}

function money(float $value): string
{
    return '$' . number_format($value, 2, ',', '.');
}

try {
    $db = Database::connect();
    $repo = new Repository($db);
    $adminPassword = getenv('ADMIN_PASSWORD') ?: '';
    if ($adminPassword !== '') {
        $repo->createAdmin(
            getenv('ADMIN_NAME') ?: 'Administradora',
            getenv('ADMIN_EMAIL') ?: 'admin@floristeria.local',
            $adminPassword
        );
    }
} catch (Throwable $error) {
    error_log((string) $error);
    page('Configuración requerida', '<section class="login-card"><div class="flower">✿</div><h1>Conexión pendiente</h1><p>No fue posible conectar con la base de datos.</p><p class="muted">Verifica las variables del archivo .env, la extensión pdo_pgsql y que hayas importado database/schema.sql.</p></section>');
    exit;
}

$route = is_string($_GET['r'] ?? null) ? $_GET['r'] : (isset($_SESSION['user']) ? 'dashboard' : 'login');

if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $user = $email ? $repo->findUserByEmail($email) : null;
    if ($user && password_verify((string) ($_POST['password'] ?? ''), $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'name' => $user['name']];
        unset($_SESSION['csrf']);
        redirect('dashboard');
    }
    flash('Correo o contraseña incorrectos.', 'error');
    redirect('login');
}

if ($route === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    redirect('login');
}

if (!isset($_SESSION['user']) && $route !== 'login') {
    redirect('login');
}

if ($route === 'login') {
    page('Iniciar sesión', showFlash() . '<section class="login-card"><div class="flower">✿</div><p class="eyebrow centered">FLORISTERÍA DARCY</p><h1>Bienvenida de nuevo</h1><p>Ingresa para administrar tu floristería.</p><form method="post"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><label>Correo electrónico<input type="email" name="email" autocomplete="username" required maxlength="190"></label><label>Contraseña<input type="password" name="password" autocomplete="current-password" required></label><button class="button-primary full" type="submit">Ingresar al sistema <span>→</span></button></form><small>Acceso exclusivo para administración.</small></section>');
    exit;
}

$content = showFlash();
$userId = (int) $_SESSION['user']['id'];

try {
    if ($route === 'dashboard') {
        $stats = $repo->dashboard();
        $lowStock = $db->query('SELECT name, quantity, min_stock FROM products WHERE active = TRUE AND quantity <= min_stock ORDER BY quantity ASC, name ASC LIMIT 6')->fetchAll();
        $recentSales = $db->query("SELECT s.id, s.created_at, s.total, c.name AS customer FROM sales s LEFT JOIN customers c ON c.id = s.customer_id WHERE s.status = 'completed' ORDER BY s.created_at DESC LIMIT 5")->fetchAll();
        $content .= '<div class="welcome-row"><div><p class="eyebrow">RESUMEN GENERAL</p><h1>Panel principal</h1><p class="muted">Así se mueve tu floristería hoy.</p></div><a class="button-primary" href="?r=sales">＋ Registrar venta</a></div>';
        $content .= '<section class="metric-grid"><article class="metric-card"><span class="metric-icon green">✿</span><span class="metric-label">Productos activos</span><strong>' . $stats['products'] . '</strong><small>En el catálogo</small></article><article class="metric-card"><span class="metric-icon rose">♙</span><span class="metric-label">Clientes registrados</span><strong>' . $stats['customers'] . '</strong><small>En tu directorio</small></article><article class="metric-card"><span class="metric-icon amber">▤</span><span class="metric-label">Alertas de inventario</span><strong>' . $stats['low_stock'] . '</strong><small>En nivel mínimo o inferior</small></article><article class="metric-card"><span class="metric-icon violet">＄</span><span class="metric-label">Ventas de hoy</span><strong class="metric-money">' . money($stats['sales_today']) . '</strong><small>Ingresos registrados hoy</small></article></section>';
        $content .= '<div class="dashboard-grid"><section class="panel"><div class="panel-heading"><div><p class="eyebrow">ATENCIÓN</p><h2>Productos por reponer</h2></div><a href="?r=inventory">Ver inventario →</a></div>';
        if (!$lowStock) {
            $content .= '<div class="empty-state"><span>✓</span><strong>Todo en orden</strong><p>No hay productos por debajo del stock mínimo.</p></div>';
        } else {
            $content .= '<div class="stock-list">';
            foreach ($lowStock as $item) {
                $content .= '<div class="stock-row"><span class="product-symbol">✿</span><span class="stock-name"><strong>' . h($item['name']) . '</strong><small>Mínimo: ' . h($item['min_stock']) . '</small></span><span class="stock-count">' . h($item['quantity']) . ' <small>unid.</small></span></div>';
            }
            $content .= '</div>';
        }
        $content .= '</section><section class="panel"><div class="panel-heading"><div><p class="eyebrow">ACTIVIDAD</p><h2>Ventas recientes</h2></div><a href="?r=sales">Ver todas →</a></div>';
        if (!$recentSales) {
            $content .= '<div class="empty-state"><span>＄</span><strong>Aún no hay ventas</strong><p>Cuando registres una venta aparecerá aquí.</p></div>';
        } else {
            $content .= '<div class="sales-list">';
            foreach ($recentSales as $sale) {
                $content .= '<div class="sale-row"><span class="sale-symbol">＄</span><span><strong>Venta #' . h($sale['id']) . '</strong><small>' . h($sale['customer'] ?: 'Venta de mostrador') . ' · ' . h(date('d/m/Y H:i', strtotime($sale['created_at']))) . '</small></span><strong class="sale-total">' . money((float) $sale['total']) . '</strong></div>';
            }
            $content .= '</div>';
        }
        $content .= '</section></div><section class="insight-strip"><span>✿</span><div><strong>Tu operación, más organizada.</strong><p>Registra cada entrada y salida para mantener el inventario actualizado y consultar su historial.</p></div><a href="?r=inventory">Gestionar inventario →</a></section>';
    } elseif ($route === 'categories') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '' || textLength($name) > 100) {
                throw new InvalidArgumentException('El nombre es obligatorio y no puede superar 100 caracteres.');
            }
            $repo->createCategory($name, trim((string) ($_POST['description'] ?? '')));
            flash('Categoría registrada correctamente.');
            redirect('categories');
        }
        $rows = $repo->all('categories', 'name ASC');
        $content .= '<div class="page-heading"><p class="eyebrow">CATÁLOGO</p><h1>Categorías</h1><p class="muted">Organiza los productos por tipo o familia.</p></div><section class="panel form-panel"><div class="panel-heading"><div><h2>Nueva categoría</h2><p>Completa la información para clasificar tus productos.</p></div></div><form method="post"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><div class="form-grid"><label>Nombre de la categoría<input name="name" required maxlength="100" placeholder="Ej. Flores frescas"></label><label>Descripción<input name="description" maxlength="255" placeholder="Descripción breve"></label></div><button class="button-primary" type="submit">Guardar categoría</button></form></section><section class="panel"><div class="panel-heading"><div><h2>Categorías registradas</h2><p>' . count($rows) . ' categorías disponibles</p></div></div>';
        if (!$rows) {
            $content .= '<div class="empty-state"><span>◇</span><strong>No hay categorías</strong><p>Registra la primera categoría para organizar tus productos.</p></div>';
        } else {
            $content .= '<div class="table-wrap"><table><thead><tr><th>Categoría</th><th>Descripción</th></tr></thead><tbody>';
            foreach ($rows as $row) {
                $content .= '<tr><td><strong>' . h($row['name']) . '</strong></td><td>' . h($row['description'] ?: '—') . '</td></tr>';
            }
            $content .= '</tbody></table></div>';
        }
        $content .= '</section>';
    } elseif ($route === 'products') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $quantity = positiveInt($_POST['quantity'] ?? null, 'Las existencias', true);
            $minStock = positiveInt($_POST['min_stock'] ?? 5, 'El stock mínimo', true);
            $categoryId = positiveInt($_POST['category_id'] ?? null, 'La categoría');
            $priceRaw = trim((string) ($_POST['price'] ?? ''));
            if (!is_numeric($priceRaw)) {
                throw new InvalidArgumentException('El precio debe ser numérico.');
            }
            $categoryQuery = $db->prepare('SELECT name FROM categories WHERE id = :id');
            $categoryQuery->execute(['id' => $categoryId]);
            $categoryName = $categoryQuery->fetchColumn();
            if (!$categoryName) {
                throw new InvalidArgumentException('Selecciona una categoría válida.');
            }
            $valid = ProductRules::validate((string) ($_POST['name'] ?? ''), (string) $categoryName, $quantity, (float) $priceRaw, $minStock);
            $repo->createProduct([
                'category_id' => $categoryId,
                'name' => $valid['name'],
                'description' => trim((string) ($_POST['description'] ?? '')),
                'quantity' => $valid['quantity'],
                'price' => $valid['price'],
                'min_stock' => $valid['min_stock'],
            ]);
            flash('Producto registrado correctamente.');
            redirect('products');
        }
        $categories = $repo->all('categories', 'name ASC');
        $rows = $db->query('SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id = p.category_id WHERE p.active = TRUE ORDER BY p.name ASC')->fetchAll();
        $content .= '<div class="page-heading"><p class="eyebrow">CATÁLOGO E INVENTARIO</p><h1>Productos</h1><p class="muted">Administra los productos, sus precios y niveles de existencias.</p></div>';
        if (!$categories) {
            $content .= '<section class="notice"><strong>Primero necesitas una categoría.</strong><p>Registra al menos una categoría para poder crear productos.</p><a href="?r=categories">Ir a categorías →</a></section>';
        } else {
            $content .= '<section class="panel form-panel"><div class="panel-heading"><div><h2>Registrar producto</h2><p>Agrega un producto al catálogo de la floristería.</p></div></div><form method="post"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><div class="form-grid"><label>Nombre del producto<input name="name" required maxlength="150" placeholder="Ej. Ramo de rosas"></label><label>Categoría<select name="category_id" required><option value="">Seleccionar categoría</option>';
            foreach ($categories as $category) {
                $content .= '<option value="' . h($category['id']) . '">' . h($category['name']) . '</option>';
            }
            $content .= '</select></label><label>Existencias iniciales<input type="number" name="quantity" min="0" step="1" value="0" required></label><label>Precio unitario<input type="number" name="price" min="0.01" step="0.01" required placeholder="0.00"></label><label>Stock mínimo<input type="number" name="min_stock" min="0" step="1" value="5" required></label><label>Descripción<input name="description" maxlength="500" placeholder="Detalles del producto"></label></div><button class="button-primary" type="submit">＋ Guardar producto</button></form></section>';
        }
        $content .= '<section class="panel"><div class="panel-heading"><div><h2>Inventario de productos</h2><p>' . count($rows) . ' productos activos</p></div><a href="?r=inventory">Movimientos de inventario →</a></div>';
        if (!$rows) {
            $content .= '<div class="empty-state"><span>✿</span><strong>Tu catálogo está vacío</strong><p>Cuando registres productos aparecerán en esta tabla.</p></div>';
        } else {
            $content .= '<div class="table-wrap"><table><thead><tr><th>Producto</th><th>Categoría</th><th>Existencias</th><th>Precio</th><th>Estado</th></tr></thead><tbody>';
            foreach ($rows as $row) {
                $low = (int) $row['quantity'] <= (int) $row['min_stock'];
                $content .= '<tr><td><strong>' . h($row['name']) . '</strong><small class="table-subtitle">' . h($row['description']) . '</small></td><td>' . h($row['category']) . '</td><td><strong>' . h($row['quantity']) . '</strong> <small>unid.</small></td><td>' . money((float) $row['price']) . '</td><td><span class="status-pill ' . ($low ? 'warning' : 'good') . '">' . ($low ? 'Stock bajo' : 'Disponible') . '</span></td></tr>';
            }
            $content .= '</tbody></table></div>';
        }
        $content .= '</section>';
    } elseif ($route === 'inventory') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $productId = positiveInt($_POST['product_id'] ?? null, 'El producto');
            $quantity = positiveInt($_POST['quantity'] ?? null, 'La cantidad');
            $type = (string) ($_POST['movement_type'] ?? '');
            $reason = trim((string) ($_POST['reason'] ?? ''));
            if (textLength($reason) < 3 || textLength($reason) > 255) {
                throw new InvalidArgumentException('El motivo debe tener entre 3 y 255 caracteres.');
            }
            $repo->registerInventoryMovement($productId, $userId, $type, $quantity, $reason);
            flash('Movimiento registrado. Las existencias fueron actualizadas.');
            redirect('inventory');
        }
        $products = $db->query('SELECT id, name, quantity FROM products WHERE active = TRUE ORDER BY name')->fetchAll();
        $movements = $repo->inventoryMovements(40);
        $content .= '<div class="page-heading"><p class="eyebrow">CONTROL DE EXISTENCIAS</p><h1>Inventario</h1><p class="muted">Registra entradas y salidas manuales y conserva la trazabilidad de cada movimiento.</p></div>';
        if (!$products) {
            $content .= '<section class="notice"><strong>No hay productos activos.</strong><p>Registra productos antes de realizar movimientos.</p><a href="?r=products">Ir a productos →</a></section>';
        } else {
            $content .= '<section class="panel form-panel"><div class="panel-heading"><div><h2>Nuevo movimiento</h2><p>El sistema actualizará las existencias automáticamente.</p></div></div><form method="post"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><div class="form-grid"><label>Producto<select name="product_id" required><option value="">Seleccionar producto</option>';
            foreach ($products as $product) {
                $content .= '<option value="' . h($product['id']) . '">' . h($product['name']) . ' · Stock actual: ' . h($product['quantity']) . '</option>';
            }
            $content .= '</select></label><label>Tipo de movimiento<select name="movement_type" required><option value="entry">Entrada · Aumentar existencias</option><option value="exit">Salida · Disminuir existencias</option></select></label><label>Cantidad<input type="number" name="quantity" min="1" step="1" required></label><label>Motivo<input name="reason" required minlength="3" maxlength="255" placeholder="Ej. Compra a proveedor, ajuste de inventario"></label></div><button class="button-primary" type="submit">Registrar movimiento</button></form></section>';
        }
        $content .= '<section class="panel"><div class="panel-heading"><div><h2>Historial de movimientos</h2><p>Últimos 40 movimientos registrados.</p></div></div>';
        if (!$movements) {
            $content .= '<div class="empty-state"><span>▤</span><strong>Sin movimientos registrados</strong><p>Las entradas, salidas y descuentos por ventas aparecerán aquí.</p></div>';
        } else {
            $content .= '<div class="table-wrap"><table><thead><tr><th>Fecha</th><th>Producto</th><th>Movimiento</th><th>Cantidad</th><th>Antes</th><th>Después</th><th>Motivo</th><th>Responsable</th></tr></thead><tbody>';
            foreach ($movements as $movement) {
                $entry = $movement['movement_type'] === 'entry';
                $content .= '<tr><td>' . h(date('d/m/Y H:i', strtotime($movement['created_at']))) . '</td><td><strong>' . h($movement['product_name']) . '</strong></td><td><span class="status-pill ' . ($entry ? 'good' : 'warning') . '">' . ($entry ? 'Entrada' : 'Salida') . '</span></td><td>' . ($entry ? '+' : '−') . h($movement['quantity']) . '</td><td>' . h($movement['previous_stock']) . '</td><td><strong>' . h($movement['new_stock']) . '</strong></td><td>' . h($movement['reason']) . '</td><td>' . h($movement['user_name']) . '</td></tr>';
            }
            $content .= '</tbody></table></div>';
        }
        $content .= '</section>';
    } elseif ($route === 'customers') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            if ($name === '' || strlen($name) > 150) {
                throw new InvalidArgumentException('El nombre es obligatorio y no puede superar 150 caracteres.');
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException('El correo electrónico no tiene un formato válido.');
            }
            $repo->createCustomer([
                'name' => $name,
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'email' => $email ?: null,
                'address' => trim((string) ($_POST['address'] ?? '')),
            ]);
            flash('Cliente registrado correctamente.');
            redirect('customers');
        }
        $rows = $repo->all('customers', 'name ASC');
        $content .= '<div class="page-heading"><p class="eyebrow">RELACIONES</p><h1>Clientes</h1><p class="muted">Conserva la información de contacto de tus clientes.</p></div><section class="panel form-panel"><div class="panel-heading"><div><h2>Registrar cliente</h2><p>Los datos te ayudarán a asociar las ventas.</p></div></div><form method="post"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><div class="form-grid"><label>Nombre completo<input name="name" required maxlength="150"></label><label>Teléfono<input name="phone" maxlength="30" autocomplete="tel"></label><label>Correo electrónico<input type="email" name="email" maxlength="190" autocomplete="email"></label><label>Dirección<input name="address" maxlength="255" autocomplete="street-address"></label></div><button class="button-primary" type="submit">＋ Registrar cliente</button></form></section><section class="panel"><div class="panel-heading"><div><h2>Directorio de clientes</h2><p>' . count($rows) . ' clientes registrados</p></div></div>';
        if (!$rows) {
            $content .= '<div class="empty-state"><span>♙</span><strong>Aún no hay clientes</strong><p>Los clientes que registres aparecerán aquí.</p></div>';
        } else {
            $content .= '<div class="table-wrap"><table><thead><tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Dirección</th></tr></thead><tbody>';
            foreach ($rows as $row) {
                $content .= '<tr><td><strong>' . h($row['name']) . '</strong></td><td>' . h($row['phone'] ?: '—') . '</td><td>' . h($row['email'] ?: '—') . '</td><td>' . h($row['address'] ?: '—') . '</td></tr>';
            }
            $content .= '</tbody></table></div>';
        }
        $content .= '</section>';
    } elseif ($route === 'sales') {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $postedIds = $_POST['product_id'] ?? [];
            $postedQuantities = $_POST['quantity'] ?? [];
            if (!is_array($postedIds) || !is_array($postedQuantities)) {
                throw new InvalidArgumentException('La selección de productos no es válida.');
            }
            $items = [];
            foreach ($postedIds as $index => $id) {
                $productId = positiveInt($id, 'El producto');
                $rawQuantity = $postedQuantities[$index] ?? '0';
                $quantity = positiveInt($rawQuantity, 'La cantidad', true);
                if ($quantity > 0) {
                    $items[$productId] = ($items[$productId] ?? 0) + $quantity;
                }
            }
            $discountRaw = trim((string) ($_POST['discount'] ?? '0'));
            if (!is_numeric($discountRaw)) {
                throw new InvalidArgumentException('El descuento debe ser numérico.');
            }
            $discount = (float) $discountRaw;
            $customerRaw = (string) ($_POST['customer_id'] ?? '');
            $customerId = $customerRaw === '' ? null : positiveInt($customerRaw, 'El cliente');
            if ($customerId !== null) {
                $checkCustomer = $db->prepare('SELECT 1 FROM customers WHERE id = :id');
                $checkCustomer->execute(['id' => $customerId]);
                if (!$checkCustomer->fetchColumn()) {
                    throw new InvalidArgumentException('El cliente seleccionado no existe.');
                }
            }
            $saleId = $repo->createSale($customerId, $userId, $items, $discount, (string) ($_POST['notes'] ?? ''));
            flash('Venta #' . $saleId . ' registrada. El inventario se actualizó automáticamente.');
            redirect('sales');
        }
        $products = $db->query('SELECT id, name, quantity, price FROM products WHERE active = TRUE AND quantity > 0 ORDER BY name')->fetchAll();
        $customers = $repo->all('customers', 'name ASC');
        $sales = $db->query("SELECT s.*, c.name AS customer_name, u.name AS seller FROM sales s LEFT JOIN customers c ON c.id = s.customer_id JOIN users u ON u.id = s.user_id ORDER BY s.created_at DESC LIMIT 100")->fetchAll();
        $content .= '<div class="page-heading"><p class="eyebrow">OPERACIÓN COMERCIAL</p><h1>Ventas</h1><p class="muted">Registra operaciones y consulta el historial de ventas.</p></div><section class="panel form-panel"><div class="panel-heading"><div><h2>Nueva venta</h2><p>Selecciona los productos y las cantidades. El stock se valida al confirmar.</p></div></div>';
        if (!$products) {
            $content .= '<div class="notice"><strong>No hay productos disponibles para vender.</strong><p>Registra productos o aumenta sus existencias.</p><a href="?r=products">Ir a productos →</a></div>';
        } else {
            $content .= '<form method="post" id="sale-form"><input type="hidden" name="csrf" value="' . h(csrf()) . '"><label>Cliente<select name="customer_id"><option value="">Venta de mostrador</option>';
            foreach ($customers as $customer) {
                $content .= '<option value="' . h($customer['id']) . '">' . h($customer['name']) . '</option>';
            }
            $content .= '</select></label><div class="product-picker">';
            foreach ($products as $product) {
                $content .= '<label class="product-pick"><span class="product-symbol">✿</span><span class="pick-info"><strong>' . h($product['name']) . '</strong><small>' . h($product['quantity']) . ' disponibles · ' . money((float) $product['price']) . '</small></span><input type="hidden" name="product_id[]" value="' . h($product['id']) . '"><input class="quantity-input" type="number" name="quantity[]" min="0" max="' . h($product['quantity']) . '" step="1" value="0" aria-label="Cantidad de ' . h($product['name']) . '"></label>';
            }
            $content .= '</div><div class="form-grid"><label>Descuento (%)<input type="number" name="discount" min="0" max="100" step="0.01" value="0"></label><label>Notas<input name="notes" maxlength="1000" placeholder="Observaciones de la venta"></label></div><button class="button-primary" type="submit">Confirmar venta <span>→</span></button></form>';
        }
        $content .= '</section><section class="panel"><div class="panel-heading"><div><h2>Historial de ventas</h2><p>Últimas 100 operaciones registradas.</p></div></div>';
        if (!$sales) {
            $content .= '<div class="empty-state"><span>＄</span><strong>Sin ventas registradas</strong><p>Las ventas confirmadas aparecerán aquí.</p></div>';
        } else {
            $content .= '<div class="table-wrap"><table><thead><tr><th>Operación</th><th>Fecha</th><th>Cliente</th><th>Responsable</th><th>Subtotal</th><th>Descuento</th><th>Total</th><th>Estado</th></tr></thead><tbody>';
            foreach ($sales as $sale) {
                $content .= '<tr><td><strong>#' . h($sale['id']) . '</strong></td><td>' . h(date('d/m/Y H:i', strtotime($sale['created_at']))) . '</td><td>' . h($sale['customer_name'] ?: 'Mostrador') . '</td><td>' . h($sale['seller']) . '</td><td>' . money((float) $sale['subtotal']) . '</td><td>' . h($sale['discount_percent']) . '%</td><td><strong>' . money((float) $sale['total']) . '</strong></td><td><span class="status-pill good">' . h($sale['status'] === 'completed' ? 'Completada' : 'Cancelada') . '</span></td></tr>';
            }
            $content .= '</tbody></table></div>';
        }
        $content .= '</section>';
    } else {
        http_response_code(404);
        $content .= '<section class="panel"><div class="empty-state"><span>?</span><strong>Página no encontrada</strong><p>La sección solicitada no existe.</p><a href="?r=dashboard">Volver al panel →</a></div></section>';
    }
} catch (Throwable $error) {
    error_log((string) $error);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = $error instanceof InvalidArgumentException || $error instanceof RuntimeException
            ? $error->getMessage()
            : 'No fue posible completar la operación. Verifica los datos e inténtalo nuevamente.';
        flash($message, 'error');
        redirect($route);
    }
    $content .= '<div class="alert error">No fue posible cargar esta sección. Intenta nuevamente; si el problema continúa, revisa el registro de errores del servidor.</div>';
}

page(match ($route) {
    'dashboard' => 'Panel principal',
    'products' => 'Productos',
    'inventory' => 'Inventario',
    'categories' => 'Categorías',
    'customers' => 'Clientes',
    'sales' => 'Ventas',
    default => 'Administración',
}, $content);

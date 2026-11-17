<?php
require_once __DIR__ . '/../includes/auth_admin.php';
require_once __DIR__ . '/../includes/db.php';

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$mi_departamento = $_SESSION['usuario_departamento'] ?? '';

$esAdmin = ($rol === 'admin');
$esJefe = (strpos($rol, 'jefe_') === 0);
$puedeGestionar = ($esAdmin || $esJefe);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .tabla-titulo {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #0B2D6B;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 3px solid #c9973a;
        }
        .tabla-titulo i { color: #c9973a; font-size: 22px; }
        .tabla-titulo small {
            font-size: 13px;
            font-weight: 400;
            color: #475569;
            margin-left: 8px;
        }
        .table-almacenatec {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #FFFFFF;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.05);
        }
        .table-almacenatec thead { background: #0B2D6B; color: #FFFFFF; }
        .table-almacenatec thead th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 3px solid #c9973a;
            white-space: nowrap;
        }
        .table-almacenatec thead th i { margin-right: 6px; color: #c9973a; }
        .table-almacenatec tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
            vertical-align: middle;
        }
        .table-almacenatec tbody tr:hover { background: #F0F4F9; }
        .table-almacenatec tbody tr:last-child td { border-bottom: none; }
        .table-responsive-almacenatec { overflow-x: auto; border-radius: 10px; }
        .card-header-almacenatec {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            flex-wrap: wrap;
            gap: 12px;
        }
        .card-header-almacenatec h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #0B2D6B;
            margin: 0;
        }
        .card-header-almacenatec h3 i { color: #c9973a; margin-right: 8px; }

        /* ── Búsqueda ── */
        .search-almacenatec { position: relative; display: inline-flex; align-items: center; }
        .search-almacenatec i { position: absolute; left: 12px; color: #94A3B8; font-size: 15px; }
        .search-almacenatec input {
            padding: 8px 12px 8px 36px; border: 1.5px solid #E2E8F0; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif; background: #FFFFFF;
            color: #1E293B; transition: border-color 0.2s; min-width: 250px;
        }
        .search-almacenatec input:focus { outline: none; border-color: #0B2D6B; box-shadow: 0 0 0 3px rgba(11,45,107,.1); }

        .badge-almacenatec {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-almacenatec.admin { background: #d1fae5; color: #059669; }
        .badge-almacenatec.usuario { background: #e5e7eb; color: #6b7280; }
        .badge-almacenatec.mantenimiento { background: #fef3c7; color: #d97706; }
        .badge-almacenatec.finanzas { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.activo_fijo { background: #d1fae5; color: #059669; }
        .badge-almacenatec.almacen_general { background: #fef3c7; color: #d97706; }
        .badge-almacenatec.jefe { background: #f3e8ff; color: #7c3aed; }

        .badge-almacenatec.activo { color: #059669; }
        .badge-almacenatec.inactivo { color: #dc2626; }

        .btn-almacenatec {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-almacenatec.editar { background: rgba(11,45,107,.08); color: #0B2D6B; }
        .btn-almacenatec.editar:hover { background: #0B2D6B; color: #FFFFFF; }
        .btn-almacenatec.eliminar { background: rgba(184,28,46,.08); color: #B81C2E; }
        .btn-almacenatec.eliminar:hover { background: #B81C2E; color: #FFFFFF; }
        .btn-almacenatec.toggle { background: rgba(100,100,100,.1); color: #475569; }
        .btn-almacenatec.toggle:hover { background: #475569; color: #FFFFFF; }

        .btn-primary-almacenatec {
            background: #0B2D6B; color: #FFFFFF; border: none; padding: 9px 18px;
            border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-primary-almacenatec:hover { background: #071d46; color: #FFFFFF; }
        .btn-primary-almacenatec i { color: #c9973a; }

        .btn-outline-almacenatec {
            background: transparent; color: #0B2D6B; border: 1.5px solid #0B2D6B;
            padding: 7px 17px; border-radius: 8px; font-size: 13.5px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-outline-almacenatec:hover { background: #0B2D6B; color: #FFFFFF; }
        .btn-outline-almacenatec i { color: #0B2D6B; }
        .btn-outline-almacenatec:hover i { color: #FFFFFF; }

        .btn-accent-almacenatec {
            background: #B81C2E; color: #FFFFFF; border: none; padding: 9px 18px;
            border-radius: 8px; font-size: 13.5px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-accent-almacenatec:hover { background: #8f1522; color: #FFFFFF; }

        .modal-header-almacenatec {
            background: #0B2D6B; color: #fff; border-radius: 10px 10px 0 0;
            padding: 16px 20px; border-bottom: none;
        }
        .modal-header-almacenatec h5 { color: #fff; margin: 0; }
        .modal-header-almacenatec .btn-close { filter: invert(1); }

        #campoDepartamento { display: none; }
        #campoDepartamento.visible { display: block; }

        @media (max-width: 768px) {
            .table-almacenatec thead th { font-size: 10px; padding: 8px 10px; }
            .table-almacenatec tbody td { font-size: 12px; padding: 8px 10px; }
            .table-almacenatec thead th i { display: none; }
            .search-almacenatec input { min-width: 150px; }
        }
    </style>
</head>
<body>
<div class="app-layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-mark">AT</div>
                <div class="logo-text">
                    <span class="logo-name">AlmacenaTec</span>
                    <span class="logo-sub">TecNM Saltillo</span>
                </div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">Módulos</div>
            <a href="../home/index.php" class="sidebar-link"><i class="bi bi-house-door"></i> Inicio</a>
            <?php if ($esAdmin): ?>
            <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
            <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
            <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
            <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
            <a href="../orden_compra/compras.php" class="sidebar-link"><i class="bi bi-cart-check"></i> Compras</a>
            <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
            <div class="nav-section" style="margin-top:8px">Administración</div>
            <?php endif; ?>
            <a href="../usuarios/main_usuarios.php" class="sidebar-link active"><i class="bi bi-people"></i> Usuarios</a>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2026</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-people me-2" style="color:#0B2D6B"></i>Gestión de Usuarios</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; Usuarios</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-almacenatec" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">

            <?php if (isset($_GET['ok'])): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i> Usuario guardado correctamente.</div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])):
                $msg = $_GET['error'] === 'username_duplicado' ? 'El nombre de usuario ya existe' :
                       ($_GET['error'] === 'ultimo_admin' ? 'No puedes degradar al único administrador' :
                       ($_GET['error'] === 'password_requerido' ? 'La contraseña es requerida para nuevos usuarios' :
                       'Error al guardar el usuario'));
            ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $msg ?></div>
            <?php endif; ?>

            <div class="page-header">
                <h2><i class="bi bi-people me-2" style="color:#0B2D6B"></i>Usuarios del sistema</h2>
                <?php if ($puedeGestionar): ?>
                <button class="btn-primary-almacenatec" onclick="abrirModalUsuario()"><i class="bi bi-plus-lg"></i> Nuevo Usuario</button>
                <?php endif; ?>
            </div>

            <div class="tabla-titulo">
                <i class="bi bi-people"></i>
                Lista de Usuarios
                <small>Gestión de cuentas de acceso al sistema</small>
            </div>

            <div class="card-panel">
                <div class="card-header-almacenatec">
                    <h3><i class="bi bi-table"></i>Usuarios registrados</h3>
                    <div class="search-almacenatec">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por nombre, usuario o rol...">
                    </div>
                </div>

                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec" id="tablaUsuarios">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> ID</th>
                                <th><i class="bi bi-person"></i> Nombre</th>
                                <th><i class="bi bi-person-badge"></i> Usuario</th>
                                <th><i class="bi bi-tag"></i> Rol</th>
                                <th><i class="bi bi-building"></i> Departamento</th>
                                <th><i class="bi bi-circle"></i> Estado</th>
                                <th><i class="bi bi-clock"></i> Último acceso</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usuariosTableBody"></tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal: Nuevo/Editar Usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header-almacenatec">
                <h5 class="modal-title" id="modalUsuarioTitle"><i class="bi bi-person-plus me-2"></i>Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_PATH ?>handlers/save_usuario.php" id="formUsuario">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editId" value="0">
                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="nombre" id="editNombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="username" id="editUsername" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña <span class="text-muted" id="passHint">(mínimo 4 caracteres)</span></label>
                        <input type="password" name="password" id="editPassword" class="form-control" placeholder="Dejar en blanco para no cambiar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select name="rol" id="editRol" class="form-select" required onchange="verificarRolJefe()">
                            <optgroup label="Generales">
                                <option value="usuario">Usuario (En espera de rol)</option>
                                <?php if ($esAdmin): ?>
                                <option value="admin">Administrador</option>
                                <?php endif; ?>
                            </optgroup>
                            
                            <?php if ($esAdmin): ?>
                            <optgroup label="Módulos">
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="finanzas">Finanzas</option>
                                <option value="activo_fijo">Activo Fijo</option>
                                <option value="almacen_general">Almacén General</option>
                            </optgroup>
                            <optgroup label="Jefes de Departamento">
                                <option value="jefe_sistemas">Jefe de Sistemas</option>
                                <option value="jefe_mantenimiento">Jefe de Mantenimiento</option>
                                <option value="jefe_finanzas">Jefe de Finanzas</option>
                                <option value="jefe_rh">Jefe de Recursos Humanos</option>
                            </optgroup>
                            
                            <?php elseif ($rol === 'jefe_sistemas'): ?>
                            <optgroup label="Módulos de Sistemas">
                                <option value="activo_fijo">Activo Fijo</option>
                                <option value="almacen_general">Almacén General</option>
                            </optgroup>
                            
                            <?php elseif ($rol === 'jefe_mantenimiento'): ?>
                            <optgroup label="Módulos de Mantenimiento">
                                <option value="mantenimiento">Mantenimiento</option>
                            </optgroup>
                            
                            <?php elseif ($rol === 'jefe_finanzas'): ?>
                            <optgroup label="Módulos de Finanzas">
                                <option value="finanzas">Finanzas</option>
                            </optgroup>
                            
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="campoDepartamento">
                        <label class="form-label">Departamento <span class="text-danger">*</span></label>
                        <input type="text" name="departamento" id="editDepartamento" class="form-control" value="<?= htmlspecialchars($mi_departamento) ?>" <?= $esJefe ? 'readonly' : '' ?>>
                        <small class="text-muted"><?= $esJefe ? 'Asignado automáticamente a tu área' : 'Solo para jefes de departamento' ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-almacenatec" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cancelar</button>
                    <button type="submit" class="btn-primary-almacenatec"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    const soyAdmin = <?= $esAdmin ? 'true' : 'false' ?>;
    const miDepartamento = "<?= htmlspecialchars($mi_departamento) ?>";
    let todosLosUsuarios = []; // Guardamos todos para el buscador

    function toggleSidebar() {
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    function verificarRolJefe() {
        const rol = document.getElementById('editRol').value;
        const campo = document.getElementById('campoDepartamento');
        if (rol.startsWith('jefe_')) {
            campo.classList.add('visible');
        } else {
            campo.classList.remove('visible');
        }
    }

    function abrirModalUsuario(data) {
        document.getElementById('formUsuario').reset();
        document.getElementById('editId').value = 0;
        document.getElementById('editPassword').placeholder = 'Nueva contraseña';
        document.getElementById('passHint').textContent = '(mínimo 4 caracteres)';
        document.getElementById('modalUsuarioTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>Nuevo Usuario';
        document.getElementById('campoDepartamento').classList.remove('visible');

        if (!soyAdmin) {
            document.getElementById('editDepartamento').value = miDepartamento;
        }

        if (data) {
            document.getElementById('editId').value = data.id;
            document.getElementById('editNombre').value = data.nombre;
            document.getElementById('editUsername').value = data.username;
            document.getElementById('editRol').value = data.rol;
            document.getElementById('editDepartamento').value = data.departamento || '';
            document.getElementById('editPassword').placeholder = 'Dejar en blanco para no cambiar';
            document.getElementById('passHint').textContent = '(dejar en blanco para no cambiar)';
            document.getElementById('modalUsuarioTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Usuario';
            
            if (data.rol && data.rol.startsWith('jefe_')) {
                document.getElementById('campoDepartamento').classList.add('visible');
            }
        }
        new bootstrap.Modal(document.getElementById('modalUsuario')).show();
    }

    function toggleUsuario(id, action) {
        if (!confirm('¿Estás seguro de ' + (action === 'deactivate' ? 'desactivar' : 'activar') + ' este usuario?')) return;
        fetch('<?= BASE_PATH ?>handlers/toggle_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&action=' + action
        }).then(r => r.json()).then(d => { if (d.ok) location.reload(); else alert('Error: ' + d.error); });
    }

    function deleteUsuario(id) {
        if (!confirm('⚠️ ¿Eliminar este usuario permanentemente?')) return;
        fetch('<?= BASE_PATH ?>handlers/delete_usuario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        }).then(r => r.json()).then(d => { if (d.ok) location.reload(); else alert('Error: ' + d.error); });
    }

    function renderTabla(usuarios) {
        const tbody = document.getElementById('usuariosTableBody');
        tbody.innerHTML = '';
        
        if (usuarios.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;color:#94A3B8;">No se encontraron usuarios</td></tr>';
            return;
        }

        const rolesLabels = { 
            'admin': 'Administrador', 
            'usuario': 'Usuario', 
            'mantenimiento': 'Mantenimiento',
            'finanzas': 'Finanzas',
            'activo_fijo': 'Activo Fijo',
            'almacen_general': 'Almacén General',
            'jefe_sistemas': 'Jefe de Sistemas',
            'jefe_mantenimiento': 'Jefe de Mantenimiento',
            'jefe_finanzas': 'Jefe de Finanzas',
            'jefe_rh': 'Jefe de Recursos Humanos'
        };
        const rolesBadge = { 
            'admin': 'admin', 
            'usuario': 'usuario', 
            'mantenimiento': 'mantenimiento',
            'finanzas': 'finanzas',
            'activo_fijo': 'activo_fijo',
            'almacen_general': 'almacen_general',
            'jefe_sistemas': 'jefe',
            'jefe_mantenimiento': 'jefe',
            'jefe_finanzas': 'jefe',
            'jefe_rh': 'jefe'
        };
        
        usuarios.forEach(u => {
            const tr = document.createElement('tr');
            const estado = u.activo ? 'activo' : 'inactivo';
            const estadoIcon = u.activo ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            const esJefe = u.rol && u.rol.startsWith('jefe_');
            
            let botonesAccion = '';
            
            if (soyAdmin) {
                botonesAccion = `
                    <button class="btn-almacenatec editar" onclick="abrirModalUsuario({id:${u.id},nombre:'${u.nombre}',username:'${u.username}',rol:'${u.rol}',departamento:'${u.departamento || ''}'})"><i class="bi bi-pencil"></i></button>
                    ${u.rol !== 'admin' ? `<button class="btn-almacenatec eliminar" onclick="deleteUsuario(${u.id})"><i class="bi bi-trash"></i></button>` : ''}
                    ${u.id != <?= $_SESSION['usuario_id'] ?> ? `<button class="btn-almacenatec toggle" onclick="toggleUsuario(${u.id}, '${u.activo ? 'deactivate' : 'activate'}')"><i class="bi ${u.activo ? 'bi-pause-circle' : 'bi-play-circle'}"></i></button>` : ''}
                `;
            } else {
                if (u.rol === 'usuario') {
                    botonesAccion = `
                        <button class="btn-almacenatec editar" onclick="abrirModalUsuario({id:${u.id},nombre:'${u.nombre}',username:'${u.username}',rol:'${u.rol}',departamento:'${u.departamento || ''}'})" title="Asignar Rol"><i class="bi bi-pencil"></i> Asignar Rol</button>
                    `;
                } else {
                    botonesAccion = `<span style="font-size:11px;color:#94A3B8;">Solo lectura</span>`;
                }
            }

            tr.innerHTML = `
                <td><span style="font-weight:600;color:#0B2D6B">${u.id}</span></td>
                <td>${u.nombre}</td>
                <td>${u.username}</td>
                <td><span class="badge-almacenatec ${rolesBadge[u.rol] || 'usuario'}">${rolesLabels[u.rol] || u.rol}</span></td>
                <td>${esJefe ? (u.departamento || '—') : '—'}</td>
                <td><i class="bi ${estadoIcon} ${estado === 'activo' ? 'badge-almacenatec activo' : 'badge-almacenatec inactivo'}"></i></td>
                <td>${u.last_login ? new Date(u.last_login).toLocaleString() : 'Nunca'}</td>
                <td>${botonesAccion}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function cargarUsuarios() {
        fetch('<?= BASE_PATH ?>includes/get_usuarios.php')
            .then(r => r.json()).then(d => {
                if (!d.ok) return alert('Error al cargar usuarios: ' + d.error);
                todosLosUsuarios = d.usuarios; // Guardamos para el buscador
                renderTabla(todosLosUsuarios);
            });
    }

    // LÓGICA DEL BUSCADOR EN TIEMPO REAL
    document.addEventListener('DOMContentLoaded', function() {
        cargarUsuarios();

        document.getElementById('searchInput').addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            
            if (!q) {
                renderTabla(todosLosUsuarios);
                return;
            }

            const filtrados = todosLosUsuarios.filter(u => {
                const nombre = (u.nombre || '').toLowerCase();
                const username = (u.username || '').toLowerCase();
                const rol = (u.rol || '').toLowerCase();
                const depto = (u.departamento || '').toLowerCase();
                return nombre.includes(q) || username.includes(q) || rol.includes(q) || depto.includes(q);
            });

            renderTabla(filtrados);
        });
    });
</script>
</body>
</html>
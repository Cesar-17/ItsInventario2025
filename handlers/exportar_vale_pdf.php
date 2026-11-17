<?php
// handlers/exportar_vale_pdf.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Cargar dompdf (si usas Composer)
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'herramienta';

if (!$id) {
    die('ID inválido');
}

// Obtener datos del vale
if ($tipo === 'herramienta') {
    $stmt = $pdo->prepare("
        SELECT vh.*, 
               GROUP_CONCAT(dvh.descripcion SEPARATOR ' | ') as items_desc
        FROM vales_herramienta vh
        LEFT JOIN detalles_vale_herramienta dvh ON dvh.vale_herramienta_id = vh.id
        WHERE vh.id = ?
        GROUP BY vh.id
    ");
    $stmt->execute([$id]);
    $vale = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener detalles
    $stmt = $pdo->prepare("
        SELECT dvh.*, p.descripcion as producto_desc, u.abreviatura as unidad
        FROM detalles_vale_herramienta dvh
        LEFT JOIN productos p ON p.id = dvh.producto_id
        LEFT JOIN unidades_medida u ON u.id = dvh.unidad_medida_id
        WHERE dvh.vale_herramienta_id = ?
    ");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $titulo = 'VALE DE HERRAMIENTA';
    $color = '#d97706';
    
} else {
    $stmt = $pdo->prepare("
        SELECT vm.*, 
               GROUP_CONCAT(ds.descripcion SEPARATOR ' | ') as items_desc
        FROM vales_material vm
        LEFT JOIN detalles_salida ds ON ds.salida_id = vm.id
        WHERE vm.id = ?
        GROUP BY vm.id
    ");
    $stmt->execute([$id]);
    $vale = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Obtener detalles
    $stmt = $pdo->prepare("
        SELECT ds.*, p.descripcion as producto_desc, u.abreviatura as unidad
        FROM detalles_salida ds
        LEFT JOIN productos p ON p.id = ds.producto_id
        LEFT JOIN unidades_medida u ON u.id = ds.unidad_medida_id
        WHERE ds.salida_id = ?
    ");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $titulo = 'VALE POR MATERIAL';
    $color = '#0b2d6b';
}

if (!$vale) {
    die('Vale no encontrado');
}

// Generar HTML para PDF
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $titulo . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            padding: 40px; 
            background: #fff;
        }
        .header {
            background: ' . $color . ';
            color: #fff;
            padding: 20px 24px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .header h1 { font-size: 24px; margin: 0; }
        .header small { color: rgba(255,255,255,0.8); }
        .content { padding: 24px; border: 1px solid #e5e7eb; border-top: none; }
        .row { display: flex; margin-bottom: 12px; }
        .label { font-weight: 600; width: 150px; color: #6b7280; }
        .value { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { 
            background: ' . $color . '; 
            color: #fff; 
            padding: 10px 12px; 
            text-align: left;
            font-size: 12px;
        }
        td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
        .total { 
            text-align: right; 
            font-weight: 700; 
            font-size: 18px; 
            padding-top: 16px;
            border-top: 2px solid #e5e7eb;
            margin-top: 16px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 2px dashed #e5e7eb;
            display: flex;
            justify-content: space-between;
        }
        .footer .firma { text-align: center; }
        .footer .firma .linea { 
            width: 200px; 
            border-bottom: 1px solid #000; 
            margin: 20px auto 4px; 
        }
        .footer .firma .label { font-size: 11px; color: #6b7280; }
        .badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pendiente { background: #fef3c7; color: #d97706; }
        .badge-autorizado { background: #dbeafe; color: #0b2d6b; }
        .badge-entregado { background: #d1fae5; color: #059669; }
        .badge-cancelado { background: #fee2e2; color: #dc2626; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>' . $titulo . '</h1>
        <small>TecNM Campus Saltillo</small>
    </div>

    <div class="content">
        <div class="row">
            <div class="label">Folio:</div>
            <div class="value"><strong>' . htmlspecialchars($vale['folio']) . '</strong></div>
        </div>
        <div class="row">
            <div class="label">Fecha:</div>
            <div class="value">' . date('d/m/Y', strtotime($vale['fecha'])) . '</div>
        </div>
        <div class="row">
            <div class="label">Solicita:</div>
            <div class="value">' . htmlspecialchars($vale['solicita']) . '</div>
        </div>
        ' . (isset($vale['area_uso']) ? '
        <div class="row">
            <div class="label">Área de uso:</div>
            <div class="value">' . htmlspecialchars($vale['area_uso']) . '</div>
        </div>
        ' : '') . '
        <div class="row">
            <div class="label">Autoriza:</div>
            <div class="value">' . htmlspecialchars($vale['autoriza']) . '</div>
        </div>
        <div class="row">
            <div class="label">Estatus:</div>
            <div class="value"><span class="badge badge-' . $vale['estatus'] . '">' . ucfirst($vale['estatus']) . '</span></div>
        </div>
        
        <div style="margin-top:16px;">
            <div style="font-weight:600;margin-bottom:8px;">Descripción:</div>
            <div style="background:#f8faff;padding:12px 16px;border-radius:6px;">' . nl2br(htmlspecialchars($vale['descripcion'])) . '</div>
        </div>';

// Detalles
if (!empty($detalles)) {
    $html .= '
        <div style="margin-top:16px;">
            <div style="font-weight:600;margin-bottom:8px;">Detalles:</div>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Descripción</th>
                        <th style="text-align:center">Cantidad</th>
                        <th style="text-align:center">Unidad</th>
                        <th style="text-align:right">Precio</th>
                        <th style="text-align:right">Importe</th>
                    </tr>
                </thead>
                <tbody>';
    $total = 0;
    foreach ($detalles as $det) {
        $importe = $det['cantidad'] * $det['precio_unitario'];
        $total += $importe;
        $html .= '
            <tr>
                <td>' . htmlspecialchars($det['codigo_barras'] ?? '') . '</td>
                <td>' . htmlspecialchars($det['descripcion'] ?? '') . '</td>
                <td style="text-align:center">' . $det['cantidad'] . '</td>
                <td style="text-align:center">' . htmlspecialchars($det['unidad'] ?? 'pz') . '</td>
                <td style="text-align:right">$' . number_format($det['precio_unitario'], 2) . '</td>
                <td style="text-align:right">$' . number_format($importe, 2) . '</td>
            </tr>';
    }
    $html .= '
                </tbody>
            </table>
            <div class="total">Total: $' . number_format($total, 2) . '</div>
        </div>';
}

$html .= '
        <div class="footer">
            <div class="firma">
                <div class="linea"></div>
                <div class="label">_____________________</div>
                <div class="label">Solicita</div>
            </div>
            <div class="firma">
                <div class="linea"></div>
                <div class="label">_____________________</div>
                <div class="label">Autoriza</div>
            </div>
            <div class="firma">
                <div class="linea"></div>
                <div class="label">_____________________</div>
                <div class="label">Vo.Bo. Almacén</div>
            </div>
        </div>
        
        <div style="text-align:center;font-size:11px;color:#6b7280;margin-top:24px;border-top:1px solid #e5e7eb;padding-top:12px;">
            <i class="bi bi-geo-alt-fill"></i> TecNM Campus Saltillo · ' . date('Y') . '
        </div>
    </div>

</body>
</html>';

// Configurar dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Descargar PDF
$filename = $titulo . '_' . $vale['folio'] . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
?>
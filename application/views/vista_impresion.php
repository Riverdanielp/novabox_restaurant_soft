<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Documento para Impresora Matricial</title>
    <style>
        /* Configuración clave: forzar orientación portrait */
        @page {
            size: 140mm 140mm; /* ALTO x ANCHO (invertido para portrait) */
            margin: 0;
            /* Chrome necesita esta declaración explícita */
            size: portrait;
        }
        
        body {
            margin: 0;
            padding: 0;
            width: 140mm;  /* Ancho físico real del papel (14cm) */
            height: 140mm; /* Alto físico real del papel (22cm) */
            font-family: 'Courier New', monospace;
            font-size: 12pt;
        }
        
        .documento {
            width: 140mm;
            height: 140mm;
            padding: 5mm;
            box-sizing: border-box;
        }
        
        /* Rotación del contenido (opcional) */
        .contenido-vertical {
            transform: rotate(0deg);
            transform-origin: top left;
        }
        
        @media print {
            /* Resetear márgenes al imprimir */
            body, .documento {
                margin: 0;
                padding: 0;
            }
            
            /* Ocultar elementos no necesarios */
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="documento">
        <div class="contenido-vertical">
            <!-- Encabezado -->
            <div style="text-align: center;">
                <h2>COMPROBANTE DE VENTA</h2>
                <hr>
                <p><strong>Empresa:</strong> MI EMPRESA S.A.</p>
                <p><strong>Dirección:</strong> Calle Principal 123</p>
                <p><strong>Teléfono:</strong> 555-1234</p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                <hr>
            </div>
            
            <!-- Detalle -->
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000;">Código</th>
                        <th style="border: 1px solid #000;">Descripción</th>
                        <th style="border: 1px solid #000;">Cant.</th>
                        <th style="border: 1px solid #000;">P.Unit</th>
                        <th style="border: 1px solid #000;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000;">ART-001</td>
                        <td style="border: 1px solid #000;">Producto de ejemplo 1</td>
                        <td style="border: 1px solid #000;">2</td>
                        <td style="border: 1px solid #000;">$150.00</td>
                        <td style="border: 1px solid #000;">$300.00</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000;">ART-002</td>
                        <td style="border: 1px solid #000;">Producto de ejemplo 2</td>
                        <td style="border: 1px solid #000;">1</td>
                        <td style="border: 1px solid #000;">$75.50</td>
                        <td style="border: 1px solid #000;">$75.50</td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Totales -->
            <div style="margin-top: 10px;">
                <hr>
                <p style="text-align: right;"><strong>SUBTOTAL: $375.50</strong></p>
                <p style="text-align: right;"><strong>IVA (10%): $37.55</strong></p>
                <p style="text-align: right;"><strong>TOTAL: $413.05</strong></p>
            </div>
            
        </div>
    </div>

    <!-- Botón de impresión para pruebas (no aparece al imprimir) -->
    <div class="no-print" style="position: fixed; top: 10px; right: 10px;">
        <button onclick="window.print()" style="padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer;">
            Imprimir Documento
        </button>
    </div>
</body>
</html>
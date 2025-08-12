function printDivOld(divName) {
    let printContents = document.getElementById(divName).innerHTML;
    let  originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}
function printDiv(divName) {
    let printContents = document.getElementById(divName).innerHTML;
    let win = window.open('', '', 'height=400,width=600');
    win.document.write('<html><head><title>Etiqueta</title>');
    // IMPORTANTE: incluye tus estilos de impresión aquí:
    win.document.write(`
        <style>
        @media print {
            @page {
                size: 80mm 30mm;
                margin: 0;
            }
            body, html {
                width: 80mm;
                height: 30mm;
                margin: 0 !important;
                padding: 0 !important;
                background: white;
            }
            #printableArea {
                width: 80mm;
                height: 30mm;
                display: flex;
                justify-content: center;
                align-items: center;
                margin: 0;
                padding: 0;
                background: white;
            }
            .etiqueta {
                width: 76mm;
                height: 26mm;
                margin: 2mm auto;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                border-radius: 3mm;
                background: white;
                font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            }
            .sucursal {
                font-size: 8pt;
                font-weight: bold;
                text-align: center;
                color: #222;
                margin-bottom: 0.2mm;
                letter-spacing: 0.5mm;
            }
            .barcode {
                display: block;
                margin: 0 auto;
                height: 5mm !important;
                width: 40mm !important;
            }
            .codigo {
                font-size: 8pt;
                font-family: 'Courier New', Courier, monospace;
                letter-spacing: 2px;
                text-align: center;
                margin: 0.5mm 0 1.2mm 0;
                color: #444;
            }
            .producto {
                font-size: 9pt;
                font-weight: 500;
                text-align: center;
                color: #222;
                margin: 0.5mm 0 1mm 0;
            }
            .precio {
                font-size: 16pt;
                font-weight: bold;
                text-align: center;
                color: #007bff;
                margin-top: 0.5mm;
                letter-spacing: 1px;
            }
        }
        </style>
    `);
    win.document.write('</head><body>');
    win.document.write(printContents);
    win.document.write('</body></html>');
    win.document.close();
    win.focus();
    win.print();
    win.close();
}
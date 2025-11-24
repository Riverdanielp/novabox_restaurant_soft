<?php
/**
 * Script de Migración: Sincronizar Pagos Antiguos con Ventas
 * 
 * Este script recorre todos los pagos existentes y los asigna a las ventas
 * correspondientes, actualizando el campo paid_due_amount de cada venta.
 * 
 * IMPORTANTE: 
 * - Ejecutar UNA SOLA VEZ por terminal
 * - SOLO accesible desde CLI (línea de comandos) por seguridad
 * - Excluye pagos negativos (saldos iniciales)
 * 
 * Uso desde terminal:
 * php index.php Migration_sync_payments index {COMPANY_ID} {TOKEN}
 * 
 * Token = hash('sha256', COMPANY_ID . date('Y-m-d') . 'MIGRATION_2025')
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sync_payments extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Common_model');
        $this->Common_model->setDefaultTimezone();
    }

    /**
     * Ejecutar migración de pagos antiguos
     * SOLO desde terminal
     * 
     * @param int $company_id ID de la compañía
     */
    public function index($company_id = null) {
        // BLOQUEAR acceso desde navegador
        if (!$this->input->is_cli_request()) {
            show_error('Este script solo puede ejecutarse desde terminal por seguridad', 403, 'Acceso Denegado');
            return;
        }
        
        // Validar parámetros
        if (!$company_id) {
            echo "ERROR: Falta company_id\n";
            echo "Uso: php index.php Migration_sync_payments index {COMPANY_ID}\n\n";
            echo "Ejemplo:\n";
            echo "php index.php Migration_sync_payments index 1\n";
            return;
        }
        
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║       MIGRACIÓN DE PAGOS ANTIGUOS - INICIO               ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "Company ID: $company_id\n";
        echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        
        $stats = [
            'total_payments' => 0,
            'processed_payments' => 0,
            'skipped_payments' => 0,
            'negative_payments_skipped' => 0,
            'total_sales_updated' => 0,
            'total_amount' => 0,
            'errors' => []
        ];
        
        // Contar pagos negativos (solo para estadística)
        $this->db->where('del_status', 'Live');
        $this->db->where('amount <', 0);
        $this->db->where('company_id', $company_id);
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM tbl_customer_due_receives_sales cdrs 
            WHERE cdrs.due_receive_id = tbl_customer_due_receives.id
        )', NULL, FALSE);
        $stats['negative_payments_skipped'] = $this->db->count_all_results('tbl_customer_due_receives');
        
        // Obtener todos los pagos POSITIVOS que NO tienen registros en tbl_customer_due_receives_sales
        $this->db->select('cdr.*');
        $this->db->from('tbl_customer_due_receives cdr');
        $this->db->where('cdr.del_status', 'Live');
        $this->db->where('cdr.company_id', $company_id);
        $this->db->where('cdr.amount >', 0); // SOLO pagos positivos - excluir negativos
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM tbl_customer_due_receives_sales cdrs 
            WHERE cdrs.due_receive_id = cdr.id
        )', NULL, FALSE);
        $this->db->order_by('cdr.date', 'ASC');
        
        $payments = $this->db->get()->result();
        $stats['total_payments'] = count($payments);
        
        echo "Pagos positivos sin asignar: {$stats['total_payments']}\n";
        echo "Pagos negativos (saldos iniciales) omitidos: {$stats['negative_payments_skipped']}\n\n";
        
        if ($stats['total_payments'] == 0) {
            echo "✓ No hay pagos pendientes de sincronizar\n";
            echo "\n╔══════════════════════════════════════════════════════════╗\n";
            echo "║            MIGRACIÓN COMPLETADA - SIN CAMBIOS            ║\n";
            echo "╚══════════════════════════════════════════════════════════╝\n";
            return;
        }
        
        echo "Procesando pagos...\n";
        echo str_repeat("─", 80) . "\n";
        
        foreach ($payments as $index => $payment) {
            echo "\n[" . ($index + 1) . "/{$stats['total_payments']}] ";
            echo "Pago ID: {$payment->id} - Ref: {$payment->reference_no}\n";
            
            // Obtener nombre del cliente
            $customer = $this->db->get_where('tbl_customers', ['id' => $payment->customer_id])->row();
            $customer_name = $customer ? $customer->name : 'Desconocido';
            
            echo "  Cliente: {$customer_name} (ID: {$payment->customer_id})\n";
            echo "  Monto: $" . number_format($payment->amount, 2) . "\n";
            echo "  Fecha: {$payment->date}\n";
            
            try {
                // Obtener ventas pendientes del cliente en la fecha del pago
                $this->db->select('id, sale_no, sale_date, due_amount, paid_due_amount, remaining_due');
                $this->db->from('tbl_sales');
                $this->db->where('customer_id', $payment->customer_id);
                $this->db->where('outlet_id', $payment->outlet_id);
                $this->db->where('del_status', 'Live');
                $this->db->where('order_status', '3');
                $this->db->where('due_amount >', 0);
                $this->db->where('sale_date <=', $payment->only_date);
                $this->db->order_by('sale_date', 'ASC');
                
                $sales = $this->db->get()->result();
                
                if (empty($sales)) {
                    echo "  ⚠ No se encontraron ventas. SALTADO.\n";
                    $stats['skipped_payments']++;
                    continue;
                }
                
                echo "  Ventas encontradas: " . count($sales) . "\n";
                
                // Distribuir el monto del pago entre las ventas
                $remaining_amount = $payment->amount;
                $sales_updated = 0;
                
                foreach ($sales as $sale) {
                    if ($remaining_amount <= 0) {
                        break;
                    }
                    
                    // Calcular cuánto se puede abonar a esta venta
                    $current_remaining = $sale->due_amount - $sale->paid_due_amount;
                    
                    if ($current_remaining <= 0) {
                        continue; // Ya está pagada completamente
                    }
                    
                    $amount_to_apply = min($remaining_amount, $current_remaining);
                    
                    // Insertar en tbl_customer_due_receives_sales
                    $sale_payment_data = [
                        'due_receive_id' => $payment->id,
                        'sale_id' => $sale->id,
                        'amount' => $amount_to_apply,
                        'created_at' => $payment->date
                    ];
                    
                    $this->db->insert('tbl_customer_due_receives_sales', $sale_payment_data);
                    
                    // Actualizar paid_due_amount en tbl_sales
                    $this->db->set('paid_due_amount', 'paid_due_amount + ' . $amount_to_apply, FALSE);
                    $this->db->where('id', $sale->id);
                    $this->db->update('tbl_sales');
                    
                    $remaining_amount -= $amount_to_apply;
                    $sales_updated++;
                    
                    echo "    ✓ Venta {$sale->sale_no}: Abonado $" . number_format($amount_to_apply, 2) . "\n";
                }
                
                echo "  ✓ Pago procesado. Ventas actualizadas: {$sales_updated}\n";
                $stats['processed_payments']++;
                $stats['total_sales_updated'] += $sales_updated;
                $stats['total_amount'] += $payment->amount;
                
                if ($remaining_amount > 0.01) {
                    echo "  ⚠ Sobrante no distribuido: $" . number_format($remaining_amount, 2) . "\n";
                }
                
            } catch (Exception $e) {
                echo "  ✗ ERROR: " . $e->getMessage() . "\n";
                $stats['errors'][] = [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        echo "\n" . str_repeat("─", 80) . "\n";
        echo "\n╔══════════════════════════════════════════════════════════╗\n";
        echo "║              RESUMEN DE MIGRACIÓN                        ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "Pagos positivos encontrados: {$stats['total_payments']}\n";
        echo "Pagos procesados exitosamente: {$stats['processed_payments']}\n";
        echo "Pagos saltados (sin ventas): {$stats['skipped_payments']}\n";
        echo "Pagos negativos omitidos: {$stats['negative_payments_skipped']}\n";
        echo "Total ventas actualizadas: {$stats['total_sales_updated']}\n";
        echo "Monto total procesado: $" . number_format($stats['total_amount'], 2) . "\n";
        echo "Errores: " . count($stats['errors']) . "\n";
        
        if (!empty($stats['errors'])) {
            echo "\nDETALLE DE ERRORES:\n";
            foreach ($stats['errors'] as $error) {
                echo "  - Pago ID {$error['payment_id']}: {$error['error']}\n";
            }
        }
        
        echo "\n╔══════════════════════════════════════════════════════════╗\n";
        echo "║            MIGRACIÓN COMPLETADA EXITOSAMENTE             ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
    }

    /**
     * Verificar estado de sincronización
     * SOLO desde terminal
     * 
     * @param int $company_id ID de la compañía
     */
    public function verify($company_id = null) {
        // BLOQUEAR acceso desde navegador
        if (!$this->input->is_cli_request()) {
            show_error('Este script solo puede ejecutarse desde terminal por seguridad', 403, 'Acceso Denegado');
            return;
        }
        
        if (!$company_id) {
            echo "ERROR: Falta company_id\n";
            echo "Uso: php index.php Migration_sync_payments verify {COMPANY_ID}\n";
            return;
        }
        
        echo "╔══════════════════════════════════════════════════════════╗\n";
        echo "║         VERIFICACIÓN DE SINCRONIZACIÓN                   ║\n";
        echo "╚══════════════════════════════════════════════════════════╝\n";
        echo "Company ID: $company_id\n";
        echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";
        
        // Pagos positivos sin asignar
        $this->db->select('COUNT(*) as total');
        $this->db->from('tbl_customer_due_receives cdr');
        $this->db->where('cdr.del_status', 'Live');
        $this->db->where('cdr.company_id', $company_id);
        $this->db->where('cdr.amount >', 0);
        $this->db->where('NOT EXISTS (
            SELECT 1 FROM tbl_customer_due_receives_sales cdrs 
            WHERE cdrs.due_receive_id = cdr.id
        )', NULL, FALSE);
        $unassigned_positive = $this->db->get()->row()->total;
        
        // Pagos negativos (solo info)
        $this->db->where('del_status', 'Live');
        $this->db->where('company_id', $company_id);
        $this->db->where('amount <', 0);
        $negative_payments = $this->db->count_all_results('tbl_customer_due_receives');
        
        echo "PAGOS:\n";
        echo "  Pagos positivos sin asignar: {$unassigned_positive}\n";
        echo "  Pagos negativos (saldos iniciales): {$negative_payments}\n\n";
        
        // Ventas con inconsistencias
        $this->db->select('s.id, s.sale_no, s.paid_due_amount, COALESCE(SUM(cdrs.amount), 0) as calculated_paid');
        $this->db->from('tbl_sales s');
        $this->db->join('tbl_customer_due_receives_sales cdrs', 'cdrs.sale_id = s.id', 'left');
        $this->db->where('s.del_status', 'Live');
        $this->db->where('s.company_id', $company_id);
        $this->db->where('s.order_status', '3');
        $this->db->where('s.due_amount >', 0);
        $this->db->group_by('s.id');
        $this->db->having('s.paid_due_amount != COALESCE(SUM(cdrs.amount), 0)');
        $inconsistent_sales = $this->db->get()->result();
        
        echo "VENTAS:\n";
        echo "  Ventas con inconsistencias: " . count($inconsistent_sales) . "\n\n";
        
        if (count($inconsistent_sales) > 0) {
            echo "DETALLE DE INCONSISTENCIAS:\n";
            echo str_repeat("─", 100) . "\n";
            printf("%-8s %-20s %-18s %-18s %-18s\n", "ID", "Sale No", "Paid Amount", "Suma Real", "Diferencia");
            echo str_repeat("─", 100) . "\n";
            
            foreach ($inconsistent_sales as $sale) {
                $diff = $sale->paid_due_amount - $sale->calculated_paid;
                printf("%-8s %-20s $%-17.2f $%-17.2f $%-17.2f\n", 
                    $sale->id, 
                    $sale->sale_no, 
                    $sale->paid_due_amount, 
                    $sale->calculated_paid, 
                    $diff
                );
            }
            echo str_repeat("─", 100) . "\n\n";
        }
        
        if ($unassigned_positive == 0 && count($inconsistent_sales) == 0) {
            echo "╔══════════════════════════════════════════════════════════╗\n";
            echo "║    ✓ TODO SINCRONIZADO CORRECTAMENTE                    ║\n";
            echo "╚══════════════════════════════════════════════════════════╝\n";
        } else {
            echo "╔══════════════════════════════════════════════════════════╗\n";
            echo "║    ⚠ SE DETECTARON INCONSISTENCIAS                      ║\n";
            echo "║      Ejecute la migración para corregir                  ║\n";
            echo "╚══════════════════════════════════════════════════════════╝\n";
        }
    }
}

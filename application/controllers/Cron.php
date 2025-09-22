<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Controller for CodeIgniter 3
 *
 * Permite ejecutar tareas desde CLI (recomendado) o vía HTTP con token.
 * Incluye tareas de ejemplo y utilidades para parsear argumentos.
 *
 * Uso (CLI):
 *   php index.php cron help
 *   php index.php cron clear_cache
 *   php index.php cron cleanup_cart_files days=7 dry_run=1
 *   php index.php cron backup_db keep=10
 *   php index.php cron send_daily_reports email=admin@example.com
 *
 * Uso (HTTP, sólo si configuras un token):
 *   GET /index.php/cron/clear_cache?token=TU_TOKEN
 *   GET /index.php/cron/cleanup_cart_files?token=TU_TOKEN&days=7
 *
 * Seguridad:
 * - Por defecto sólo permite CLI. Para habilitar HTTP, define un token:
 *   $config['cron_secret'] = 'cambiame';  // en application/config/config.php
 *   // o define variable de entorno CRON_SECRET
 */
class Cron extends CI_Controller
{
	/** @var bool */
	private $is_cli = false;

	/** @var string|null */
	private $expected_token = null;

	public function __construct()
	{
		parent::__construct();

		// Determinar entorno
		$this->is_cli = (php_sapi_name() === 'cli' || defined('STDIN')) ? true : is_cli();

		// Cargar helpers comunes
		$this->load->helper(array('file', 'url', 'date'));
		$this->load->helper('security');

		// Preparar token esperado (si está configurado)
		$this->load->config('config', true);
		$token_cfg = $this->config->item('cron_secret');
		if (empty($token_cfg)) {
			$token_cfg = getenv('CRON_SECRET') ?: null;
		}
		$this->expected_token = $token_cfg ?: null;

		// Evitar timeouts para tareas largas
		if (function_exists('set_time_limit')) {
			@set_time_limit(0);
		}

		// Logging de invocación
		log_message('info', 'Cron invocado desde ' . ($this->is_cli ? 'CLI' : 'HTTP') . ' -> URI: ' . current_url());

        
        $this->load->helper('factura_send_helper');
        $this->load->library('facturasend'); // La librería es necesaria para el helper
        
	}

	/**
	 * Muestra ayuda y lista de tareas disponibles.
	 */
	public function index()
	{
		return $this->help();
	}

	/**
	 * Ayuda de uso
	 */
	public function help()
	{
		$help = array(
			'title'   => 'Cron - Tareas disponibles',
			'usage'   => array(
				'CLI'   => array(
					'php index.php cron help',
					'php index.php cron clear_cache',
					'php index.php cron cleanup_cart_files days=7 dry_run=1',
					'php index.php cron backup_db keep=10',
					'php index.php cron send_daily_reports email=admin@example.com',
				),
				'HTTP'  => array(
					'Habilita token con $config["cron_secret"] o env CRON_SECRET',
					site_url('cron/clear_cache?token=TU_TOKEN'),
					site_url('cron/cleanup_cart_files?token=TU_TOKEN&days=7'),
				)
			),
			'tasks'   => array(
				'clear_cache'         => 'Limpia cache de aplicación (application/cache) manteniendo .htaccess e index.html',
				'cleanup_cart_files'  => 'Elimina assets/cart_data_*.json antiguos. Args: days (int, por defecto 2), dry_run (0/1)',
				'backup_db'           => 'Genera respaldo de la base de datos en uploads/backups. Args: keep (int, conservar últimos N, por defecto 7)',
				'send_daily_reports'  => 'Ejemplo de tarea: genera reporte del día en uploads/reports. Args: email (opcional)',
				'test'                => 'Muestra una barra de carga de 5 segundos (sólo para CLI).'
			)
		);

		return $this->respond($help);
	}

	/**
	 * Muestra una barra de carga de 5 segundos. Sólo para CLI.
	 */
	public function test()
	{
		if (!$this->check_access()) {
			return $this->deny();
		}

		if (!$this->is_cli) {
			return $this->respond(array('status' => 'info', 'message' => 'Esta función solo está disponible en modo CLI.'), 400);
		}

		echo "Iniciando tarea de prueba (5 segundos)..." . PHP_EOL;
		$total_steps = 100;
		$bar_width = 50;
		$total_duration_us = 5 * 1000000; // 5 segundos en microsegundos
		$delay_us = $total_duration_us / $total_steps;

		for ($i = 0; $i <= $total_steps; $i++) {
			$percent = $i;
			$filled_width = round($bar_width * $percent / 100);
			$empty_width = $bar_width - $filled_width;

			$bar = '[' . str_repeat('=', $filled_width) . ($filled_width < $bar_width ? '>' : '') . str_repeat(' ', $empty_width) . ']';

			// \r mueve el cursor al inicio de la línea
			echo "\r" . sprintf('Progreso: %s %3d%%', $bar, $percent);

			if ($i < $total_steps) {
				usleep($delay_us);
			}
		}

		echo PHP_EOL . "Tarea de prueba completada." . PHP_EOL;
		// No uses $this->respond() aquí porque ya hemos enviado salida directa.
	}

	/**
	 * Limpia la cache de la app (application/cache) preservando archivos protectores.
	 */
	public function clear_cache()
	{
		if (!$this->check_access()) {
			return $this->deny();
		}

		$cache_path = APPPATH . 'cache';
		if (!is_dir($cache_path)) {
			return $this->respond(array('status' => 'ok', 'message' => 'No existe directorio de cache', 'path' => $cache_path));
		}

		$removed = 0;
		$errors  = array();

		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($cache_path, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($it as $file) {
			$path = $file->getPathname();
			$base = $file->getFilename();
			if ($base === '.htaccess' || $base === 'index.html' || $base === 'index.php') {
				continue;
			}
			try {
				if ($file->isDir()) {
					// No borres el directorio raíz cache
					if (realpath($path) !== realpath($cache_path)) {
						@rmdir($path);
					}
				} else {
					if (@unlink($path)) {
						$removed++;
					} else {
						$errors[] = $path;
					}
				}
			} catch (Exception $e) {
				$errors[] = $path . ' -> ' . $e->getMessage();
			}
		}

		log_message('info', 'clear_cache: archivos eliminados=' . $removed . ', errores=' . count($errors));

		return $this->respond(array(
			'status'  => 'ok',
			'removed' => $removed,
			'errors'  => $errors,
			'path'    => $cache_path,
		));
	}

	/**
	 * Elimina archivos assets/cart_data_*.json con antigüedad mayor a X días.
	 * Args:
	 *  - days: int (por defecto 2)
	 *  - dry_run: 0/1 (muestra qué haría sin borrar)
	 */
	public function cleanup_cart_files()
	{
		if (!$this->check_access()) {
			return $this->deny();
		}

		$args = $this->args(array(
			'days'    => 2,
			'dry_run' => 0,
		));

		$days   = (int) $args['days'];
		$dryRun = (int) $args['dry_run'] === 1;

		$dir = FCPATH . 'assets' . DIRECTORY_SEPARATOR;
		if (!is_dir($dir)) {
			return $this->respond(array('status' => 'error', 'message' => 'Directorio no encontrado', 'path' => $dir), 404);
		}

		$now = time();
		$threshold = $now - ($days * 86400);
		$pattern = '/^cart_data_\d+\.json$/i';

		$deleted = 0;
		$skipped = 0;
		$candidates = array();
		$errors = array();

		$dh = opendir($dir);
		if ($dh === false) {
			return $this->respond(array('status' => 'error', 'message' => 'No se pudo abrir directorio', 'path' => $dir), 500);
		}
		while (($file = readdir($dh)) !== false) {
			if (!preg_match($pattern, $file)) {
				continue;
			}
			$full = $dir . $file;
			if (!is_file($full)) {
				continue;
			}
			$mtime = @filemtime($full) ?: 0;
			if ($mtime > 0 && $mtime < $threshold) {
				$candidates[] = array('file' => $full, 'mtime' => $mtime);
			} else {
				$skipped++;
			}
		}
		closedir($dh);

		foreach ($candidates as $item) {
			if ($dryRun) {
				continue;
			}
			if (@unlink($item['file'])) {
				$deleted++;
			} else {
				$errors[] = $item['file'];
			}
		}

		log_message('info', sprintf('cleanup_cart_files: days=%d, dry_run=%d, candidatos=%d, borrados=%d, omitidos=%d', $days, $dryRun ? 1 : 0, count($candidates), $deleted, $skipped));

		return $this->respond(array(
			'status'      => 'ok',
			'days'        => $days,
			'dry_run'     => $dryRun ? 1 : 0,
			'candidates'  => $candidates,
			'deleted'     => $deleted,
			'skipped'     => $skipped,
			'errors'      => $errors,
			'path'        => $dir,
		));
	}

	/**
	 * Respaldo de BD en uploads/backups. Mantiene sólo los últimos N backups.
	 * Args:
	 *  - keep: int (por defecto 7)
	 */
	public function backup_db()
	{
		if (!$this->check_access()) {
			return $this->deny();
		}

		$args = $this->args(array('keep' => 7));
		$keep = max(1, (int) $args['keep']);

		$backup_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'backups';
		if (!is_dir($backup_dir)) {
			@mkdir($backup_dir, 0755, true);
		}
		if (!is_dir($backup_dir) || !is_writable($backup_dir)) {
			return $this->respond(array('status' => 'error', 'message' => 'Directorio no escribible', 'path' => $backup_dir), 500);
		}

		$this->load->dbutil();
		$this->load->helper('file');

		$date = date('Ymd_His');
		$sql_name = 'db_' . $date . '.sql';
		$zip_name = 'backup_' . $date . '.zip';

		$prefs = array(
			'format'      => 'zip',
			'filename'    => $sql_name,
			'foreign_key_checks' => false,
			'newline'     => "\n",
		);

		$backup = $this->dbutil->backup($prefs);
		if (!$backup) {
			return $this->respond(array('status' => 'error', 'message' => 'No se pudo generar backup (dbutil)'), 500);
		}

		$fullpath = $backup_dir . DIRECTORY_SEPARATOR . $zip_name;
		if (!write_file($fullpath, $backup)) {
			return $this->respond(array('status' => 'error', 'message' => 'No se pudo escribir el archivo', 'file' => $fullpath), 500);
		}

		// Política de retención
		$deleted = $this->rotate_backups($backup_dir, $keep);

		log_message('info', sprintf('backup_db: creado %s, reteniendo %d, eliminados=%d', $zip_name, $keep, $deleted));

		return $this->respond(array(
			'status'  => 'ok',
			'file'    => $fullpath,
			'keep'    => $keep,
			'deleted' => $deleted,
		));
	}

	/**
	 * Ejemplo de envío de reportes diarios (aquí genera un archivo de texto).
	 * Args:
	 *  - email: string (opcional)
	 */
	public function send_daily_reports()
	{
		if (!$this->check_access()) {
			return $this->deny();
		}

		$args = $this->args(array('email' => ''));
		$email = trim((string) $args['email']);

		$reports_dir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'reports';
		if (!is_dir($reports_dir)) {
			@mkdir($reports_dir, 0755, true);
		}

		$today  = date('Y-m-d');
		$file   = $reports_dir . DIRECTORY_SEPARATOR . 'daily_report-' . $today . '.txt';
		$lines  = array();
		$lines[] = 'Reporte diario - ' . $today;
		$lines[] = 'Generado: ' . date('c');
		$lines[] = '';
		$lines[] = 'Este es un ejemplo de reporte. Integra tus propias métricas aquí.';

		$ok = @file_put_contents($file, implode("\n", $lines));
		if ($ok === false) {
			return $this->respond(array('status' => 'error', 'message' => 'No se pudo escribir reporte', 'file' => $file), 500);
		}

		// Opcional: si se proporciona un email y está configurada la librería
		$mail_status = 'omitted';
		if ($email !== '') {
			if ($this->load->is_loaded('email') || @class_exists('CI_Email')) {
				$this->load->library('email');
				// En configuraciones reales, define protocol, smtp_host, etc. en application/config/email.php
				$this->email->from('no-reply@localhost', 'Cron');
				$this->email->to($email);
				$this->email->subject('Reporte diario ' . $today);
				$this->email->message('Se adjunta reporte diario.');
				$this->email->attach($file);
				$mail_status = $this->email->send() ? 'sent' : 'failed';
			} else {
				$mail_status = 'email_library_missing';
			}
		}

		log_message('info', sprintf('send_daily_reports: file=%s, email=%s, mail_status=%s', $file, $email ?: '-', $mail_status));

		return $this->respond(array(
			'status'      => 'ok',
			'file'        => $file,
			'email'       => $email ?: null,
			'mail_status' => $mail_status,
		));
	}

	// ===================== Utilidades =====================

	/**
	 * Comprueba acceso: permite CLI siempre; HTTP sólo si hay token válido.
	 */
	private function check_access()
	{
		if ($this->is_cli) {
			return true;
		}
		// Si no hay token configurado, sólo CLI
		if (empty($this->expected_token)) {
			return false;
		}
		$provided = $this->input->get('token');
		if (empty($provided)) {
			// También permite por header
			$provided = $this->input->get_request_header('X-CRON-TOKEN');
		}
		return is_string($provided) && hash_equals($this->expected_token, $provided);
	}

	/**
	 * Respuesta de denegado
	 */
	private function deny()
	{
		$msg = 'Acceso denegado. Usa CLI o configura un token (cron_secret / CRON_SECRET).';
		if ($this->is_cli) {
			echo $msg . PHP_EOL;
			return;
		}
		$this->output
			->set_status_header(403)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode(array('status' => 'error', 'message' => $msg), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			->_display();
		exit;
	}

	/**
	 * Responde en JSON para HTTP y texto para CLI.
	 * @param mixed $data
	 * @param int $status_code
	 */
	private function respond($data, $status_code = 200)
	{
		if ($this->is_cli) {
			if (is_array($data) || is_object($data)) {
				echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
			} else {
				echo (string) $data . PHP_EOL;
			}
			return;
		}
		$this->output
			->set_status_header($status_code)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			->_display();
		exit;
	}

	/**
	 * Parseo básico de argumentos desde CLI (segments) y HTTP (GET).
	 * - Para CLI acepta key=value a partir del 3er segmento.
	 * - Para HTTP toma $_GET y sobreescribe valores por segmentos si existen.
	 * @param array $defaults
	 * @return array
	 */
	private function args(array $defaults = array())
	{
		$args = $defaults;

		// 1) HTTP GET
		foreach ($this->input->get() as $k => $v) {
			if ($k === 'token') { continue; }
			$args[$k] = $v;
		}

		// 2) CLI segments key=value
		$segments = $this->uri->segment_array();
		// controller(1) / method(2) / args(3..n)
		for ($i = 3; $i <= count($segments); $i++) {
			$seg = $segments[$i];
			if (strpos($seg, '=') !== false) {
				list($k, $v) = explode('=', $seg, 2);
				$k = trim($k);
				if ($k !== '') {
					$args[$k] = $v;
				}
			}
		}

		return $args;
	}

	/**
	 * Elimina backups antiguos dejando sólo $keep más recientes.
	 * @param string $dir
	 * @param int $keep
	 * @return int cantidad eliminada
	 */
	private function rotate_backups($dir, $keep)
	{
		$files = glob($dir . DIRECTORY_SEPARATOR . 'backup_*.zip');
		if (!$files) { return 0; }

		// Ordenar por fecha de modificación desc
		usort($files, function ($a, $b) {
			return filemtime($b) - filemtime($a);
		});

		$deleted = 0;
		for ($i = $keep; $i < count($files); $i++) {
			if (@unlink($files[$i])) {
				$deleted++;
			}
		}
		return $deleted;
	}

    
    /**
     * Tarea CRON para sincronizar los estados de los documentos electrónicos pendientes.
     * 
     * Este método busca facturas con estado 'Generado' (0) o 'Enviado' (1),
     * consulta su estado actual en la API de FacturaSend y actualiza la base de datos local.
     * 
     * Para ejecutar desde el servidor, usa el siguiente comando:
     * php /ruta/completa/a/tu/proyecto/index.php cron sync_facturasend_estados
     */
    public function sync_facturasend_estados() {
        echo "--- Iniciando sincronización de estados de FacturaSend ---\n";
        
        try {
            // Llamamos a la función del helper con un lote de 100 (puedes ajustarlo)
            $resultado = fs_facturasend_actualizar_estados_pendientes(100);

            if ($resultado['success']) {
                $msg = sprintf(
                    "Sincronización finalizada.\nProcesados: %d\nActualizados: %d\nErrores de lote: %d\n",
                    $resultado['procesados'] ?? 0,
                    $resultado['actualizados'] ?? 0,
                    $resultado['errores'] ?? 0
                );
                echo $msg;

                // Opcional: Loguear detalles si los hay
                if (!empty($resultado['detalles'])) {
                    log_message('info', 'Detalles de la sincronización de estados: ' . json_encode($resultado['detalles']));
                }

            } else {
                $error_msg = 'Error en la sincronización: ' . ($resultado['message'] ?? 'Error desconocido.');
                echo $error_msg . "\n";
                log_message('error', $error_msg . ' Respuesta completa: ' . json_encode($resultado));
            }

        } catch (Exception $e) {
            $exception_msg = 'Excepción capturada durante la sincronización: ' . $e->getMessage();
            echo $exception_msg . "\n";
            log_message('error', $exception_msg);
        }

        echo "--- Sincronización de estados de FacturaSend terminada ---\n";
    }

}


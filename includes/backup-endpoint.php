<?php
// Verify WordPress environment
if (!defined('ABSPATH')) {
    error_log('Unauthorized backup access from: '.$_SERVER['REMOTE_ADDR']);
    exit;
}

// Security checks - Only define if not already set
if (!defined('WP_USE_THEMES')) {
    define('WP_USE_THEMES', false);
}
require_once(ABSPATH . 'wp-load.php');

// Final validation
if (!current_user_can('export') || !wp_validate_auth_cookie('', 'logged_in')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Database backup implementation
global $wpdb;

// Secure backup directory inside wp-content
$backup_dir = WP_CONTENT_DIR . '/backups/';
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0750, true)) {
        die('Failed to create backup directory');
    }
    // Add .htaccess protection
    file_put_contents($backup_dir . '.htaccess', "Deny from all\n");
}

$backup_file = $backup_dir . 'backup-' . DB_NAME . '-' . date("Y-m-d-His") . '.sql.gz';

// Validate backup path
if (strpos(realpath($backup_dir), realpath($backup_file)) !== 0) {
    die('Invalid backup path');
}

// Create backup file
$gz = gzopen($backup_file, 'w9');
if (!$gz) {
    die("Failed to create backup file");
}

// Get tables list
$tables = $wpdb->get_col("SHOW TABLES");
if (empty($tables)) {
    gzclose($gz);
    die('No tables found');
}

foreach ($tables as $table) {
    $table = esc_sql($table);
    
    // Table structure
    $create_table = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
    if ($create_table) {
        gzwrite($gz, "-- Table structure for $table\n");
        gzwrite($gz, $create_table[1] . ";\n\n");
    }

    // Table data
    $offset = 0;
    $chunk_size = 1000;
    do {
        $data = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `$table` LIMIT %d OFFSET %d",
            $chunk_size,
            $offset
        ), ARRAY_A);

        if ($data) {
            $buffer = "-- Data for $table (chunk $offset)\n";
            foreach ($data as $row) {
                $values = array_map([$wpdb, '_escape'], $row);
                $buffer .= "INSERT INTO `$table` (`" . implode('`, `', array_keys($row)) 
                         . "`) VALUES ('" . implode("', '", $values) . "');\n";
            }
            gzwrite($gz, $buffer);
            $offset += $chunk_size;
        }
    } while (!empty($data));
    
    gzwrite($gz, "\n");
}

gzclose($gz);

// Set secure permissions
chmod($backup_file, 0640);

// Cleanup old backups (30 days)
$files = glob($backup_dir . '*.sql.gz');
$now = time();
foreach ($files as $file) {
    if (is_file($file) && ($now - filemtime($file)) > 2592000) { // 30 days in seconds
        unlink($file);
    }
}
wp_redirect(admin_url());
exit;
<?php
declare(strict_types=1);

namespace Dionnie\Helpers;

class LogHelper {

    /**
     * Logs data to the WordPress debug.log file if WP_DEBUG is enabled.
     *
     * @param mixed $data The data to log (string, array, object, etc.)
     * @param string $prefix An optional prefix to easily find your log entries.
     */
    public static function debug($data, string $prefix = 'DIONNIE_PLUGIN'): void {
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            if (is_array($data) || is_object($data)) {
                $data = print_r($data, true);
            }
            
            $message = sprintf('[%s] %s', $prefix, $data);
            
            error_log($message);
        }
    }
}
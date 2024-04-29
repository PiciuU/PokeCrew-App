<?php

namespace Framework\Console\Commands;

/**
 * Class Maintenance
 *
 * This class defines maintenance mode commands to control and check the status of maintenance mode in the framework.
 * It allows enabling and disabling maintenance mode and provides feedback on its status.
 *
 * @package Framework\Console\Commands
 */
class Maintenance
{
    /**
     * The path where maintenance files are stored.
     *
     * @var string
     */
    private $storagePath = 'storage/framework/';

    /**
     * The filename for the maintenance mode file.
     *
     * @var string
     */
    private $maintenanceFileName = 'maintenance.php';

    /**
     * The filename for the maintenance configuration file.
     *
     * @var string
     */
    private $maintenanceConfigName = 'maintenance_config';

    /**
     * Execute the default command and provide feedback on maintenance status.
     */
    public function execute() : void
    {
        echo $this->isMaintenanceEnabled()
        ? <<<EOT
        Maintenance mode is currently active.
        You can disable it with command "php dfork maintenance:disable"
        EOT
        : <<<EOT
        Maintenance mode is currently inactive.
        You can enable it with command "php dfork maintenance:enable"
        EOT;
    }

    /**
     * Enable maintenance mode, displaying a custom HTML page or a default message.
     * This method creates the 'maintenance.php' and 'maintenance_config' files with the specified content.
     *
     * @throws \Exception If unable to create the 'maintenance.php' or 'maintenance_config' file.
     */
    public function enable() : void
    {
        if ($this->isMaintenanceEnabled()) {
            throw new \Exception("Maintenance mode is already active.");
        }

        $content = <<<'EOT'
        <?php

        // Check if the application is in maintenance mode
        if (!file_exists($cfg = __DIR__.'/@var(maintenanceConfigName)')) {
            return;
        }

        // Decode the JSON content of the configuration file
        $data = json_decode(file_get_contents($cfg), true);

        // Allow the framework to handle maintenance mode bypass based on whitelisted IP addresses
        if (isset($data['whitelist_ip']) && !empty($data['whitelist_ip'])) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip_address = $_SERVER['HTTP_CLIENT_IP'];
            } elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip_address = $_SERVER['REMOTE_ADDR'];
            }

            foreach($data['whitelist_ip'] as $whitelist_ip) {
                if ($_SERVER['REMOTE_ADDR'] === $whitelist_ip) return;
            }
        }

        // Allow the framework to handle maintenance mode bypass based on a secret key
        if (isset($data['secret']) && str_ends_with($_SERVER['REQUEST_URI'], "?secret_key=".$data['secret'])) {
            return;
        }

        // Redirect to the specified path if necessary
        if (isset($data['redirect']) && !empty($data['redirect']) && $_SERVER['REQUEST_URI'] !== $data['redirect']) {
            http_response_code(302);
            header('Location: '.$data['redirect']);

            exit;
        }

        // Output the maintenance template
        http_response_code($data['status'] ?? 503);

        if (isset($data['retry'])) {
            header('Retry-After: '.$data['retry']);
        }

        if (isset($data['refresh'])) {
            header('Refresh: '.$data['refresh']);
        }

        // Display a default message if no custom template is specified or the template file is not found
        if (!isset($data['template']) || !file_exists($template = __DIR__.$data['template'])) {
            echo "Application is currently in maintenance mode.";
        } else {
            require $template;
        }

        exit;
        EOT;

        $content = preg_replace_callback('/@var\s?\(\s*(.+?)\s*\);?/', fn ($matches) => $this->{$matches[1]}, $content);

        $dataContent = <<<EOT
        {
            "whitelist_ip": [],
            "template": "/resources/maintenance_view.php",
            "secret": null,
            "status": 503,
            "redirect": null,
            "refresh": null,
            "retry": null
        }
        EOT;

        if (file_put_contents($this->storagePath.$this->maintenanceFileName, $content) === false) {
            throw new \Exception("Failed to create the 'maintenance.php' file.");
        }

        if (file_put_contents($this->storagePath.$this->maintenanceConfigName, $dataContent) === false) {
            throw new \Exception("Failed to create the 'maintenance_config' file.");
        }

        echo "Maintenance mode has been activated.\n";
    }

    /**
     * Disable maintenance mode by removing the 'maintenance.php' and 'maintenance_config' files.
     *
     * @throws \Exception If unable to disable maintenance mode.
     */
    public function disable() : void
    {
        if (!$this->isMaintenanceEnabled()) {
            throw new \Exception("Maintenance mode is not active.");
        }

        if (unlink($this->storagePath.$this->maintenanceFileName) && unlink($this->storagePath.$this->maintenanceConfigName)) {
            echo "Maintenance mode has been deactivated.\n";
        } else {
            throw new \Exception("Failed to disable maintenance mode.");
        }
    }

    /**
     * Check if maintenance mode is enabled by verifying the existence of the 'maintenance.php' and 'maintenance_config' files.
     *
     * @return bool True if maintenance mode is enabled; otherwise, false.
     */
    private function isMaintenanceEnabled() : bool
    {
        return file_exists($this->storagePath.$this->maintenanceFileName) && file_exists($this->storagePath.$this->maintenanceConfigName);
    }
}
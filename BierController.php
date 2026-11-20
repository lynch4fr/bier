<?php

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;

class BierController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return true;
    }

    public function slug(): string
    {
        return 'bier';
    }

    public function icon(): string
    {
        return 'fa-lightbulb-o';
    }

    public function name(): string
    {
        return __('Bier');
    }

    public function data(Device $device, Request $request): array
    {
        $legacyFile = base_path('includes/html/pages/device/bier.inc.php');

        if (!is_file($legacyFile)) {
            throw new \RuntimeException('Fichier bier.inc.php introuvable');
        }

        ob_start();

        try {
            require $legacyFile;
            $html = ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return [
            'html' => $html,
        ];
    }
}

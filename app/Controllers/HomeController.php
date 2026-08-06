<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\SettingsModel;

/**
 * Public marketing landing page.
 */
final class HomeController extends BaseController
{
    /**
     * Displays the public landing page.
     */
    public function index(Request $request): Response
    {
        $settings = (new SettingsModel())->all();
        $brandName = $settings['school.name']['value'] ?? 'Zionex Solutions';
        $logoPath = $settings['school.logo']['value'] ?? '';
        $phone = (string) ($settings['school.phone']['value'] ?? '');
        $whatsappNumber = preg_replace('/\D+/', '', $phone) ?? '';
        if ($whatsappNumber !== '' && str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '234' . substr($whatsappNumber, 1);
        }

        return $this->renderView('home', [
            'title' => $brandName . ' | School Management System',
            'brandName' => $brandName,
            'logoUrl' => $logoPath ? asset(ltrim((string) $logoPath, '/')) : asset('assets/img/logo/school-logo.png'),
            'whatsappNumber' => $whatsappNumber,
        ]);
    }

    /**
     * Demonstrates JSON responses for future API-style endpoints.
     */
    public function health(Request $request): Response
    {
        return $this->json([
            'status' => 'ok',
            'app' => app_config('app.name'),
            'time' => date('c'),
        ]);
    }
}

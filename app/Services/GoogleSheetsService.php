<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetsService
{
    protected ?Sheets $service = null;

    protected function service(): Sheets
    {
        if ($this->service) {
            return $this->service;
        }

        $credentials = config('services.google_sheets.credentials_path');

        $client = new Client();
        $client->setAuthConfig($credentials);
        $client->addScope(Sheets::SPREADSHEETS_READONLY);

        return $this->service = new Sheets($client);
    }

    public function readSheet(string $spreadsheetId, string $range = 'A:Z'): array
    {
        $response = $this->service()->spreadsheets_values->get($spreadsheetId, $range);

        return $response->getValues() ?? [];
    }

    public function listTabs(string $spreadsheetId): array
    {
        $sheet = $this->service()->spreadsheets->get($spreadsheetId);

        $tabs = [];
        foreach ($sheet->getSheets() as $s) {
            $tabs[] = $s->getProperties()->getTitle();
        }

        return $tabs;
    }
}

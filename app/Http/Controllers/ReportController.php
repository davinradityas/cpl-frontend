<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ApiService $api) {}

    public function index()
    {
        $kurikulumProdis = $this->dataOf($this->api->getKurikulumProdis());
        $mataKuliahs     = $this->dataOf($this->api->getMataKuliahs());
        $angkatanData    = $this->dataOf($this->api->getAngkatanList());
        $angkatanList    = $angkatanData['angkatans'] ?? [];
        
        $mhsResp         = $this->api->getMahasiswas(['per_page' => 1000]);
        $mahasiswas      = $mhsResp['data'] ?? [];

        return view('reports.index', compact(
            'kurikulumProdis',
            'mataKuliahs',
            'angkatanList',
            'mahasiswas'
        ));
    }

    private function dataOf(array $response): array
    {
        return ($response['success'] ?? false) ? ($response['data'] ?? []) : [];
    }
}

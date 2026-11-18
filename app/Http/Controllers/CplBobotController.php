<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CplBobotController extends Controller
{
    public function __construct(protected ApiService $api) {}

    /**
     * Tampilkan halaman pengaturan bobot untuk CPL tertentu.
     * GET /cpls/{id}/bobot
     */
    public function index(int $id)
    {
        $response = $this->api->getCplBobotDetail($id);

        if (empty($response['success'])) {
            abort($response['status'] ?? 404, $response['message'] ?? 'CPL tidak ditemukan.');
        }

        $cpl = $response['data'];

        // Hitung bobot_persen dari pivot bobot (desimal 0-1 → persen)
        foreach ($cpl['mata_kuliahs'] as &$mk) {
            $mk['bobot_persen_input'] = $cpl['bobot_mode'] === 'manual'
                ? round((float)($mk['bobot'] ?? 0) * 100, 2)
                : $mk['bobot_persen'];  // bobot SKS sudah dalam %
        }
        unset($mk);

        return view('cpls.bobot', compact('cpl'));
    }

    /**
     * Simpan mode bobot + nilai bobot manual.
     * POST /cpls/{id}/bobot
     */
    public function update(Request $request, int $id)
    {
        $mode   = $request->input('bobot_mode', 'sks');
        $bobots = [];

        if ($mode === 'manual') {
            // Bangun array bobot dari form input
            $mkIds      = $request->input('mata_kuliah_ids', []);
            $bobotPersen = $request->input('bobot_persen', []);

            foreach ($mkIds as $idx => $mkId) {
                $bobots[] = [
                    'mata_kuliah_id' => (int) $mkId,
                    'bobot_persen'   => (float) ($bobotPersen[$idx] ?? 0),
                ];
            }
        }

        $response = $this->api->updateCplBobot($id, [
            'bobot_mode' => $mode,
            'bobots'     => $bobots,
        ]);

        if (!empty($response['success'])) {
            Session::flash('success', $response['message'] ?? 'Bobot CPL berhasil disimpan.');
        } else {
            Session::flash('error', $response['message'] ?? 'Gagal menyimpan bobot.');
        }

        return redirect()->route('cpls.bobot.index', $id);
    }
}

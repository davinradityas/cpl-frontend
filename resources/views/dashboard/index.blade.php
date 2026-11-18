@extends('layouts.app')

@section('content')
@php
    $semanticClass = function ($color) {
        return match ($color) {
            'success' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
            'warning' => 'bg-amber-50 text-amber-600 border-amber-200',
            'danger'  => 'bg-rose-50 text-rose-600 border-rose-200',
            'info'    => 'bg-blue-50 text-blue-600 border-blue-200',
            default   => 'bg-slate-50 text-slate-500 border-slate-200',
        };
    };

    $semanticIcon = function ($color) {
        return match ($color) {
            'success' => '✅',
            'warning' => '⚠️',
            'danger'  => '❌',
            'info'    => 'ℹ️',
            default   => '—',
        };
    };

    $chartItems = collect($items)->map(function ($item, $index) {
        $prodi      = $item['prodi'] ?? [];
        $summaryCpl = $item['summary_cpl'] ?? [];
        $cpls       = collect($summaryCpl['cpls'] ?? []);

        return [
            'id'              => 'cplRadarChart' . $index,
            'id_keseluruhan'  => 'cplRadarChartKeseluruhan' . $index,
            'title'           => $prodi['nama'] ?? 'Prodi',
            'subtitle'        => ($prodi['fakultas']['nama'] ?? ($item['fakultas_group'] ?? '-'))
                                  . ' · Kurikulum ' . ($item['kurikulum'] ?? '-'),
            'labels'          => $cpls->pluck('kode')->values(),
            'values'          => $cpls->map(fn($cpl) => (float) ($cpl['nilai_cpl'] ?? 0))->values(),
            'values_keseluruhan' => $cpls->map(fn($cpl) => (float) ($cpl['nilai_cpl_keseluruhan'] ?? 0))->values(),
            'average'         => (float) ($summaryCpl['rata_rata_semua'] ?? 0),
            'average_keseluruhan' => (float) ($summaryCpl['rata_rata_keseluruhan'] ?? 0),
            'target'          => (float) ($summaryCpl['target'] ?? 75),
        ];
    })->values();
@endphp

{{-- ===== HEADER ===== --}}
<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Dashboard</h2>
    <p class="mt-1 text-sm text-slate-500">
        Ketercapaian CPL per Program Studi dan Angkatan.
    </p>
</div>

@if($errorMessage)
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ $errorMessage }}
    </div>
@endif

{{-- ===== FILTER PANEL ===== --}}
<section class="mb-6 tsu-card rounded-lg p-5">
    <form id="dashboard-filter-form" method="GET" action="{{ route('dashboard.index') }}"
          class="grid gap-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_auto] lg:items-end">

        {{-- Pilih KP / Prodi --}}
        <div>
            <label for="kurikulum_prodi_id"
                   class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Program Studi
            </label>
            <select id="kurikulum_prodi_id" name="kurikulum_prodi_id"
                    class="w-full rounded-lg border border-[#d7e7e9] bg-white/95 px-3 py-2.5 text-sm outline-none
                           focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]"
                    @if($role === 'admin_kaprodi') disabled @endif>
                @if($role !== 'admin_kaprodi')
                    <option value="">— Pilih Program Studi —</option>
                @endif
                @foreach($allKps as $kp)
                    <option value="{{ $kp['id'] }}"
                        @selected((string)($filters['kurikulum_prodi_id'] ?? '') === (string)$kp['id'])>
                        {{ $kp['prodi']['nama'] ?? '-' }}
                        ({{ $kp['kurikulum']['kode'] ?? $kp['kurikulum'] ?? '-' }})
                    </option>
                @endforeach
            </select>
            @if($role === 'admin_kaprodi' && $selectedKp)
                {{-- Hidden input agar value tetap terkirim walau disabled --}}
                <input type="hidden" name="kurikulum_prodi_id" value="{{ $selectedKp['id'] }}">
            @endif
        </div>

        {{-- Pilih Angkatan --}}
        <div>
            <label for="angkatan"
                   class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                Angkatan
            </label>
            <select id="angkatan" name="angkatan"
                    class="w-full rounded-lg border border-[#d7e7e9] bg-white/95 px-3 py-2.5 text-sm outline-none
                           focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]"
                    @if(empty($angkatanList)) disabled @endif>
                @if(empty($angkatanList))
                    @if(empty($selectedKp))
                        <option value="">— Pilih Program Studi terlebih dahulu —</option>
                    @else
                        <option value="">— Belum ada data angkatan —</option>
                    @endif
                @else
                    {{-- Placeholder: paksa user pilih angkatan secara eksplisit --}}
                    <option value="" @selected(empty($filters['angkatan']))>— Pilih Angkatan —</option>
                    @foreach($angkatanList as $akt)
                        <option value="{{ $akt }}" @selected((string)($filters['angkatan'] ?? '') === (string)$akt)>
                            {{ $akt }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="tsu-primary-button rounded-lg px-4 py-2.5 text-sm font-semibold shadow-[0_10px_30px_rgba(14,122,134,0.08)]">
                Tampilkan
            </button>
            <a href="{{ route('dashboard.index') }}"
               class="tsu-secondary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
                Reset
            </a>
        </div>
    </form>

    {{-- Badge konteks aktif --}}
    @if($isReady)
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 border border-teal-200
                         px-3 py-1 text-xs font-semibold text-teal-700">
                📊 {{ $selectedKp['prodi']['nama'] ?? '-' }}
                &nbsp;·&nbsp;
                Angkatan {{ $filters['angkatan'] }}
            </span>
        </div>
    @endif
</section>

{{-- ===== STATE: Belum pilih KP ===== --}}
@if($needSelectKp)
    <div class="tsu-card rounded-lg p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#e8f6f7] text-2xl">
            🎓
        </div>
        <h3 class="text-base font-semibold text-slate-800">Pilih Program Studi</h3>
        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
            Pilih Program Studi di atas untuk menampilkan data ketercapaian CPL per angkatan.
        </p>
    </div>

{{-- ===== STATE: KP dipilih, angkatan belum dipilih ===== --}}
@elseif($needSelectAngk && !empty($angkatanList))
    <div class="tsu-card rounded-lg p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#e8f6f7] text-2xl">
            📅
        </div>
        <h3 class="text-base font-semibold text-slate-800">Pilih Angkatan</h3>
        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
            Program Studi sudah dipilih. Silakan pilih <strong>Angkatan</strong> lalu klik <strong>Tampilkan</strong>.
        </p>
    </div>

{{-- ===== STATE: KP dipilih tapi tidak ada data mahasiswa ===== --}}
@elseif($needSelectAngk && empty($angkatanList))
    <div class="tsu-card rounded-lg p-10 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-yellow-50 text-2xl">
            📂
        </div>
        <h3 class="text-base font-semibold text-slate-800">Belum Ada Data Mahasiswa</h3>
        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">
            Program Studi ini belum memiliki data mahasiswa terdaftar.
            Tambahkan mahasiswa terlebih dahulu agar dapat melihat data CPL.
        </p>
    </div>

{{-- ===== STATE: Data siap tampil ===== --}}
@elseif($isReady)

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="tsu-card tsu-card-hover rounded-lg p-5">
            <div class="text-sm text-slate-500">Total Matkul</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $totals['total_mk'] }}</div>
        </div>
        <div class="tsu-card tsu-card-hover rounded-lg p-5">
            <div class="text-sm text-slate-500">Total CPL</div>
            <div class="mt-2 text-3xl font-bold text-slate-900">{{ $totals['total_cpl'] }}</div>
        </div>
        <div class="tsu-card tsu-card-hover rounded-lg p-5">
            <div class="text-sm text-slate-500">CPL Tercapai (MK Terselenggara)</div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $totals['tercapai_aktual'] }}</div>
        </div>
        <div class="tsu-card tsu-card-hover rounded-lg p-5">
            <div class="text-sm text-slate-500">CPL Tercapai (MK Keseluruhan)</div>
            <div class="mt-2 text-3xl font-bold text-emerald-600">{{ $totals['tercapai_keseluruhan'] }}</div>
        </div>
    </div>

    {{-- Spider Graphs --}}
    @if($chartItems->isNotEmpty())
        <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
            @foreach($chartItems as $chart)
                {{-- Spider Graph 1: Nilai Aktual --}}
                <section class="tsu-card rounded-lg p-5">
                    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">Spider Graph CPL-MK Terselenggara</h3>
                            <p class="mt-1 text-xs text-slate-400">Dihitung dari MK yang sudah terselenggara (MK belum ada nilai diabaikan)</p>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $chart['title'] }} · {{ $chart['subtitle'] }}</p>
                        </div>
                        <div class="rounded-lg bg-[#e8f6f7] px-3 py-2 text-sm text-[#075866] whitespace-nowrap">
                            Rata-rata {{ number_format($chart['average'], 2) }}
                        </div>
                    </div>
                    <div class="relative h-80">
                        @if(count($chart['labels']) > 0)
                            <canvas id="{{ $chart['id'] }}"></canvas>
                        @else
                            <div class="flex h-full items-center justify-center rounded-lg border
                                        border-dashed border-[#d7e7e9] text-sm text-slate-500">
                                Belum ada data CPL untuk spider graph.
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Spider Graph 2: Progres Keseluruhan --}}
                <section class="tsu-card rounded-lg p-5">
                    <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">Spider Graph CPL-MK Keseluruhan</h3>
                            <p class="mt-1 text-xs text-slate-400">Dihitung dari semua MK kurikulum (MK belum terselenggara dihitung 0)</p>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $chart['title'] }} · {{ $chart['subtitle'] }}</p>
                        </div>
                        <div class="rounded-lg bg-violet-50 border border-violet-200 px-3 py-2 text-sm text-violet-700 whitespace-nowrap">
                            Rata-rata {{ number_format($chart['average_keseluruhan'], 2) }}
                        </div>
                    </div>
                    <div class="relative h-80">
                        @if(count($chart['labels']) > 0)
                            <canvas id="{{ $chart['id_keseluruhan'] }}"></canvas>
                        @else
                            <div class="flex h-full items-center justify-center rounded-lg border
                                        border-dashed border-[#d7e7e9] text-sm text-slate-500">
                                Belum ada data CPL untuk spider graph.
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    {{-- Tabel CPL per Prodi --}}
    <div class="mt-6 space-y-4">
        @forelse($items as $item)
            @php
                $prodi      = $item['prodi'] ?? [];
                $fakultas   = $prodi['fakultas'] ?? [];
                $summaryCpl = $item['summary_cpl'] ?? [];
                $cpls       = $summaryCpl['cpls'] ?? [];
            @endphp
            <section class="tsu-card rounded-lg">
                <div class="border-b border-[#d7e7e9] p-5">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $prodi['nama'] ?? '-' }}</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ $fakultas['nama'] ?? ($item['fakultas_group'] ?? '-') }}
                                · Kurikulum {{ $item['kurikulum'] ?? '-' }}
                                · Angkatan {{ $item['angkatan'] ?? $filters['angkatan'] }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @foreach($summaryCpl['jumlah_per_kategori'] ?? [] as $k)
                                @if($k['nama'] !== 'Belum Ada Data' && $k['count'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs font-medium {{ $semanticClass($k['warna']) }}">
                                        {{ $k['count'] }} {{ $k['nama'] }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                        <thead class="bg-[#f7fbfb] text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th rowspan="2" class="px-5 py-3 border-b border-[#d7e7e9] align-middle">Kode CPL</th>
                                <th colspan="2" class="px-5 py-3 border-b border-[#d7e7e9]">Nilai CPL</th>
                                <th colspan="2" class="px-5 py-3 border-b border-[#d7e7e9]">Status</th>
                            </tr>
                            <tr>
                                <th class="px-5 py-2 border-b border-[#d7e7e9]">MK Terselenggara</th>
                                <th class="px-5 py-2 border-b border-[#d7e7e9]">MK Keseluruhan</th>
                                <th class="px-5 py-2 border-b border-[#d7e7e9]">MK Terselenggara</th>
                                <th class="px-5 py-2 border-b border-[#d7e7e9]">MK Keseluruhan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($cpls as $cpl)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3 font-medium text-slate-900 text-center">{{ $cpl['kode'] ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-700 font-mono text-center">
                                        {{ number_format((float)($cpl['nilai_cpl'] ?? 0), 2) }}
                                    </td>
                                    <td class="px-5 py-3 text-slate-700 font-mono text-center">
                                        {{ number_format((float)($cpl['nilai_cpl_keseluruhan'] ?? 0), 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1
                                                     text-xs font-medium {{ $semanticClass($cpl['status_color'] ?? 'slate') }}">
                                            {{ $semanticIcon($cpl['status_color'] ?? 'slate') }}
                                            {{ $cpl['status'] ?? 'Belum Ada Data' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1
                                                     text-xs font-medium {{ $semanticClass($cpl['status_keseluruhan_color'] ?? 'slate') }}">
                                            {{ $semanticIcon($cpl['status_keseluruhan_color'] ?? 'slate') }}
                                            {{ $cpl['status_keseluruhan'] ?? 'Belum Ada Data' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-slate-500">
                                        Belum ada data CPL untuk angkatan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="tsu-card rounded-lg p-8 text-center text-sm text-slate-500">
                Tidak ada data CPL yang dapat ditampilkan untuk kombinasi ini.
            </div>
        @endforelse
    </div>

@else
    {{-- Fallback --}}
    <div class="tsu-card rounded-lg p-6 text-center text-sm text-slate-500">
        Pilih Program Studi dan Angkatan untuk menampilkan data dashboard.
    </div>
@endif

@endsection

@push('scripts')
<script>
    (function () {
        const initCharts = function () {
            if (!window.Chart) return;

            const charts = @json($chartItems);

            charts.forEach(function (chart) {
                // === Spider Graph 1: Nilai Aktual ===
                const canvas1 = document.getElementById(chart.id);
                if (canvas1 && chart.labels.length > 0) {
                    const existing1 = Chart.getChart(chart.id);
                    if (existing1) existing1.destroy();

                    new Chart(canvas1, {
                        type: 'radar',
                        data: {
                            labels: chart.labels,
                            datasets: [
                                {
                                    label: 'Nilai CPL (Aktual)',
                                    data: chart.values,
                                    fill: true,
                                    backgroundColor: 'rgba(14, 122, 134, 0.16)',
                                    borderColor: 'rgb(14, 122, 134)',
                                    pointBackgroundColor: 'rgb(14, 122, 134)',
                                    pointBorderColor: '#ffffff',
                                    pointHoverBackgroundColor: '#ffffff',
                                    pointHoverBorderColor: 'rgb(14, 122, 134)',
                                    borderWidth: 2,
                                },
                                {
                                    label: 'Target ' + chart.target,
                                    data: chart.labels.map(function () { return chart.target; }),
                                    fill: false,
                                    borderColor: 'rgb(248, 194, 28)',
                                    pointRadius: 0,
                                    borderDash: [6, 4],
                                    borderWidth: 1.5,
                                },
                            ],
                        },
                        options: radarOptions(),
                    });
                }

                // === Spider Graph 2: Progres Keseluruhan ===
                const canvas2 = document.getElementById(chart.id_keseluruhan);
                if (canvas2 && chart.labels.length > 0) {
                    const existing2 = Chart.getChart(chart.id_keseluruhan);
                    if (existing2) existing2.destroy();

                    new Chart(canvas2, {
                        type: 'radar',
                        data: {
                            labels: chart.labels,
                            datasets: [
                                {
                                    label: 'Progres Keseluruhan',
                                    data: chart.values_keseluruhan,
                                    fill: true,
                                    backgroundColor: 'rgba(124, 58, 237, 0.14)',
                                    borderColor: 'rgb(124, 58, 237)',
                                    pointBackgroundColor: 'rgb(124, 58, 237)',
                                    pointBorderColor: '#ffffff',
                                    pointHoverBackgroundColor: '#ffffff',
                                    pointHoverBorderColor: 'rgb(124, 58, 237)',
                                    borderWidth: 2,
                                },
                                {
                                    label: 'Target ' + chart.target,
                                    data: chart.labels.map(function () { return chart.target; }),
                                    fill: false,
                                    borderColor: 'rgb(248, 194, 28)',
                                    pointRadius: 0,
                                    borderDash: [6, 4],
                                    borderWidth: 1.5,
                                },
                            ],
                        },
                        options: radarOptions(),
                    });
                }
            });
        };

        function radarOptions() {
            return {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        min: 0,
                        max: 100,
                        ticks: { stepSize: 20, backdropColor: 'transparent' },
                        pointLabels: {
                            color: '#334155',
                            font: { size: 12, weight: '600' },
                        },
                        grid: { color: 'rgba(148, 163, 184, 0.35)' },
                        angleLines: { color: 'rgba(148, 163, 184, 0.35)' },
                    },
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 14, boxHeight: 14 },
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + context.formattedValue;
                            },
                        },
                    },
                },
            };
        }


        // Setup AJAX dynamic angkatan dropdown & form submit validation
        function setupDashboardFilters() {
            const kpSelect = document.getElementById('kurikulum_prodi_id');
            const angkatanSelect = document.getElementById('angkatan');
            const filterForm = document.getElementById('dashboard-filter-form') || (kpSelect ? kpSelect.closest('form') : null);

            if (kpSelect && angkatanSelect && !kpSelect.dataset.ajaxAttached) {
                kpSelect.dataset.ajaxAttached = 'true';
                kpSelect.addEventListener('change', function () {
                    const kpId = this.value;

                    if (!kpId) {
                        angkatanSelect.innerHTML = '<option value="">— Pilih Program Studi terlebih dahulu —</option>';
                        angkatanSelect.disabled = true;
                        return;
                    }

                    angkatanSelect.disabled = true;
                    angkatanSelect.innerHTML = '<option value="">Memuat daftar angkatan...</option>';

                    fetch('{{ route('dashboard.angkatan-json') }}?kurikulum_prodi_id=' + encodeURIComponent(kpId), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        const angkatans = data.angkatans || [];
                        if (angkatans.length === 0) {
                            angkatanSelect.innerHTML = '<option value="">— Belum ada data angkatan —</option>';
                            angkatanSelect.disabled = true;
                        } else {
                            let options = '<option value="">— Pilih Angkatan —</option>';
                            angkatans.forEach(akt => {
                                options += `<option value="${akt}">${akt}</option>`;
                            });
                            angkatanSelect.innerHTML = options;
                            angkatanSelect.disabled = false;
                        }
                    })
                    .catch(err => {
                        console.error('Error fetching angkatan:', err);
                        angkatanSelect.innerHTML = '<option value="">— Gagal memuat angkatan —</option>';
                        angkatanSelect.disabled = true;
                    });
                });
            }

            if (filterForm && !filterForm.dataset.validateAttached) {
                filterForm.dataset.validateAttached = 'true';
                filterForm.addEventListener('submit', function (e) {
                    const kp = document.getElementById('kurikulum_prodi_id');
                    const angk = document.getElementById('angkatan');

                    if (kp && !kp.disabled && !kp.value) {
                        e.preventDefault();
                        alert('Silakan pilih Program Studi terlebih dahulu.');
                        kp.focus();
                        return;
                    }

                    if (!angk || !angk.value) {
                        e.preventDefault();
                        alert('Silakan pilih Angkatan terlebih dahulu.');
                        if (angk) angk.focus();
                        return;
                    }
                });
            }
        }

        // Jalankan inisialisasi grafik & handler filter
        function initAll() {
            initCharts();
            setupDashboardFilters();
        }

        if (document.readyState !== 'loading') {
            initAll();
        } else {
            document.addEventListener('DOMContentLoaded', initAll);
        }
        document.addEventListener('turbo:load', initAll);
    })();
</script>
@endpush

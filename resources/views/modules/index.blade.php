@extends('layouts.app')

@section('content')
@php
    $title = 'Data ' . ($config['title'] ?? '');
    $get = fn ($item, $key) => data_get($item, $key, '-');

    $semanticClass = function ($color) {
        return match ($color) {
            'success' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
            'warning' => 'bg-amber-50 text-amber-600 border-amber-200',
            'danger'  => 'bg-rose-50 text-rose-600 border-rose-200',
            'info'    => 'bg-blue-50 text-blue-600 border-blue-200',
            default   => 'bg-slate-50 text-slate-500 border-slate-200',
        };
    };

    $semanticText = function ($color) {
        return match ($color) {
            'success' => 'Hijau',
            'warning' => 'Kuning',
            'danger'  => 'Merah',
            'info'    => 'Biru',
            default   => 'Abu-abu',
        };
    };
@endphp

<div x-data="{
    createOpen: false,
    editOpen: false,
    selected: {},
    cplModalOpen: false,
    cplData: [],
    cplLoading: false,
    activeMkName: '',
    openEdit(item) {
        this.selected = {
            ...item,
            fakultas_id: item.fakultas?.id ?? '',
            kurikulum_id: item.kurikulum?.id ?? '',
            prodi_id: item.prodi?.id ?? '',
            kurikulum_prodi_id: item.kurikulum_prodi?.id ?? '',
            mahasiswa_id: item.mahasiswa?.id ?? '',
            mata_kuliah_id: item.mata_kuliah?.id ?? '',
            is_active: item.is_active ? 1 : 0,
        };
        this.editOpen = true;
    },
    async openCplModal(id, name) {
        this.activeMkName = name;
        this.cplData = [];
        this.cplLoading = true;
        this.cplModalOpen = true;

        try {
            const response = await fetch(`/mata-kuliahs/${id}/cpls`);
            const result = await response.json();
            this.cplData = result.data || [];
        } catch (error) {
            console.error('Failed to fetch CPLs:', error);
            alert('Gagal mengambil data CPL.');
        } finally {
            this.cplLoading = false;
        }
    }
}">
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $config['title'] }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $config['description'] }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(in_array($module, ['cpls', 'cpmks', 'mata-kuliahs', 'mahasiswas']))
                <button type="button"
                        @click="$dispatch('open-import-modal-{{ $module }}')"
                        class="tsu-secondary-button inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Import Excel
                </button>
            @endif

            <button type="button"
                    @click="createOpen = true"
                    class="tsu-primary-button inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold">
                Tambah {{ $config['title'] }}
            </button>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="tsu-card rounded-lg p-4">
            <div class="text-sm text-slate-500">Total Data</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">
                {{ !empty($paginationMeta['total']) ? $paginationMeta['total'] : count($data) }}
            </div>
        </div>
        <div class="tsu-card rounded-lg p-4 md:col-span-2">
            <div class="text-sm text-slate-500">Modul</div>
            <div class="mt-2 text-base font-semibold text-slate-900">{{ $config['title'] }}</div>
        </div>
    </div>

    @if($module === 'mahasiswas')
        <section class="mb-4 tsu-card rounded-lg p-5">
            <form method="GET" action="{{ route('mahasiswas.index') }}" class="grid gap-4 lg:grid-cols-[220px_220px_1fr_auto] lg:items-end">
                <div>
                    <label for="kurikulum_prodi_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Kurikulum Prodi</label>
                    <select id="kurikulum_prodi_id" name="kurikulum_prodi_id" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2.5 text-sm" onchange="this.form.submit()">
                        @foreach(($filterOptions['kurikulumProdis'] ?? []) as $kp)
                            <option value="{{ $kp['id'] }}" @selected(($filters['kurikulum_prodi_id'] ?? '') == $kp['id'])>
                                {{ $kp['kode_kur_prodi'] }} — {{ $kp['prodi']['nama'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="angkatan" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Angkatan</label>
                    <select id="angkatan" name="angkatan" class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2.5 text-sm" onchange="this.form.submit()">
                        <option value="all">Semua Angkatan</option>
                        @foreach(($filterOptions['angkatans'] ?? []) as $angkatan)
                            <option value="{{ $angkatan }}" @selected(($filters['angkatan'] ?? 'all') === (string) $angkatan)>
                                {{ $angkatan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Nama</label>
                    <input id="search"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           type="search"
                           placeholder="Ketik nama mahasiswa"
                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2.5 text-sm">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2.5 text-sm font-semibold">Filter</button>
                    <a href="{{ route('mahasiswas.index') }}" class="tsu-secondary-button rounded-lg px-4 py-2.5 text-sm font-semibold">Reset</a>
                </div>
            </form>
        </section>
    @endif

    @if(in_array($module, ['cpls', 'mata-kuliahs']))
        <section class="mb-4 tsu-card rounded-lg p-5">
            <form method="GET" action="{{ route($config['route'] . '.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[220px]">
                    <label for="kurikulum_prodi_id" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Filter Kurikulum Prodi</label>
                    <select id="kurikulum_prodi_id" name="kurikulum_prodi_id"
                            class="w-full rounded-lg border border-[#d7e7e9] bg-white px-3 py-2.5 text-sm outline-none focus:border-[#0e7a86] focus:ring-2 focus:ring-[#e8f6f7]"
                            onchange="this.form.submit()">
                        @foreach(($filterOptions['kurikulumProdis'] ?? []) as $kp)
                            <option value="{{ $kp['id'] }}" @selected(($filters['kurikulum_prodi_id'] ?? '') == $kp['id'])>
                                {{ $kp['kode_kur_prodi'] }} — {{ $kp['prodi']['nama'] ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </section>
    @endif


    <div class="overflow-hidden tsu-card rounded-lg">
        <div class="overflow-x-auto">
            <table x-data="tableSort()" class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                <thead class="bg-[#f7fbfb] text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort(0)">No <span x-show="sortCol === 0" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        @foreach($config['columns'] as $index => $column)
                            <th class="px-4 py-3 cursor-pointer select-none hover:bg-slate-100" @click="sort({{ $index + 1 }})">
                                {{ $column['label'] }} <span x-show="sortCol === {{ $index + 1 }}" x-text="sortAsc ? '↑' : '↓'"></span>
                            </th>
                        @endforeach
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody x-ref="tbody" class="divide-y divide-slate-100 bg-white">
                    @forelse($data as $item)
                        <tr class="hover:bg-[#f7fbfb]">
                            <td class="px-4 py-3 text-slate-500">{{ $loop->iteration }}</td>
                            @foreach($config['columns'] as $column)
                                @php $value = $get($item, $column['key']); @endphp
                                <td class="px-4 py-3 text-slate-700">
                                    @if(($column['type'] ?? null) === 'boolean')
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-medium {{ $value ? 'border-green-200 bg-green-50 text-green-700' : 'border-[#d7e7e9] bg-[#f7fbfb] text-slate-600' }}">
                                            {{ $value ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    @elseif($column['key'] === 'warna')
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-medium {{ $semanticClass($value) }}">
                                            {{ $semanticText($value) }}
                                        </span>
                                    @else
                                        {{ is_array($value) ? json_encode($value) : $value }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    @if($module === 'mata-kuliahs')
                                        <button type="button"
                                                @click="openCplModal('{{ $item['id'] }}', '{{ addslashes($item['nama']) }}')"
                                                class="tsu-primary-button rounded-lg px-3 py-1.5 text-xs font-semibold bg-[#e8f6f7] text-[#0e7a86] hover:bg-[#d7e7e9] border border-[#d7e7e9]"
                                                title="Lihat CPL yang dipetakan">
                                            CPL
                                        </button>
                                        <a href="{{ route('mk-cpmk.index', $item['id']) }}"
                                           class="tsu-primary-button rounded-lg px-3 py-1.5 text-xs font-semibold">
                                            CPMK
                                        </a>
                                    @endif
                                    
                                    @if($module === 'mahasiswas')
                                        <a href="{{ route('mahasiswas.detail', $item['id']) }}"
                                           class="tsu-primary-button rounded-lg px-3 py-1.5 text-xs font-semibold bg-[#e8f6f7] text-[#0e7a86] hover:bg-[#d7e7e9] border border-[#d7e7e9]">
                                            Detail
                                        </a>
                                    @endif

                                    @if($module === 'cpls')
                                        <a href="{{ route('cpls.bobot.index', $item['id']) }}"
                                           class="inline-flex items-center gap-1 rounded-lg border border-[#d7e7e9] bg-[#e8f6f7] px-3 py-1.5 text-xs font-semibold text-[#0e7a86] hover:bg-[#d7e7e9] transition"
                                           title="Atur bobot mata kuliah untuk CPL ini">
                                            Bobot
                                        </a>
                                    @endif

                                    <button type="button"
                                            @click='openEdit(@json($item))'
                                            class="tsu-secondary-button rounded-lg px-3 py-1.5 text-xs font-semibold">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route($config['route'] . '.destroy', $item['id']) }}">
                                        @csrf
                                        @method('DELETE')
                                        @if(!empty($filters['kurikulum_prodi_id']))
                                            <input type="hidden" name="kurikulum_prodi_id" value="{{ $filters['kurikulum_prodi_id'] }}">
                                        @endif
                                        @if(!empty($filters['angkatan']) && $filters['angkatan'] !== 'all')
                                            <input type="hidden" name="angkatan" value="{{ $filters['angkatan'] }}">
                                        @endif
                                        <button type="submit"
                                                onclick="return confirm('Hapus data ini?')"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100 border border-red-200">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($config['columns']) + 2 }}" class="px-4 py-8 text-center text-slate-500">
                                Belum ada data {{ strtolower($config['title']) }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination (hanya untuk modul mahasiswas) --}}
    @if($module === 'mahasiswas' && !empty($paginationMeta) && ($paginationMeta['last_page'] ?? 1) > 1)
        @php
            $currentPage = $paginationMeta['current_page'] ?? 1;
            $lastPage    = $paginationMeta['last_page'] ?? 1;
            $from        = $paginationMeta['from'] ?? null;
            $to          = $paginationMeta['to'] ?? null;
            $total       = $paginationMeta['total'] ?? 0;
            $queryWithoutPage = collect(request()->query())->except('page')->toArray();
        @endphp
        <div class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row">
            <p class="text-sm text-slate-500">
                Menampilkan
                <span class="font-semibold text-slate-700">{{ $from }}&ndash;{{ $to }}</span>
                dari <span class="font-semibold text-slate-700">{{ $total }}</span> data
            </p>

            <nav class="flex items-center gap-1" aria-label="Pagination">
                {{-- Tombol Prev --}}
                @if($currentPage > 1)
                    <a href="{{ request()->fullUrlWithQuery(array_merge($queryWithoutPage, ['page' => $currentPage - 1])) }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d7e7e9] bg-white text-slate-600 hover:bg-[#e8f6f7] hover:text-[#0e7a86] transition">
                        &lsaquo;
                    </a>
                @else
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d7e7e9] bg-slate-50 text-slate-300 cursor-not-allowed">&lsaquo;</span>
                @endif

                {{-- Nomor Halaman --}}
                @for($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++)
                    @if($p === $currentPage)
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0e7a86] text-sm font-semibold text-white shadow-sm">{{ $p }}</span>
                    @else
                        <a href="{{ request()->fullUrlWithQuery(array_merge($queryWithoutPage, ['page' => $p])) }}"
                           class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d7e7e9] bg-white text-sm text-slate-600 hover:bg-[#e8f6f7] hover:text-[#0e7a86] transition">
                            {{ $p }}
                        </a>
                    @endif
                @endfor

                {{-- Tombol Next --}}
                @if($currentPage < $lastPage)
                    <a href="{{ request()->fullUrlWithQuery(array_merge($queryWithoutPage, ['page' => $currentPage + 1])) }}"
                       class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d7e7e9] bg-white text-slate-600 hover:bg-[#e8f6f7] hover:text-[#0e7a86] transition">
                        &rsaquo;
                    </a>
                @else
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-[#d7e7e9] bg-slate-50 text-slate-300 cursor-not-allowed">&rsaquo;</span>
                @endif
            </nav>
        </div>
    @endif

    <div x-cloak x-show="createOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="createOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4">
                <h3 class="font-semibold text-slate-900">Tambah {{ $config['title'] }}</h3>
            </div>
            <form method="POST" action="{{ route($config['route'] . '.store') }}" class="space-y-4 p-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                @if(!empty($filters['kurikulum_prodi_id']))
                    <input type="hidden" name="filter_kurikulum_prodi_id" value="{{ $filters['kurikulum_prodi_id'] }}">
                @endif
                @if(!empty($filters['angkatan']) && $filters['angkatan'] !== 'all')
                    <input type="hidden" name="angkatan" value="{{ $filters['angkatan'] }}">
                @endif
                @foreach($config['fields'] as $field)
                    @include('modules._field', ['field' => $field, 'mode' => 'create', 'options' => $options])
                @endforeach
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="createOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium" :disabled="isSubmitting">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold" :disabled="isSubmitting" x-text="isSubmitting ? 'Menyimpan...' : 'Simpan'">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div x-cloak x-show="editOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="editOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4">
                <h3 class="font-semibold text-slate-900">Edit {{ $config['title'] }}</h3>
            </div>
            <form method="POST" :action="`/{{ $config['route'] }}/${selected.id}`" class="space-y-4 p-6" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
                @csrf
                @method('PUT')
                @if(!empty($filters['kurikulum_prodi_id']))
                    <input type="hidden" name="filter_kurikulum_prodi_id" value="{{ $filters['kurikulum_prodi_id'] }}">
                @endif
                <input type="hidden" name="kurikulum_prodi_id" :value="selected.kurikulum_prodi_id || '{{ $filters['kurikulum_prodi_id'] ?? '' }}'">
                @if(!empty($filters['angkatan']) && $filters['angkatan'] !== 'all')
                    <input type="hidden" name="angkatan" value="{{ $filters['angkatan'] }}">
                @endif
                @foreach($config['fields'] as $field)
                    @include('modules._field', ['field' => $field, 'mode' => 'edit', 'options' => $options])
                @endforeach
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="editOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium" :disabled="isSubmitting">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold" :disabled="isSubmitting" x-text="isSubmitting ? 'Menyimpan...' : 'Perbarui'">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal CPL per Mata Kuliah --}}
    @if($module === 'mata-kuliahs')
        <div x-cloak x-show="cplModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div @click.away="cplModalOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-lg bg-white shadow-xl">
                <div class="border-b border-[#d7e7e9] px-6 py-4 flex justify-between items-center">
                    <div>
                        <h3 class="font-semibold text-slate-900">Pemetaan CPL</h3>
                        <p class="text-sm text-slate-500" x-text="activeMkName"></p>
                    </div>
                    <button @click="cplModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <div class="p-6">
                    <div x-show="cplLoading" class="text-center text-sm text-slate-500 py-4">
                        Memuat data CPL...
                    </div>
                    
                    <div x-show="!cplLoading && cplData.length === 0" class="text-center text-sm text-slate-500 py-4" style="display: none;">
                        Mata kuliah ini belum dipetakan ke CPL manapun.
                    </div>

                    <div x-show="!cplLoading && cplData.length > 0" style="display: none;">
                        <ul class="space-y-3">
                            <template x-for="cpl in cplData" :key="cpl.id">
                                <li class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    <div class="flex items-start gap-3">
                                        <span class="rounded bg-[#0e7a86] px-2 py-1 text-xs font-bold text-white" x-text="cpl.kode"></span>
                                        <div class="text-sm text-slate-700" x-text="cpl.deskripsi"></div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-[#d7e7e9] px-6 py-4 flex justify-end">
                    <button type="button" @click="cplModalOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium">Tutup</button>
                </div>
            </div>
        </div>
    @endif

    @if(in_array($module, ['cpls', 'cpmks', 'mata-kuliahs', 'mahasiswas']))
        @php
            $importType = rtrim($module, 's');
            if ($module === 'mata-kuliahs') $importType = 'mata-kuliah';
            if ($module === 'cpmks') $importType = 'cpmk';

            $activeKpLabel = null;
            $activeProdiLabel = null;
            if (in_array($module, ['cpls', 'cpmks', 'mata-kuliahs']) && !empty($filters['kurikulum_prodi_id'])) {
                foreach (($filterOptions['kurikulumProdis'] ?? []) as $kp) {
                    if ($kp['id'] == $filters['kurikulum_prodi_id']) {
                        $activeKpLabel = ($kp['kode_kur_prodi'] ?? '') . ' — ' . ($kp['prodi']['nama'] ?? '');
                        break;
                    }
                }
            }
            if ($module === 'mahasiswas' && !empty($filters['prodi_id'])) {
                foreach (($filterOptions['prodis'] ?? []) as $pr) {
                    if ($pr['id'] == $filters['prodi_id']) {
                        $activeProdiLabel = $pr['nama'] ?? '';
                        break;
                    }
                }
            }
        @endphp

        <x-modal-import
            id="{{ $module }}"
            title="Import {{ $config['title'] }}"
            endpoint="{{ route('import.' . $importType) }}"
            templateUrl="{{ route('import.template-' . $importType) }}"
        >
            @if(in_array($module, ['cpls', 'cpmks', 'mata-kuliahs', 'mahasiswas']))
                @if(!empty($filters['kurikulum_prodi_id']))
                    <input type="hidden" name="kurikulum_prodi_id" value="{{ $filters['kurikulum_prodi_id'] }}">
                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 mb-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-blue-500 mb-0.5">Data akan diimport ke Kurikulum Prodi:</p>
                        <p class="text-sm font-bold text-blue-900">{{ $activeKpLabel }}</p>
                    </div>
                @else
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 mb-3">
                        <p class="text-sm font-semibold text-amber-800">⚠ Pilih Kurikulum Prodi terlebih dahulu</p>
                        <p class="text-xs text-amber-700 mt-1">Tutup modal ini, pilih Kurikulum Prodi pada filter di atas tabel, lalu klik tombol Import Excel kembali.</p>
                    </div>
                @endif
            @endif
        </x-modal-import>
    @endif
</div>
@endsection

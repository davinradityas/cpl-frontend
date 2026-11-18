@extends('layouts.app')

@section('content')
<div x-data="jenisMatkul()" x-init="init()">

    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Jenis Mata Kuliah</h2>
            <p class="mt-1 text-sm text-slate-500">Template bobot penilaian per kategori Mata Kuliah (total per Jenis = 100%)</p>
        </div>
        <button type="button" @click="openAdd()"
                class="tsu-primary-button rounded-lg px-4 py-2.5 text-sm font-semibold">
            + Tambah Jenis Matkul
        </button>
    </div>

    {{-- Tabel --}}
    <div class="tsu-card overflow-hidden rounded-xl">
        <div class="overflow-x-auto">
            <table x-data="tableSort()" class="min-w-full divide-y divide-[#d7e7e9] text-sm">
                <thead class="bg-[#f7fbfb] text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left cursor-pointer select-none hover:bg-slate-100" @click="sort(0)">Nama Jenis Matkul <span x-show="sortCol === 0" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        @foreach($jenisPenilaians as $index => $jp)
                            <th class="px-3 py-3 text-center border-l border-[#d7e7e9] cursor-pointer select-none hover:bg-slate-100" @click="sort({{ $index + 1 }})">
                                {{ $jp['nama'] }} <span x-show="sortCol === {{ $index + 1 }}" x-text="sortAsc ? '↑' : '↓'"></span>
                            </th>
                        @endforeach
                        <th class="px-3 py-3 text-center border-l border-[#d7e7e9] cursor-pointer select-none hover:bg-slate-100" @click="sort({{ count($jenisPenilaians) + 1 }})">Total <span x-show="sortCol === {{ count($jenisPenilaians) + 1 }}" x-text="sortAsc ? '↑' : '↓'"></span></th>
                        <th class="px-4 py-3 text-right border-l border-[#d7e7e9]">Aksi</th>
                    </tr>
                </thead>
                <tbody x-ref="tbody" class="divide-y divide-slate-100 bg-white">
                    @forelse($jenisMatkulis as $jm)
                        <tr class="hover:bg-[#f7fbfb]">
                            <td class="px-5 py-3">
                                <div class="font-semibold text-slate-900">{{ $jm['nama'] }}</div>
                                @if($jm['keterangan'])
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $jm['keterangan'] }}</div>
                                @endif
                            </td>
                            @foreach($jenisPenilaians as $jp)
                                @php
                                    $b = collect($jm['bobots'])->firstWhere('jenis_penilaian_id', $jp['id']);
                                    $bv = $b ? $b['bobot'] : 0;
                                @endphp
                                <td class="px-3 py-3 text-center border-l border-[#d7e7e9] {{ $bv > 0 ? 'font-semibold text-slate-800' : 'text-slate-300' }}">
                                    {{ $bv > 0 ? $bv : '-' }}
                                </td>
                            @endforeach
                            <td class="px-3 py-3 text-center border-l border-[#d7e7e9]">
                                <span class="font-bold {{ $jm['bobot_valid'] ? 'text-green-600' : 'text-red-500' }}">
                                    {{ $jm['total_bobot'] }}
                                    @if(!$jm['bobot_valid']) <span class="text-xs font-normal">(≠100)</span> @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right border-l border-[#d7e7e9]">
                                <div class="inline-flex gap-1 items-center">
                                    @if($jm['has_related_matkul'] ?? false)
                                        <div class="relative group inline-flex gap-1">
                                            <button type="button" disabled
                                                    class="rounded bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                                Edit
                                            </button>
                                            <button type="button" disabled
                                                    class="rounded bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-400 cursor-not-allowed">
                                                Hapus
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block w-52 rounded-lg bg-slate-800 text-white text-xs px-3 py-2 shadow-lg z-50 leading-relaxed">
                                                Tidak dapat diedit atau dihapus karena sudah berelasi dengan Mata Kuliah.
                                                <div class="absolute top-full right-3 border-4 border-transparent border-t-slate-800"></div>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button"
                                                @click="openEdit({{ json_encode($jm) }})"
                                                class="rounded bg-indigo-50 border border-indigo-200 px-2.5 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('jenis-matkul.destroy', $jm['id']) }}" class="inline"
                                              onsubmit="return confirm('Hapus Jenis Matkul \'{{ $jm['nama'] }}\'? Mata kuliah yang menggunakannya akan kehilangan referensi Jenis Matkul.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="rounded bg-red-50 border border-red-200 px-2.5 py-1 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($jenisPenilaians) + 3 }}" class="px-5 py-10 text-center text-slate-400">
                                Belum ada Jenis Matkul. Klik <strong>+ Tambah Jenis Matkul</strong> untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== MODAL: Tambah / Edit ===== --}}
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="modalOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4 bg-[#f7fbfb]">
                <h3 class="font-bold text-slate-900" x-text="editId ? 'Edit Jenis Matkul' : 'Tambah Jenis Matkul'"></h3>
                <p class="text-xs text-slate-500 mt-0.5">Total bobot semua jenis penilaian harus = <strong>100</strong></p>
            </div>

            <form method="POST" :action="editId ? '{{ url('/jenis-matkul') }}/' + editId : '{{ route('jenis-matkul.store') }}'" class="p-6">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>

                <div class="mb-5 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Nama Jenis Matkul <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" x-model="form.nama" required
                               class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-400"
                               placeholder="Contoh: Kuliah Praktikum">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">Keterangan</label>
                        <input type="text" name="keterangan" x-model="form.keterangan"
                               class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm focus:border-teal-500 focus:ring-1 focus:ring-teal-400"
                               placeholder="Opsional">
                    </div>
                </div>

                {{-- Tabel Input Bobot --}}
                <div class="rounded-lg border border-[#d7e7e9] overflow-hidden mb-5">
                    <div class="bg-slate-50 px-4 py-3 border-b border-[#d7e7e9]">
                        <p class="text-sm font-semibold text-slate-700">Bobot per Jenis Penilaian (%)</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach($jenisPenilaians as $idx => $jp)
                            <div class="flex items-center gap-4 px-4 py-3">
                                <input type="hidden" name="bobots[{{ $idx }}][jenis_penilaian_id]" value="{{ $jp['id'] }}">
                                <label class="flex-1 text-sm font-medium text-slate-700">{{ $jp['nama'] }}</label>
                                <div class="relative w-28">
                                    <input type="number" step="any" min="0" max="100"
                                           name="bobots[{{ $idx }}][bobot]"
                                           x-model.number="form.bobots[{{ $jp['id'] }}]"
                                           class="w-full rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm text-right pr-7 focus:border-teal-500 focus:ring-1 focus:ring-teal-400"
                                           placeholder="0">
                                    <span class="absolute right-2.5 top-2.5 text-slate-400 text-xs">%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Total indicator --}}
                <div class="mb-5 rounded-lg p-4 flex justify-between items-center"
                     :class="totalValid ? 'bg-green-50 border border-green-200' : 'bg-amber-50 border border-amber-200'">
                    <span class="text-sm font-bold text-slate-700">Total Bobot:</span>
                    <div class="text-right">
                        <span class="text-xl font-bold" :class="totalValid ? 'text-green-600' : 'text-red-600'" x-text="totalBobot.toFixed(1)"></span>
                        <span class="text-sm text-slate-500 ml-1">/ 100</span>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="tsu-secondary-button rounded-lg px-4 py-2 text-sm">Batal</button>
                    <button type="submit" class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold"
                            :disabled="!totalValid || !form.nama"
                            :class="{'opacity-50 cursor-not-allowed': !totalValid || !form.nama}">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@push('scripts')
<script>
function jenisMatkul() {
    return {
        modalOpen: false,
        editId: null,
        form: {
            nama: '',
            keterangan: '',
            bobots: {}
        },
        jenisPenilaians: @json($jenisPenilaians),

        init() {
            this.resetForm();
        },

        resetForm() {
            this.form.nama = '';
            this.form.keterangan = '';
            this.form.bobots = {};
            this.jenisPenilaians.forEach(jp => {
                this.form.bobots[jp.id] = 0;
            });
        },

        openAdd() {
            this.editId = null;
            this.resetForm();
            this.modalOpen = true;
        },

        openEdit(jm) {
            this.editId = jm.id;
            this.form.nama = jm.nama;
            this.form.keterangan = jm.keterangan || '';
            this.form.bobots = {};
            this.jenisPenilaians.forEach(jp => {
                const b = (jm.bobots || []).find(x => x.jenis_penilaian_id === jp.id);
                this.form.bobots[jp.id] = b ? b.bobot : 0;
            });
            this.modalOpen = true;
        },

        get totalBobot() {
            return Object.values(this.form.bobots).reduce((s, v) => s + Number(v || 0), 0);
        },

        get totalValid() {
            return Math.abs(this.totalBobot - 100) < 0.01;
        }
    };
}
</script>
@endpush
@endsection

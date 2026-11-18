@extends('layouts.app')

@section('content')
@php
    $title = 'Fakultas';
@endphp

<div x-data="{
    createOpen: false,
    editOpen: false,
    selected: {
        id: null,
        kode: '',
        nama: '',
    },
    openEdit(item) {
        this.selected = {
            id: item.id,
            kode: item.kode ?? '',
            nama: item.nama ?? '',
        };
        this.editOpen = true;
    }
}">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Data Fakultas</h2>
            <p class="mt-1 text-sm text-slate-500">
                Kelola master data fakultas. Menu ini hanya tersedia untuk superadmin.
            </p>
        </div>

        <button type="button"
                @click="createOpen = true"
                class="tsu-primary-button inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-semibold">
            Tambah Fakultas
        </button>
    </div>

    <div class="tsu-card overflow-hidden rounded-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#d7e7e9]">
                <thead class="bg-[#f7fbfb]">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            No
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Kode
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Nama Fakultas
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#d7e7e9] bg-white">
                    @forelse($fakultas as $item)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">
                                {{ $item['kode'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                {{ $item['nama'] ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button"
                                            @click='openEdit(@json($item))'
                                            class="tsu-secondary-button rounded-lg px-3 py-1.5 text-xs font-semibold">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('fakultas.destroy', $item['id']) }}"
                                          data-confirm-delete>
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                                Belum ada data fakultas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create Modal --}}
    <div x-cloak
         x-show="createOpen"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="createOpen = false"
             class="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Tambah Fakultas</h3>
            </div>

            <form method="POST" action="{{ route('fakultas.store') }}" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Kode Fakultas
                    </label>
                    <input type="text"
                           name="kode"
                           value="{{ old('kode') }}"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                           required>

                    @error('kode')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if(session('errors_api.kode'))
                        <p class="mt-1 text-xs text-red-600">
                            {{ session('errors_api.kode.0') }}
                        </p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Nama Fakultas
                    </label>
                    <input type="text"
                           name="nama"
                           value="{{ old('nama') }}"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                           required>

                    @error('nama')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    @if(session('errors_api.nama'))
                        <p class="mt-1 text-xs text-red-600">
                            {{ session('errors_api.nama.0') }}
                        </p>
                    @endif
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                            @click="createOpen = false"
                            class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium">
                        Batal
                    </button>

                    <button type="submit"
                            class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div x-cloak
         x-show="editOpen"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
        <div @click.away="editOpen = false"
             class="w-full max-w-lg rounded-lg bg-white shadow-xl">
            <div class="border-b border-[#d7e7e9] px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Edit Fakultas</h3>
            </div>

            <form method="POST"
                  :action="`/fakultas/${selected.id}`"
                  class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Kode Fakultas
                    </label>
                    <input type="text"
                           name="kode"
                           x-model="selected.kode"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Nama Fakultas
                    </label>
                    <input type="text"
                           name="nama"
                           x-model="selected.nama"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                           required>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button"
                            @click="editOpen = false"
                            class="tsu-secondary-button rounded-lg px-4 py-2 text-sm font-medium">
                        Batal
                    </button>

                    <button type="submit"
                            class="tsu-primary-button rounded-lg px-4 py-2 text-sm font-semibold">
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection



<div
    x-data="{ 
        isOpen: false,
        isUploading: false,
        file: null,
        errors: [],
        openModal() { this.isOpen = true; this.reset(); },
        closeModal() { this.isOpen = false; },
        reset() {
            this.file = null;
            this.errors = [];
            if(this.$refs.fileInput) this.$refs.fileInput.value = '';
        },
        handleFileChange(e) {
            this.file = e.target.files[0];
            this.errors = [];
        },
        async submit() {
            if (!this.file) {
                this.errors = ['Pilih file Excel terlebih dahulu.'];
                return;
            }
            this.isUploading = true;
            this.errors = [];
            
            let form = this.$refs.form;
            let formData = new FormData(form);
            // File input is already inside the form, but just to be sure (since it has no name attr):
            formData.set('file', this.file);

            try {
                // Ambil CSRF token dari cookie untuk request ke frontend Laravel
                const csrfToken = document.cookie.split('; ')
                    .find(row => row.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1];
                
                let response = await fetch('{{ $endpoint }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-XSRF-TOKEN': csrfToken ? decodeURIComponent(csrfToken) : ''
                    },
                    body: formData
                });
                
                let result = await response.json();
                
                if (response.ok && result.success) {
                    this.closeModal();
                    // Gunakan Turbo untuk reload halaman atau show flash message
                    Turbo.visit(window.location.href);
                } else {
                    if (result.errors && Array.isArray(result.errors)) {
                        this.errors = result.errors;
                    } else if (result.errors) {
                        this.errors = Object.values(result.errors).flat();
                    } else {
                        this.errors = [result.message || 'Terjadi kesalahan saat import data.'];
                    }
                }
            } catch (err) {
                this.errors = ['Koneksi ke server gagal.'];
            } finally {
                this.isUploading = false;
            }
        }
    }"
    @open-import-modal-{{ $id }}.window="openModal()"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/50 backdrop-blur-sm"
>
    <div 
        x-show="isOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl"
        @click.away="!isUploading && closeModal()"
    >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ $title }}</h3>
            <button @click="!isUploading && closeModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5">
            <div class="mb-5 flex items-center justify-between rounded-lg border border-teal-100 bg-teal-50 px-4 py-3">
                <div class="text-sm text-teal-800">
                    Gunakan template Excel yang disediakan untuk menghindari error validasi.
                </div>
                <a href="{{ $templateUrl }}" target="_blank" class="shrink-0 rounded bg-white px-3 py-1.5 text-xs font-semibold text-teal-600 shadow-sm ring-1 ring-inset ring-teal-200 hover:bg-teal-50">
                    Download Template
                </a>
            </div>

            <form x-ref="form" @submit.prevent="submit">
                {{ $slot }}

                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Pilih File Excel (.xlsx, .csv)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file-{{ $id }}" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                </svg>
                                <p class="mb-2 text-sm text-slate-500" x-show="!file"><span class="font-semibold">Klik untuk upload</span> atau drag and drop</p>
                                <p class="mb-2 text-sm text-teal-600 font-semibold" x-show="file" x-text="file ? file.name : ''"></p>
                                <p class="text-xs text-slate-500">XLSX, XLS, atau CSV (Max: 5MB)</p>
                            </div>
                            <input id="dropzone-file-{{ $id }}" type="file" class="hidden" accept=".xlsx,.xls,.csv" x-ref="fileInput" @change="handleFileChange" />
                        </label>
                    </div>
                </div>

                <!-- Error Messages -->
                <div x-show="errors.length > 0" class="mt-4 rounded-md bg-red-50 p-4" x-cloak>
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada file import:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul role="list" class="list-disc space-y-1 pl-5">
                                    <template x-for="err in errors">
                                        <li x-text="err"></li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="closeModal" :disabled="isUploading" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none disabled:opacity-50">
                        Batal
                    </button>
                    <button type="submit" :disabled="isUploading" class="inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 disabled:opacity-50">
                        <svg x-show="isUploading" class="-ml-1 mr-2 h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="isUploading ? 'Mengunggah...' : 'Import Data'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

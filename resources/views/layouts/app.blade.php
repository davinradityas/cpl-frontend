<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - Sistem Capaian Pembelajaran Lulusan' : 'Sistem Capaian Pembelajaran Lulusan' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/tsu logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="overflow-hidden text-slate-800">
    <div class="flex h-screen" x-data="{ sidebarOpen: false }">
        @include('layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            @include('layouts.topbar')

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @if(session('success'))
                    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    <script>
        function tableSort() {
            return {
                sortCol: null,
                sortAsc: true,
                sort(colIndex) {
                    if (this.sortCol === colIndex) {
                        this.sortAsc = !this.sortAsc;
                    } else {
                        this.sortCol = colIndex;
                        this.sortAsc = true;
                    }
                    
                    let tbody = this.$refs.tbody;
                    if (!tbody) return;
                    
                    let rows = Array.from(tbody.querySelectorAll('tr'));
                    if (rows.length === 0 || rows[0].querySelector('td[colspan]')) return;

                    rows.sort((a, b) => {
                        let cellA = a.children[colIndex].innerText.trim();
                        let cellB = b.children[colIndex].innerText.trim();
                        
                        let numA = parseFloat(cellA);
                        let numB = parseFloat(cellB);
                        
                        if (!isNaN(numA) && !isNaN(numB) && cellA !== '' && cellB !== '') {
                            return this.sortAsc ? numA - numB : numB - numA;
                        }
                        return this.sortAsc 
                            ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' }) 
                            : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
                    });
                    
                    rows.forEach(row => tbody.appendChild(row));
                }
            };
        }
    </script>
</body>
</html>

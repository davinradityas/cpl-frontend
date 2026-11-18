@foreach($menuGroups as $group)
    @php
        $visibleItems = collect($group['items'])
            ->filter(function ($item) use ($role, $hasPermission, $isSuperadmin) {
                // Cek route ada
                if (!Route::has($item['route'])) return false;
                // Superadmin melihat semuanya
                if ($isSuperadmin) return true;

                $perm         = $item['permission'] ?? null;
                $allowedRoles = $item['roles']       ?? [];

                if ($perm === null) {
                    // Tidak ada permission khusus → gunakan role filter (biasanya superadmin-only)
                    return in_array($role, $allowedRoles, true);
                }

                // Ada permission → cek berdasarkan permission (support dynamic roles)
                return $hasPermission($perm);
            });
    @endphp

    @if($visibleItems->isNotEmpty())
        <div>
            <div class="mb-2 px-3">
                <span class="tsu-section-label">{{ $group['title'] }}</span>
            </div>
            <div class="space-y-1">
                @foreach($visibleItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="tsu-nav-item flex items-center rounded-lg px-4 py-2.5 pl-7 text-sm font-medium {{ request()->routeIs($item['active']) ? 'tsu-nav-item-active' : '' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endforeach





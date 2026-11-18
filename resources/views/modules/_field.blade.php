@php
    $name = $field['name'];
    $type = $field['type'] ?? 'text';
    $label = $field['label'];
    $oldValue = old($name);
    $xModel = "selected.{$name}";
    $isRequired = $field['required'] ?? true;
@endphp

@if(!(($field['store_only'] ?? false) && $mode === 'edit'))
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>

        @if($type === 'select')
            <select name="{{ $name }}"
                    @if($mode === 'edit') x-model="{{ $xModel }}" @endif
                    class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                    @if($isRequired) required @endif>
                <option value="">Pilih {{ $label }}</option>
                @php
                    $selectOptions = $field['options'] ?? $options[$name] ?? [];
                @endphp
                @foreach($selectOptions as $key => $option)
                    @php
                        $optValue = is_array($option) ? $option['id'] : $key;
                        $optLabel = is_array($option) ? ($option[$field['option_label'] ?? 'nama'] ?? $option['kode'] ?? $option['id']) : $option;
                    @endphp
                    <option value="{{ $optValue }}"
                            @if($mode === 'create' && ((string) $oldValue === (string) $optValue || (empty($oldValue) && isset($filters[$name]) && (string)$filters[$name] === (string)$optValue))) selected @endif>
                        {{ $optLabel }}
                    </option>
                @endforeach
            </select>
        @elseif($type === 'textarea')
            <textarea name="{{ $name }}"
                      rows="3"
                      @if($mode === 'edit') x-model="{{ $xModel }}" @endif
                      class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                      @if($isRequired) required @endif>{{ $mode === 'create' ? $oldValue : '' }}</textarea>
        @elseif($type === 'checkbox')
            <input type="hidden" name="{{ $name }}" value="0">
            <label class="inline-flex items-center gap-2 rounded-lg border border-[#d7e7e9] px-3 py-2 text-sm text-slate-700">
                <input type="checkbox"
                       name="{{ $name }}"
                       value="1"
                       @if($mode === 'edit') x-model="{{ $xModel }}" @elseif($oldValue) checked @endif
                       class="rounded border-slate-300 text-[#0e7a86] focus:ring-[#0e7a86]">
                {{ $label }}
            </label>
        @else
            <input type="{{ $type }}"
                   name="{{ $name }}"
                   value="{{ $mode === 'create' ? $oldValue : '' }}"
                   @if($mode === 'edit') x-model="{{ $xModel }}" @endif
                   class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-[#0e7a86] focus:ring-[#0e7a86]"
                   @if($isRequired) required @endif>
        @endif

        @error($name)
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror

        @if(session("errors_api.{$name}"))
            <p class="mt-1 text-xs text-red-600">{{ session("errors_api.{$name}.0") }}</p>
        @endif
    </div>
@endif




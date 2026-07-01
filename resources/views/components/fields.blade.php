@foreach($fields as $field)
    @php($key = $field['key'])
    @php($name = \Zarbin\Seo\Support\SeoFormFields::inputName($key))
    @php($value = old('seo.'.$key, $values[$key] ?? ''))
    @php($label = isset($field['label_key']) ? \Zarbin\Seo\Support\UiTranslator::get($field['label_key']) : ($field['label'] ?? $key))
    @php($hint = isset($field['hint_key']) ? \Zarbin\Seo\Support\UiTranslator::get($field['hint_key']) : ($field['help'] ?? null))
    <div class="zarbin-seo-field">
        <label for="zarbin-seo-{{ $key }}">{{ $label }}</label>
        @if(($field['type'] ?? 'text') === 'textarea')
            <textarea id="zarbin-seo-{{ $key }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 3 }}">{{ $value }}</textarea>
        @elseif(($field['type'] ?? 'text') === 'select')
            @php($options = is_array($field['options'] ?? null) ? $field['options'] : [])
            <select id="zarbin-seo-{{ $key }}" name="{{ $name }}">
                @if($value !== '' && ! array_key_exists((string) $value, $options))
                    <option value="{{ $value }}" selected>Custom: {{ $value }}</option>
                @endif
                @foreach($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" @selected((string) $optionValue === (string) $value)>{{ $optionLabel }}</option>
                @endforeach
            </select>
        @else
            <input id="zarbin-seo-{{ $key }}" name="{{ $name }}" type="{{ $field['type'] ?? 'text' }}" value="{{ $value }}">
        @endif
        @if(! empty($hint))
            <div class="zarbin-seo-help">{{ $hint }}</div>
        @endif
        @if(isset($errors) && $errors->has('seo.'.$key))
            <div class="zarbin-seo-alert zarbin-seo-alert-error">{{ $errors->first('seo.'.$key) }}</div>
        @endif
    </div>
@endforeach

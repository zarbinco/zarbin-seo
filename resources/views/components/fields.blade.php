@foreach($fields as $field)
    @php($key = $field['key'])
    @php($name = \Zarbin\Seo\Support\SeoFormFields::inputName($key))
    @php($value = old('seo.'.$key, $values[$key] ?? ''))
    <div class="zarbin-seo-field">
        <label for="zarbin-seo-{{ $key }}">{{ $field['label'] }}</label>
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
        @if(! empty($field['help']))
            <div class="zarbin-seo-help">{{ $field['help'] }}</div>
        @endif
        @if(isset($errors) && $errors->has('seo.'.$key))
            <div class="zarbin-seo-alert zarbin-seo-alert-error">{{ $errors->first('seo.'.$key) }}</div>
        @endif
    </div>
@endforeach

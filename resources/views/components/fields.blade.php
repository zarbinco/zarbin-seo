@foreach($fields as $field)
    @php($key = $field['key'])
    @php($name = \Zarbin\Seo\Support\SeoFormFields::inputName($key))
    @php($value = old('seo.'.$key, $values[$key] ?? ''))
    <div class="zarbin-seo-field">
        <label for="zarbin-seo-{{ $key }}">{{ $field['label'] }}</label>
        @if(($field['type'] ?? 'text') === 'textarea')
            <textarea id="zarbin-seo-{{ $key }}" name="{{ $name }}" rows="{{ $field['rows'] ?? 3 }}">{{ $value }}</textarea>
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

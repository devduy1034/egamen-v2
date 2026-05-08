@php
    $kind = !empty($kind) ? '.' . $kind : '';
    $params['page'] = $page;
@endphp

@if(!empty($name))
    <a class="text-dark text-break" href="{{ url('admin', ['com' => $com, 'act' => 'edit', 'type' => $type], $params ?? []) }}">{{$name}}</a>
@endif
{!! $slot !!}
@php
    $kind = !empty($kind) ? '.' . $kind : '';
@endphp
@if (!empty($configMan->view))
    <a class="text-primary mr-3" href="{{ url('slugweb', ['slug' => $slug]) }}" target="_blank"><i
            class="ti ti-eye-check mr-1"></i>View</a>
@endif
@if (Func::chekcPermission($tb . $kind . '.' . $type . '.edit', $permissions))
    <a class="text-secondary mr-3"
        href="{{ url('admin', ['com' => $com, 'act' => 'edit', 'type' => $type], $params ?? []) }}"><i
            class="ti ti-edit mr-1"></i>Edit</a>
@endif
{!! $slot !!}
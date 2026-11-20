@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <div class="tw:w-full tw:overflow-x-auto">
            {!! $data['html'] ?? '' !!}
        </div>
    </x-device.page>
@endsection

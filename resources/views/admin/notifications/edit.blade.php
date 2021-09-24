@extends('admin.default')

@section('page-header')
    <a href="{{ route('admin.notifications.index') }}">Notifications</a> <small>{{ trans('app.update_item') }}</small>
@stop

@section('content')
    {!! Form::model($item, [
            'route'  => [ ADMIN . '.notifications.update', $item->id ],
            'method' => 'put'
        ])
    !!}

    @include('admin.notifications.form')

    <button type="submit" class="btn btn-primary">{{ trans('app.edit_button') }}</button>

    {!! Form::close() !!}

@stop
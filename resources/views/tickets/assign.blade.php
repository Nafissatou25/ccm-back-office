@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-body">

        <h4>Assigner le ticket #{{ $ticket->id }}</h4>

        <form method="POST"
              action="{{ route('tickets.assign', $ticket->id) }}">

            @csrf

            <div class="mb-3">
                <label>Technicien</label>

                <select name="technician_id" class="form-control">
                    @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">
                            {{ $tech->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-success">
                Assigner
            </button>

        </form>

    </div>
</div>

@endsection
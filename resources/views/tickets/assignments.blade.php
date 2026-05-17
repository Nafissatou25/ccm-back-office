@extends('layouts.app')

@section('content')

<div class="content-wrapper">

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">

                    <h4 class="card-title mb-4">
                        Assignation des tickets
                    </h4>

                    <div class="table-responsive">

                        <table class="table table-bordered">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Agence</th>
                                    <th>Type</th>
                                    <th>Priorité</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($tickets as $ticket)

                                    <tr>

                                        <td>#{{ $ticket->id }}</td>

                                        <td>
                                            {{ $ticket->agency->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $ticket->type->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $ticket->priority }}
                                        </td>

                                        <td>
                                            {{ $ticket->status }}
                                        </td>

                                        <td>

                                            <form
                                                action="{{ route('tickets.assign', $ticket->id) }}"
                                                method="POST"
                                            >
                                                @csrf

                                                <div class="d-flex gap-2">

                                                    <select
                                                        name="assigned_to"
                                                        class="form-select"
                                                        required
                                                    >

                                                        <option value="">
                                                            Choisir technicien
                                                        </option>

                                                        @foreach($technicians as $tech)

                                                            <option value="{{ $tech->id }}">
                                                                {{ $tech->name }}
                                                            </option>

                                                        @endforeach

                                                    </select>

                                                    <button
                                                        type="submit"
                                                        class="btn btn-primary"
                                                    >
                                                        Assigner
                                                    </button>

                                                </div>

                                            </form>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

@endsection
<x-layout>
    <x-slot name="title">
        Users
    </x-slot>

    <div class="wrap">
        <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Telefoon</th>
                    <th>IsClient</th>
                    <th>IsCareGiver</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($persons as $person)
                    <tr class="@if($loop->index % 2 == 0 ) even @else odd @endif">
                        <td><a href="personen/{{ $person->Id }}">{{ $person->Firstname }} {{ $person->Lastname }}</a></td>
                        <td>{{ $person->PhoneNumber }}</td>
                        <td>{{ $person->IsClient }}</td>
                        <td>{{ $person->IsCareGiver }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready( function () {
            $('#dataTables').DataTable();
        } );
    </script>
</x-layout>

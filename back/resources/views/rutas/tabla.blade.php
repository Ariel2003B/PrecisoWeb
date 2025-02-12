@foreach ($rides as $route_name => $rideGroup)
    <div class="table-container" id="table-{{ str_replace(' ', '_', $route_name) }}">
        <h3 class="text-center">Ruta: {{ $route_name }}</h3>
        <div class="scrollable-table">
            <table class="table-rutas">
                <thead>
                    <tr>
                        <th class="sticky-header sticky-col" rowspan="2">Unidad</th>
                        <th class="sticky-header sticky-col-rutina" rowspan="2">Rutina</th>
                        @foreach ($rideGroup->first() as $ride)
                            {{-- <th colspan="3">{{ $ride->stop_name }}</th> --}}
                        @endforeach
                    </tr>
                    <tr>
                        @foreach ($rideGroup->first() as $ride)
                            <th class="col-peq">Plan</th>
                            <th class="col-peq">Eje</th>
                            <th class="col-peq">Dif</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rideGroup as $ride)
                        <tr>
                            <td class="sticky-col">{{ $ride->unit_name }}</td>
                            <td class="sticky-col-rutina">{{ $ride->plan_time }} - {{ $ride->exec_time }}</td>
                            <td class="col-peq">{{ $ride->plan_time }}</td>
                            <td class="col-peq">{{ $ride->exec_time }}</td>
                            <td
                                class="col-peq {{ $ride->diff < 0 ? 'negative' : ($ride->diff > 0 ? 'positive' : '') }}">
                                {{ $ride->diff }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach

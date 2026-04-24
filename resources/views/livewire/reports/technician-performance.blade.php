<div>
    <h1 class="text-lg font-semibold mb-4">Rendimiento de Técnicos</h1>
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Técnico</th>
                    <th class="px-3 py-2 text-center">Solicitudes totales</th>
                    <th class="px-3 py-2 text-center">Aprobadas</th>
                    <th class="px-3 py-2 text-center">Rechazadas</th>
                    <th class="px-3 py-2 text-center">Sobrantes</th>
                    <th class="px-3 py-2 text-center">Dañados</th>
                </tr>
            </thead>
            <tbody>
                @foreach($technicians as $tech)
                    <tr class="border-b">
                        <td class="px-3 py-2">{{ $tech->name }}</td>
                        <td class="px-3 py-2 text-center">{{ $tech->total_requests }}</td>
                        <td class="px-3 py-2 text-center text-green-600">{{ $tech->approved_requests }}</td>
                        <td class="px-3 py-2 text-center text-red-600">{{ $tech->rejected_requests }}</td>
                        <td class="px-3 py-2 text-center text-blue-600">{{ $tech->surplus_returns ?? 0 }}</td>
                        <td class="px-3 py-2 text-center text-orange-600">{{ $tech->damage_returns ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
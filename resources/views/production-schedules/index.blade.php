<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Production Schedules') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('production-schedules.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Schedule Production
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <div class="text-blue-800 text-sm font-medium">Today's Schedules</div>
                    <div class="text-blue-900 text-2xl font-bold">{{ $schedules->where('scheduled_date', today())->count() }}</div>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <div class="text-yellow-800 text-sm font-medium">In Progress</div>
                    <div class="text-yellow-900 text-2xl font-bold">{{ $schedules->where('status', 'in_progress')->count() }}</div>
                </div>
                <div class="bg-red-100 p-4 rounded-lg">
                    <div class="text-red-800 text-sm font-medium">Overdue</div>
                    <div class="text-red-900 text-2xl font-bold">{{ $schedules->filter(fn($s) => $s->isOverdue())->count() }}</div>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <div class="text-green-800 text-sm font-medium">High Priority</div>
                    <div class="text-green-900 text-2xl font-bold">{{ $schedules->whereIn('priority', ['high', 'urgent'])->count() }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheduled Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materials</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($schedules as $schedule)
                                    <tr class="{{ $schedule->isOverdue() ? 'bg-red-50' : ($schedule->isDueToday() ? 'bg-yellow-50' : '') }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $schedule->product->name }}
                                                        @if($schedule->isOverdue())
                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                Overdue
                                                            </span>
                                                        @elseif($schedule->isDueToday())
                                                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                                Due Today
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        SKU: {{ $schedule->product->sku ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div>
                                                <div class="font-medium">{{ $schedule->scheduled_date->format('M d, Y') }}</div>
                                                @if($schedule->scheduled_time)
                                                    <div class="text-xs text-gray-500">{{ $schedule->scheduled_time->format('H:i') }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $schedule->planned_quantity }} {{ $schedule->product->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{
                                                $schedule->priority === 'urgent' ? 'bg-red-100 text-red-800' :
                                                ($schedule->priority === 'high' ? 'bg-orange-100 text-orange-800' :
                                                ($schedule->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))
                                            }}">
                                                {{ ucfirst($schedule->priority) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $schedule->assignedUser ? $schedule->assignedUser->name : 'Unassigned' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{
                                                $schedule->status === 'completed' ? 'bg-green-100 text-green-800' :
                                                ($schedule->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                                ($schedule->status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                                ($schedule->status === 'delayed' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')))
                                            }}">
                                                {{ ucfirst(str_replace('_', ' ', $schedule->status)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($schedule->hasSufficientMaterials())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Available
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Insufficient
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('production-schedules.show', $schedule) }}"
                                                   class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-full transition-colors"
                                                   title="View">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                                <a href="{{ route('production-schedules.edit', $schedule) }}"
                                                   class="inline-flex items-center justify-center w-8 h-8 text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50 rounded-full transition-colors"
                                                   title="Edit">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                <form action="{{ route('production-schedules.destroy', $schedule) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-full transition-colors"
                                                            onclick="return confirm('Are you sure you want to delete this production schedule?')"
                                                            title="Delete">
                                                        <i class="fas fa-trash text-sm"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                            No production schedules found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $schedules->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

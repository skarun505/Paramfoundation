@extends('layouts.admin')
@section('title', 'Manage Slots')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
    <div>
        <p class="text-muted text-sm">Manage time slots for visitor entry</p>
    </div>
    <a href="{{ route('admin.slots.create') }}" class="btn btn-saffron">+ New Slot</a>
</div>

<div class="card">
    <div style="overflow-x:auto;">
        <table class="param-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time Slot</th>
                    <th>Price</th>
                    <th>Booked / Capacity</th>
                    <th>Occupancy</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slots as $slot)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $slot->date->format('D, d M') }}</div>
                        <div class="text-sm text-muted">{{ $slot->date->format('Y') }}</div>
                    </td>
                    <td>{{ $slot->label }}</td>
                    <td class="text-saffron fw-bold">&#8377;{{ number_format($slot->price) }}</td>
                    <td>
                        <span class="{{ $slot->is_full ? 'text-saffron' : '' }}">
                            {{ $slot->booked }}
                        </span>
                        <span class="text-muted"> / {{ $slot->capacity }}</span>
                    </td>
                    <td style="min-width:120px;">
                        @php $pct = $slot->capacity > 0 ? round(($slot->booked/$slot->capacity)*100) : 0; @endphp
                        <div class="progress-bar">
                            <div class="progress-fill {{ $pct>=80?'high':($pct>=50?'medium':'') }}"
                                 style="width:{{ $pct }}%"></div>
                        </div>
                        <div class="text-sm text-muted" style="margin-top:4px;">{{ $pct }}%</div>
                    </td>
                    <td>
                        @if($slot->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-red">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-8">
                            <a href="{{ route('admin.slots.edit', $slot) }}" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.slots.destroy', $slot) }}"
                                  onsubmit="return confirm('Delete this slot?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding:40px;">
                        No slots created yet. <a href="{{ route('admin.slots.create') }}">Create one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($slots->hasPages())
    <div class="pagination" style="margin-top:20px; padding-top:16px; border-top:1px solid var(--navy-light);">
        {{ $slots->links() }}
    </div>
    @endif
</div>
@endsection

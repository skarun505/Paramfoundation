@extends('layouts.admin')
@section('title', isset($slot) ? 'Edit Slot' : 'Create Slot')

@section('content')
<div style="max-width:600px;">

    <a href="{{ route('admin.slots.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:20px;">
        &larr; Back to Slots
    </a>

    <div class="card">
        <div class="card-header">
            <span class="card-title">{{ isset($slot) ? 'Edit Slot' : 'New Time Slot' }}</span>
        </div>

        <form method="POST"
              action="{{ isset($slot) ? route('admin.slots.update', $slot) : route('admin.slots.store') }}">
            @csrf
            @if(isset($slot)) @method('PUT') @endif

            <div class="form-group">
                <label for="label">Slot Label</label>
                <input type="text" id="label" name="label" class="param-input"
                       value="{{ old('label', $slot->label ?? '') }}"
                       placeholder="e.g. 10:00 AM – 11:00 AM" required>
                @error('label')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" class="param-input"
                       value="{{ old('date', isset($slot) ? $slot->date->format('Y-m-d') : '') }}"
                       min="{{ now()->toDateString() }}" required>
                @error('date')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" class="param-input"
                           value="{{ old('start_time', $slot->start_time ?? '') }}" required>
                    @error('start_time')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="param-input"
                           value="{{ old('end_time', $slot->end_time ?? '') }}" required>
                    @error('end_time')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="capacity">Capacity (max visitors)</label>
                    <input type="number" id="capacity" name="capacity" class="param-input"
                           value="{{ old('capacity', $slot->capacity ?? 50) }}"
                           min="1" max="5000" required>
                    @error('capacity')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label for="price">Price per person (&#8377;)</label>
                    <input type="number" id="price" name="price" class="param-input"
                           value="{{ old('price', $slot->price ?? 150) }}"
                           min="0" step="0.01" required>
                    @error('price')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $slot->is_active ?? true) ? 'checked' : '' }}>
                    <span>Slot is active (visible to customers)</span>
                </label>
            </div>

            <div class="d-flex gap-12" style="margin-top:8px;">
                <button type="submit" class="btn btn-saffron btn-lg">
                    {{ isset($slot) ? 'Update Slot' : 'Create Slot' }}
                </button>
                <a href="{{ route('admin.slots.index') }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SlotController extends Controller
{
    public function index()
    {
        $slots = Slot::orderBy('date')->orderBy('start_time')->paginate(20);
        return view('admin.slots.index', compact('slots'));
    }

    public function create()
    {
        return view('admin.slots.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'date'       => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'capacity'   => 'required|integer|min:1|max:5000',
            'price'      => 'required|numeric|min:0',
            'is_active'  => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Slot::create($validated);

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot created successfully.');
    }

    public function edit(Slot $slot)
    {
        return view('admin.slots.create', compact('slot'));
    }

    public function update(Request $request, Slot $slot)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:100',
            'date'       => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'capacity'   => 'required|integer|min:1|max:5000',
            'price'      => 'required|numeric|min:0',
            'is_active'  => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $slot->update($validated);

        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot updated successfully.');
    }

    public function destroy(Slot $slot)
    {
        $slot->delete();
        return redirect()->route('admin.slots.index')
            ->with('success', 'Slot deleted.');
    }

    /**
     * Bulk-create slots for a date range.
     */
    public function bulkCreate(Request $request)
    {
        $request->validate([
            'date_from'  => 'required|date|after_or_equal:today',
            'date_to'    => 'required|date|after_or_equal:date_from',
            'slots'      => 'required|array|min:1',
        ]);

        $current = Carbon::parse($request->date_from);
        $end     = Carbon::parse($request->date_to);
        $created = 0;

        while ($current->lte($end)) {
            foreach ($request->slots as $slotTemplate) {
                Slot::create([
                    'label'      => $slotTemplate['label'],
                    'date'       => $current->toDateString(),
                    'start_time' => $slotTemplate['start_time'],
                    'end_time'   => $slotTemplate['end_time'],
                    'capacity'   => $slotTemplate['capacity'],
                    'price'      => $slotTemplate['price'],
                    'is_active'  => true,
                ]);
                $created++;
            }
            $current->addDay();
        }

        return redirect()->route('admin.slots.index')
            ->with('success', "{$created} slots created successfully.");
    }
}

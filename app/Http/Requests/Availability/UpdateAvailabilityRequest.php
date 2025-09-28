<?php

namespace App\Http\Requests\Availability;

use App\Models\Availability;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ];
    }

    /**
     * Add custom validation logic after base rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $propertyId = $this->property_id;
            $startDate = $this->start_date;
            $endDate = $this->end_date;
            $currentId = $this->route('availability')?->id ?? $this->availability_id;

            // Check for overlapping availability (excluding current record)
            $availabilityConflict = Availability::where('property_id', $propertyId)
                ->where('id', '!=', $currentId)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($availabilityConflict) {
                $validator->errors()->add('start_date', 'This property already has availability in the selected date range.');
            }

            // Check for conflicting bookings
            $bookingConflict = Booking::where('property_id', $propertyId)
                ->where('status', '!=', 'rejected')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                        ->orWhereBetween('end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                        });
                })
                ->exists();

            if ($bookingConflict) {
                $validator->errors()->add('start_date', 'Cannot update availability: this property has active bookings in the selected date range.');
            }
        });
    }
}

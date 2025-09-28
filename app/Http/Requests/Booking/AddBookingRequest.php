<?php

namespace App\Http\Requests\Booking;

use App\Models\Availability;
use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class AddBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'guest_name'  => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'start_date'  => 'required|date|after_or_equal:today',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $propertyId = $this->property_id;
            $startDate = $this->start_date;
            $endDate = $this->end_date;

            // Check for conflicting bookings
            $conflict = Booking::where('property_id', $propertyId)
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

            if ($conflict) {
                $validator->errors()->add('start_date', 'This property is already booked for the selected dates.');
            }

            // Check if the property is available for the requested dates
            $available = Availability::where('property_id', $propertyId)
                ->where('start_date', '<=', $startDate)
                ->where('end_date', '>=', $endDate)
                ->exists();

            if (! $available) {
                $validator->errors()->add('start_date', 'This property is not available for the selected dates.');
            }
        });
    }
}

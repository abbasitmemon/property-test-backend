<?php

namespace App\Http\Requests\Booking;

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
            $exists = Booking::where('property_id', $this->property_id)
                ->where('status', '!=', 'rejected') // ignore rejected bookings
                ->where(function ($q) {
                    $q->whereBetween('start_date', [$this->start_date, $this->end_date])
                        ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                        ->orWhere(function ($q) {
                            $q->where('start_date', '<=', $this->start_date)
                                ->where('end_date', '>=', $this->end_date);
                        });
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add('start_date', 'This property is already booked for the selected dates.');
            }
        });
    }
}

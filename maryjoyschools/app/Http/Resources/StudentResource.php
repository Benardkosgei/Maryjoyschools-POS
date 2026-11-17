<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'student_id' => $this->student_id,
            'admission_number' => $this->admission_number,
            'class_grade' => $this->class_grade,
            'stream' => $this->stream,
            'house' => $this->house,
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null,
            'age' => $this->age,
            'gender' => $this->gender,
            'phone' => $this->phone,
            'email' => $this->email,
            'adresse' => $this->adresse,
            'city' => $this->city,
            'country' => $this->country,
            'parent_guardian_name' => $this->parent_guardian_name,
            'parent_guardian_phone' => $this->parent_guardian_phone,
            'parent_guardian_email' => $this->parent_guardian_email,
            'emergency_contact' => $this->emergency_contact,
            'account_balance' => (float) $this->account_balance,
            'credit_limit' => (float) $this->credit_limit,
            'available_credit' => $this->available_credit,
            'status' => $this->status,
            'notes' => $this->notes,
            'full_student_info' => $this->full_student_info,
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            
            // Include relationships when loaded
            'sales' => $this->whenLoaded('sales'),
            'sale_returns' => $this->whenLoaded('saleReturns'),
            'quotations' => $this->whenLoaded('quotations'),
            
            // Include computed fields when requested
            'total_purchases' => $this->when(isset($this->total_purchases), $this->total_purchases),
            'pending_balance' => $this->when(isset($this->pending_balance), $this->pending_balance),
        ];
    }
}
<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\PnpRegions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
        public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'rank' => ['required', 'string', 'in:' . implode(',', [
                'Police General (PGen)', 'Police Lieutenant General (PLtGen)', 'Police Major General (PMGen)',
                'Police Brigadier General (PBGen)', 'Police Colonel (PCol)', 'Police Lieutenant Colonel (PLtCol)',
                'Police Major (PMaj)', 'Police Captain (PCpt)', 'Police Lieutenant (PLt)',
                'Police Executive Master Sergeant (PEMS)', 'Police Chief Master Sergeant (PCMS)',
                'Police Senior Master Sergeant (PSMS)', 'Police Master Sergeant (PMSg)',
                'Police Staff Sergeant (PSSg)', 'Police Corporal (PCpl)', 'Patrolman/Patrolwoman (Pat)',
            ])],
            'unit_office' => ['required', 'string', 'max:255'],
             'region' => ['required', 'string', Rule::in(PnpRegions::all())],
            // Email is intentionally not editable: it is the sign-in identity
            // and can't be changed. Any 'email' sent is ignored.
        ];
    }
}

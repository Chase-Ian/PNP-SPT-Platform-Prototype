<?php

namespace App\Http\Requests;

use App\Models\User;
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
             'region' => 'required|string|in:' . implode(',', [
            'PRO NCR - National Capital Region (NCR)',
            'PRO 1 - Region 1 - Ilocos Region',
            'PRO 2 - Region 2 - Cagayan Valley',
            'PRO 3 - Region 3 - Central Luzon',
            'PRO 4A - Region 4A - CALABARZON',
            'PRO 4B - Region 4B - MIMAROPA',
            'PRO 5 - Region 5 - Bicol Region',
            'PRO 6 - Region 6 - Western Visayas',
            'PRO 7 - Region 7 - Central Visayas',
            'PRO 8 - Region 8 - Eastern Visayas',
            'PRO 9 - Region 9 - Zamboanga Peninsula',
            'PRO 10 - Region 10 - Northern Mindanao',
            'PRO 11 - Region 11 - Davao Region',
            'PRO 12 - Region 12 - SOCCSKSARGEN',
            'PRO 13 - Region 13 - Caraga Region',
            'PRO BARMM - Bangsamoro Autonomous Region (BARMM)',
            'PRO CAR - Cordillera Administrative Region (CAR)',
            'NHQ Camp Crame - PNP National Headquarters',
        ]),
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
        ];
    }

       protected function prepareForValidation(): void
        {
            $this->merge([
                'email' => preg_replace('/[\r\n]|%0[ad]/i', '', (string) $this->input('email')),
            ]);
        }
}

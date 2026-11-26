<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PnpRegions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One official list of PNP offices: the 18 Police Regional Offices plus
 * National Headquarters, and the mapping of saved old labels.
 */
class PnpRegionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_list_has_the_18_regional_offices_and_headquarters(): void
    {
        $regions = PnpRegions::all();

        $this->assertCount(19, $regions);
        $this->assertSame(18, count(array_filter($regions, fn ($r) => str_starts_with($r, 'PRO ') || str_starts_with($r, 'NCRPO '))));
        $this->assertContains('PRO NIR - Negros Island Region', $regions);
        $this->assertContains('NHQ Camp Crame - PNP National Headquarters', $regions);
    }

    public function test_old_labels_and_variants_map_to_the_official_names(): void
    {
        foreach (PnpRegions::PREVIOUS_LABELS as $new => $old) {
            $this->assertSame($new, PnpRegions::normalize($old), "Old label: {$old}");
            $this->assertSame($new, PnpRegions::normalize($new));
        }

        $this->assertSame('NCRPO - National Capital Region Police Office', PnpRegions::normalize('NCR'));
        $this->assertSame('NCRPO - National Capital Region Police Office', PnpRegions::normalize('National Capital Region (NCR)'));
        $this->assertSame('PRO NIR - Negros Island Region', PnpRegions::normalize('Negros Island Region'));
        $this->assertSame('PRO 10 - Northern Mindanao', PnpRegions::normalize('PRO 10'));
        $this->assertSame('PRO 4A - CALABARZON', PnpRegions::normalize('Region IV-A'));
        $this->assertSame('PRO 13 - Caraga', PnpRegions::normalize('Caraga Region'));
        $this->assertNull(PnpRegions::normalize('Somewhere else'));
        $this->assertNull(PnpRegions::normalize(''));
    }

    public function test_each_office_has_its_own_control_number_code(): void
    {
        $codes = array_map(fn ($region) => PnpRegions::code($region), PnpRegions::all());

        $this->assertSame(count($codes), count(array_unique($codes)));
        $this->assertSame('NCRPO', PnpRegions::code('NCRPO - National Capital Region Police Office'));
        $this->assertSame('PRO1', PnpRegions::code('PRO 1 - Ilocos Region'));
        $this->assertSame('PRO10', PnpRegions::code('PRO 10 - Northern Mindanao'));
        $this->assertSame('PRO4A', PnpRegions::code('PRO 4A - CALABARZON'));
        $this->assertSame('PRO4B', PnpRegions::code('PRO 4B - MIMAROPA'));
        $this->assertSame('PROBAR', PnpRegions::code('PRO BAR - Bangsamoro Autonomous Region in Muslim Mindanao'));
        $this->assertSame('PRONIR', PnpRegions::code('PRO NIR - Negros Island Region'));
        $this->assertSame('NHQ', PnpRegions::code('NHQ Camp Crame - PNP National Headquarters'));

        // Old labels still get the right code; unknown or missing offices get REG.
        $this->assertSame('NCRPO', PnpRegions::code('PRO NCR - National Capital Region (NCR)'));
        $this->assertSame('PROCOR', PnpRegions::code('PRO CAR - Cordillera Administrative Region (CAR)'));
        $this->assertSame('REG', PnpRegions::code('Somewhere else'));
        $this->assertSame('REG', PnpRegions::code(null));
    }

    public function test_the_migration_maps_saved_regions(): void
    {
        $old = User::factory()->create(['region' => 'PRO CAR - Cordillera Administrative Region (CAR)']);
        $typed = User::factory()->create(['region' => 'NCR']);
        $unknown = User::factory()->create(['region' => 'Somewhere else']);

        (require database_path('migrations/2026_11_26_000002_use_official_pnp_regional_office_names.php'))->up();

        $this->assertSame('PRO COR - Cordillera Administrative Region', $old->fresh()->region);
        $this->assertSame('NCRPO - National Capital Region Police Office', $typed->fresh()->region);
        $this->assertSame('Somewhere else', $unknown->fresh()->region);
    }

    public function test_trainees_can_sign_up_under_pro_nir_but_not_an_old_label(): void
    {
        $form = [
            'first_name' => 'Ana', 'last_name' => 'Reyes', 'rank' => 'Patrolman/Patrolwoman (Pat)',
            'email' => 'ana.reyes@pnp.gov.ph', 'unit_office' => 'Bacolod CPO - Police Station 1',
            'password' => 'Strong-Pass-2026', 'password_confirmation' => 'Strong-Pass-2026', 'terms' => true,
        ];

        $this->post('/register', [...$form, 'region' => 'PRO 6 - Region 6 - Western Visayas'])->assertSessionHasErrors('region');

        $this->post('/register', [...$form, 'region' => 'PRO NIR - Negros Island Region'])->assertSessionHasNoErrors();
        $this->assertSame('PRO NIR - Negros Island Region', User::where('email', 'ana.reyes@pnp.gov.ph')->value('region'));
    }

    public function test_the_sign_up_page_gets_the_list(): void
    {
        $this->get('/register')->assertInertia(fn ($page) => $page
            ->component('Auth/Register')
            ->where('regionOptions', PnpRegions::all()));
    }
}

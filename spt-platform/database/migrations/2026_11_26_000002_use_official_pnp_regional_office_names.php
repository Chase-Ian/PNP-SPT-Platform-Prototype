<?php

use App\Support\PnpRegions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Saved offices (users.region) switch to the PNP's official names from
 * config/pnp.php: NCRPO, PRO COR, PRO BAR and shorter labels for PRO 1 to
 * PRO 13. Old labels and common variants (for example plain "NCR") are mapped;
 * anything that can't be told apart is left for the person to pick again on
 * their profile. PRO NIR is new: Negros and Siquijor personnel saved under
 * PRO 6 or PRO 7 choose it themselves.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('users')->whereNotNull('region')->distinct()->pluck('region') as $old) {
            $new = PnpRegions::normalize($old);

            if ($new !== null && $new !== $old) {
                DB::table('users')->where('region', $old)->update(['region' => $new]);
            }
        }
    }

    public function down(): void
    {
        foreach (PnpRegions::PREVIOUS_LABELS as $new => $old) {
            DB::table('users')->where('region', $new)->update(['region' => $old]);
        }
    }
};

<?php

namespace App\Console\Commands;

use App\Models\Brands;
use App\Models\VehicleModel;
use App\Support\DeviceModels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fills phone_details.model on existing phone listings by matching the
 * advert title against its brand's models (see DeviceModelMatcher). Only
 * listings with no model yet are touched, and titles that don't name exactly
 * one known model are left unassigned — they still show on the brand page.
 */
class BackfillPhoneModels extends Command
{
    protected $signature = 'phones:backfill-models {--dry-run} {--brand= : Only this brand id}';

    protected $description = 'Assign a model to phone listings that have none, inferred from the advert title';

    private const PHONES_SUBCAT_ID = 6;

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $brandIds = VehicleModel::query()
            ->whereIn('brand_id', Brands::where('subcat_id', self::PHONES_SUBCAT_ID)->select('id'))
            ->when($this->option('brand'), fn ($q, $id) => $q->where('brand_id', $id))
            ->distinct()
            ->pluck('brand_id');

        foreach ($brandIds as $brandId) {
            $brand = Brands::find($brandId);
            $models = VehicleModel::where('brand_id', $brandId)->pluck('model', 'id')->all();
            $matcher = DeviceModels::matcher($brandId);

            $rows = DB::table('phone_details')
                ->join('adverts', 'adverts.id', '=', 'phone_details.advert_id')
                ->where('adverts.brand', $brandId)
                ->where(fn ($q) => $q->whereNull('phone_details.model')->orWhereIn('phone_details.model', ['', '0']))
                ->get(['phone_details.id', 'adverts.ad_title']);

            $perModel = [];
            $unmatched = [];

            foreach ($rows as $row) {
                $modelId = $matcher->match((string) $row->ad_title);

                if ($modelId === null) {
                    $unmatched[] = $row->ad_title;
                    continue;
                }

                $perModel[$modelId][] = $row->ad_title;

                if (!$dryRun) {
                    DB::table('phone_details')->where('id', $row->id)->update(['model' => $modelId]);
                }
            }

            $matched = array_sum(array_map('count', $perModel));
            $this->info(sprintf('%s (brand %d): %d of %d listings %s a model, %d left unassigned.',
                $brand->brand, $brandId, $matched, count($rows), $dryRun ? 'would get' : 'got', count($unmatched)));

            uasort($perModel, fn ($a, $b) => count($b) <=> count($a));
            $this->table(['Model', 'Listings', 'Example titles'], collect($perModel)->map(fn ($titles, $id) => [
                $models[$id],
                count($titles),
                implode(' | ', array_map(fn ($t) => mb_strimwidth($t, 0, 40, '…'), array_slice($titles, 0, 3))),
            ])->values()->all());

            if ($unmatched && $this->output->isVerbose()) {
                $this->line('Unassigned titles:');
                foreach ($unmatched as $title) {
                    $this->line("  - {$title}");
                }
            }
        }

        return self::SUCCESS;
    }
}

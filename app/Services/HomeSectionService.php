<?php

namespace App\Services;

use App\Http\Resources\HomeSectionResource;
use App\Models\HomeSection;
use Illuminate\Support\Facades\DB;

class HomeSectionService
{
    public function index()
    {
        // $sections=HomeSection::with(
        //     [   'providers' /*=> function ($q) {
        //             $q->withAvg('reviews', 'rate');
        //         }*/,
        //        // 'providers.attachedServices',
        //       //  'providers.providerType',
        //       //  'providers.operationalHours',     
        //         'providers.reviews',
        //      //   'providers.user.activeSubscription',
        //        // 'providers.addresses',
        //     ])
        //     ->get();

        $sections = HomeSection::with([
            'providers' => function ($q) {
                $q->where('is_active', 1)->where('published', 1);
            },
            'providers.reviews',
        ])->get();

        return HomeSectionResource::collection($sections);
    }
}

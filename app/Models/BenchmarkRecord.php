<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenchmarkRecord extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'hash',
        'gpu',
        'videoRam',
        'ram',
        'windowed',
        'antiAliasing',
        'anisotropic',
        'aspectRatio',
        'drawDistance',
        'version',
        'tag',
        'netcode',
        'os',
        'overall',
        'idleInstructions',
        'scmInstructions',
        'copScene',
        'vehicleCarrec',
        '10Threads',
        '50Threads',
        '100Threads',
        'kickstartStadium',
        'vehicleLoad1',
        'vehicleLoad2',
        'actorLoad1',
        'actorLoad2',
        'vehiclePath1',
        'vehiclePath2',
        'benchmarkVersion',
    ];
}
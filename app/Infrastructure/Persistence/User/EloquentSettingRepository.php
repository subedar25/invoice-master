<?php

namespace App\Infrastructure\Persistence\User;

use App\Core\User\Contracts\SettingRepository;
use App\Models\Setting;

class EloquentSettingRepository implements SettingRepository
{
    public function all()
    {
        return Setting::all();
    }

    public function findByKey(string $key)
    {
        return Setting::where('key', $key)->first();
    }

    public function updateOrCreate(array $attributes, array $values = [])
    {
        return Setting::updateOrCreate($attributes, $values);
    }
}

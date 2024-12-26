<?php

namespace PNS\Admin\Extensions\Settings\Models;
use App\Helpers\ArrayHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SettingGroup
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Setting[] $settings
 * @property-read int|null $settings_count
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SettingGroup query()
 */

class SettingGroup extends Model
{

    protected $table = 'setting_groups';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Get the settings for the setting group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings()
    {
        return $this->hasMany(Setting::class, 'group_id');
    }

    /**
     * Get a key-value list of setting groups.
     *
     * @return array
     */
    public static function getListKV()
    {
        return self::select(['id', 'name'])->get()->pluck('name', 'id')->toArray();
    }

}

<?php

namespace PNS\Admin\Extensions\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PNS\Admin\Extensions\Settings\Models\Setting
 *
 * @property int $id
 * @property string $label
 * @property string $name
 * @property string $value
 * @property string $cast_type
 * @property string|null $description
 * @property int $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $casted_value
 * @property-read \PNS\Admin\Extensions\Settings\Models\SettingGroup $group
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 */

class Setting extends Model
{

    protected $table = 'settings';

    const CAST_TYPE_TEXT = 'text';
    const CAST_TYPE_INTEGER = 'int';
    const CAST_TYPE_FLOAT = 'float';
    const CAST_TYPE_JSON = 'json';
    const CAST_TYPE_JSON_ASSOC = 'json_assoc';
    const CAST_TYPE_BOOLEAN = 'boolean';
    const CAST_TYPE_IMAGE = 'image';
    const CAST_TYPE_FILE = 'file';
    const CAST_TYPE_HTML = 'html';
    const CAST_TYPE_CSS = 'css';
    const CAST_TYPE_JS = 'js';

    const CASTS = [
        self::CAST_TYPE_TEXT => 'Text',
        self::CAST_TYPE_INTEGER => 'Integer',
        self::CAST_TYPE_FLOAT => 'Float',
        self::CAST_TYPE_JSON => 'JSON',
        self::CAST_TYPE_JSON_ASSOC => 'JSON Assoc',
        self::CAST_TYPE_BOOLEAN => 'Boolean',
        self::CAST_TYPE_IMAGE => 'Image',
        self::CAST_TYPE_FILE => 'File',
        self::CAST_TYPE_HTML => 'HTML',
        self::CAST_TYPE_CSS => 'CSS',
        self::CAST_TYPE_JS => 'JS',
    ];

    protected $appends = ['casted_value'];

    protected $fillable = [
        'label',
        'name',
        'value',
        'cast_type',
        'description',
        'group_id',
        'sort'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function group()
    {
        return $this->belongsTo(SettingGroup::class, 'group_id');
    }

    public static function __load()
    {
        foreach (self::all() as $config) {
            config(['settings.' . $config->name => $config->casted_value]);
        }
    }

    public static function __reload()
    {
        config(['settings' => []]);
        foreach (self::all() as $config) {
            config(['settings.' . $config->name => $config->casted_value]);
        }
    }

    public static function __init() {}

    public function getCastedValueAttribute()
    {
        $value = $this->value;
        if ($this->cast_type === self::CAST_TYPE_JSON) {
            return @json_decode($value);
        }

        if ($this->cast_type === self::CAST_TYPE_JSON_ASSOC) {
            return @json_decode($value, true);
        }

        if ($this->cast_type === self::CAST_TYPE_BOOLEAN) {
            return boolVal($value);
        }

        if ($this->cast_type === self::CAST_TYPE_FLOAT) {
            return floatval($value);
        }

        if ($this->cast_type === self::CAST_TYPE_INTEGER) {
            return intval($value);
        }

        return $value;
    }
}

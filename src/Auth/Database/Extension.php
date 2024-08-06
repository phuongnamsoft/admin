<?php

namespace PNS\Admin\Auth\Database;

use PNS\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    use DefaultDatetimeFormat;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'config', 'enabled', 'is_default'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.extensions_table'));

        parent::__construct($attributes);
    }

    static public function loadExtensions() {
        config(['admin.extensions' => self::get()->toArray()]);
    }

    public function scopeEnabled($query, $enabled = true) {
        return $query->where('enabled', $enabled ? 1 : 0);
    }

    public function scopeDefault($query) {
        return $query->where('is_default', 1);
    }

    /**
     *
     * @param string $path
     *
     * @return mixed
     */
    public function getMenuIdsAttribute($menuIds)
    {
        return is_string($menuIds) ? array_filter(array_map('intval', json_decode($menuIds))) : (array) $menuIds;
    }

    
}

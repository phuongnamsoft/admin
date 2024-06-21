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
}

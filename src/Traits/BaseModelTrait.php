<?php

namespace PNS\Admin\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;

trait BaseModelTrait
{
    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function getTableColumns()
    {
        return Schema::getColumnListing($this->table);
    }

    public function scopeActive($query)
    {
        $query->where('status', 1);
        return $query;
    }

    static public function updateBy($data, $cond = []) {
        $query = new static;
        if (!empty($cond)) {
            foreach ($cond as $k => $v) {
                $query = $query->where($k, $v);
            }
        }
        return $query->update($data);
    }

    static public function getRowBy($cond = []) {
        $query = new static;
        if (!empty($cond)) {
            foreach ($cond as $k => $v) {
                $query = $query->where($k, $v);
            }
        }
        return $query->limit(1)->first();
    }

    static public function getById($id) {
        $query = new static;
        return $query->where($query->primaryKey, $id)->limit(1)->first();
    }

    static public function findBy($params)
    {
        $query = new static;
        foreach($params as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->limit(1)->first();
    }

    static public function updateById($data, $id) {
        return self::where('id', $id)->update($data);
    }

    static public function deleteByIds($ids) {
        if (is_string($ids)) {
            $ids = array_map(function ($id) {
                return intval(trim($id));
            }, explode(',' ,$ids));
        }

        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        return static::where('id', $ids)->delete();
    }

    static public function deleteBy($cond = []) {
        $query = new self;
        if (!empty($cond)) {
            foreach ($cond as $k => $v) {
                $query = $query->where($k, $v);
            }
            return $query->delete();
        }
    }

    public function getNextRecordId()
    {
        return self::where('id', '>', $this->id)->min('id');
    }

    public function getPrevRecordId()
    {
        return self::where('id', '<', $this->id)->max('id');
    }

    public function getNextRecord() {
        return self::find($this->getNextRecordId());
    }

    public function getPrevRecord() {
        return self::find($this->getPrevRecordId());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByDateRange(Builder $query, $startAt = null, $endAt = null, $key = 'created_at'): Builder
    {
        if (!empty($startAt)) {
            $query = $query->where($key, '>=', $startAt);
        }

        if (!empty($endAt)) {
            $query = $query->where($key, '<=', $endAt);
        }
        
        return $query;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByToday(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByTomorrow(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-d 00:00:00', strtotime('+1 day')), date('Y-m-d 23:59:59', strtotime('+1 day')), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByYesterday(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d 23:59:59', strtotime('-1 day')), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByThisMonth(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59'), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByLastMonth(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-01 00:00:00', strtotime('-1 month')), date('Y-m-t 23:59:59', strtotime('-1 month')), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByThisYear(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-01-01 00:00:00'), date('Y-12-31 23:59:59'), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByLastYear(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-01-01 00:00:00', strtotime('-1 year')), date('Y-12-31 23:59:59', strtotime('-1 year')), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByThisWeek(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-d 00:00:00', strtotime('monday this week')), date('Y-m-d 23:59:59', strtotime('sunday this week')), $key);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterByLastWeek(Builder $query, $key = 'created_at'): Builder
    {
        return $query->filterByDateRange(date('Y-m-d 00:00:00', strtotime('monday last week')), date('Y-m-d 23:59:59', strtotime('sunday last week')), $key);
    }

}
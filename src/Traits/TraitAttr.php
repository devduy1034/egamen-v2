<?php



namespace LARAVEL\Traits;

trait TraitAttr
{
    public function getSeo($com = 'product', $act = 'man')
    {
        $currentModelClass = get_class($this);
        $currentTableName = (new $currentModelClass)->getTable();
        return $this->belongsTo('\LARAVEL\Models\SeoModel', 'id', 'id_parent')
            ->join($currentTableName, $currentTableName . '.id', '=', 'seo.id_parent')
            ->select('seo.*', $currentTableName . '.photo', $currentTableName . '.options as base_options')
            ->where('seo.type', $this->type)
            ->where('seo.act', $act)
            ->where('seo.com', $com);
    }
    public function getPhotos()
    {
        return $this->hasMany('\LARAVEL\Models\GalleryModel', 'id_parent', 'id')->where('type_parent', $this->type);
    }
    /**
     * Scope cho trường hợp date_publish null hoặc nhỏ hơn thời gian hiện tại.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder $query
     * @return \LARAVEL\DatabaseCore\Eloquent\Builder
     */
    public function scopedatePublish($query): \LARAVEL\DatabaseCore\Eloquent\Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('date_publish')->orWhere('date_publish', '<', \Carbon\Carbon::now());
        });
    }
}

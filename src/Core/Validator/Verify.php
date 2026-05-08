<?php
namespace LARAVEL\Core\Validator;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use DB;
use Illuminate\Support\Str;
use LARAVEL\Core\Support\Facades\Hash;

trait Verify
{
    public function min($value, array $rules, float $min): void
    {
        switch (true) {
            case in_array('number', $rules):
                if ($min > (int) $value) {
                    $this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'number'], __FUNCTION__, [
                        'min' => $min
                    ]),'min');
                }
                break;
            case in_array('file', $rules) || in_array('video', $rules) || in_array('audio', $rules) || in_array('image', $rules):
                $sizeMb = $value->size / 1000 / 1000;
                if ($min > $sizeMb) {
                    $this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'file'], __FUNCTION__, [
                        'min' => $min
                    ]),'min');
                }
                break;
            case 'string':
            default:
                if (strlen((string) $value) < $min) {
                    $this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'string'], __FUNCTION__, [
                        'min' => $min
                    ]),'min');
                }
        }
    }
    public function max($value, array $rules, float $max): void
    {
        switch (true) {
            case in_array('number', $rules):
                if ($max < (int) $value) {
                    $this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'number'], __FUNCTION__, [
                        'max' => $max
                    ]),'max');
                }
                break;
            case in_array('file', $rules) || in_array('video', $rules) || in_array('audio', $rules) || in_array('image', $rules):
                $sizeMb = $value->size / 1000 / 1000;
                if ($max < $sizeMb) {
                    $this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'file'], __FUNCTION__, [
                        'max' => $max
                    ]),'max');
                }
                break;
            case 'string':
            default:
                if (strlen((string) $value) > $max) {
                    $this->pushErrorMessage($this->current, $this->buildErrorMessage([$this->current, 'string'], __FUNCTION__, [
                        'max' => $max
                    ]),'max');
                }
        }
    }
    public function number($value): void
    {
        if (!is_numeric($value)) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'number');
        }
    }
    public function string($value): void
    {
        if (!is_string($value)) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'string');
        }
    }
    public function required($value): void
    {
        if (empty($value)) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'required');
        }
    }
    public function file($value): void
    {
        if (!$value instanceof UploadedFile) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'file');
        }
    }
    public function image($value): void
    {

        if (!$value instanceof UploadedFile || strpos($value->getMimeType(), 'image/') === false) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'image');
        }
    }

    public function mimes($value, $ruleValue): void
    {
       
        if (!$value instanceof UploadedFile || (str_contains($ruleValue, explode('/',$value->getMimeType())[1])) === false) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'mimes');
        }
    }

    // public function mime($value): void
    // {

    //     if (!$value instanceof File || strpos($value->type, 'image/') === false) {
    //         $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__));
    //     }
    // }

    public function audio($value): void
    {
        if (!$value instanceof UploadedFile || strpos($value->getMimeType(), 'audio/') === true) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'audio');
        }
    }
    public function video($value): void
    {
        if (!$value instanceof UploadedFile || strpos($value->getMimeType(), 'video/') === true) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'video');
        }
    }
    public function email($value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'email');
        }
    }
    public function same($value,$ruleValue){
        if(!Str::is($this->passable->request->get($ruleValue),$value)){
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'same');
        }
    }
    public function unique($value, $ruleValue): void
    {
        list($table, $columnValue) = explode(',', $ruleValue);
        if (str_contains($columnValue, ';')) list($column, $keyValue) = explode(';', $columnValue);
        else $column = $columnValue;
        $table = DB::table($table)->where($column, $keyValue)->first();
        if ($table && isset($keyValue) && $table->$column == $keyValue || $table && !isset($keyValue)) {
            $this->pushErrorMessage($this->current, $this->buildErrorMessage($this->current, __FUNCTION__),'unique');
        }
    }
    public function handleCustomRule($rule): void
    {
        $handle = $this->getCustom($rule);
        if (!$handle($this->passable)) {
            $this->pushErrorMessage($this->current, $this->customMessages[$rule],$rule);
        }
    }
}

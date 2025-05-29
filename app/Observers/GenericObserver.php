<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class GenericObserver
{
    public function creating(Model $model)
    {
        $model->created_by = Auth::id();
        $model->updated_by = Auth::id();
    }

    public function updating(Model $model)
    {
        $model->updated_by = Auth::id();
    }

    public function deleting(Model $model)
    {
        $model->deleted_by = Auth::id();
        $model->save(); // Save changes before soft deleting
    }
}
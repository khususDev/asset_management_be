<?php

namespace App\Services\Administration;

use Illuminate\Database\Eloquent\Model;

class MasterDataService
{
    public function listItems($query, array $searchFields, int $entries): mixed
    {
        $search = request()->search ?? '';

        if ($search) {
            $query->where(function ($q) use ($searchFields, $search): void {
                foreach ($searchFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        return $query->latest()->paginate($entries);
    }

    public function createModel(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model;
    }

    public function updateModel(Model $model, array $data): Model
    {
        $model->fill($data);
        $model->save();

        return $model;
    }
}

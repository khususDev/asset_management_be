<?php

namespace App\Services\Authorization;

class RoleAccessService
{
    private function hasLoadedRelation($model, string $relation): bool
    {
        if (!$model) {
            return false;
        }

        if (method_exists($model, 'relationLoaded')) {
            return $model->relationLoaded($relation);
        }

        return false;
    }

    public function resolveForAuth($user): array
    {
        $roleIds = [];
        $roleNames = [];
        $permissionNames = [];

        if (!empty($user->role_id)) {
            $roleIds[] = (int) $user->role_id;
        }

        if ($this->hasLoadedRelation($user, 'role') && $user->role) {
            $roleNames[] = $user->role->name;
        }

        if ($this->hasLoadedRelation($user, 'roles') && $user->roles) {
            foreach ($user->roles as $role) {
                $roleIds[] = (int) $role->id;
                $roleNames[] = $role->name;
            }
        }

        $roleIds = array_values(array_unique($roleIds));
        $roleNames = array_values(array_unique(array_filter($roleNames)));

        if ($user->role && isset($user->role->permissions) && $user->role->permissions) {
            $permissionNames = $user->role->permissions->pluck('name')->all();
        }

        return [
            'role_ids' => $roleIds,
            'role_names' => $roleNames,
            'permission_names' => $permissionNames,
        ];
    }
}

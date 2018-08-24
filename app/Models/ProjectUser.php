<?php

namespace App\Models;

use App\Models\Pivot;
use App\Models\Project;
use App\Models\User;
use App\Models\Role;

class ProjectUser extends Pivot
{
    /**
     * 获取对应项目。
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * 获取对应用户。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取对应项目角色。
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 删除该项目角色相关的所有记录
     */
    public function deleteAll()
    {
        $nodes = $this->project->nodes;
        foreach ($nodes as $node) {
            $node->users()->detach($this->user_id);
        }
        $this->delete();
    }

    /**
     * 删除该项目角色相关的节点角色记录
     */
    public function deleteNodeUser()
    {
        $user = User::findOrFail($this->user_id)
            ->nodes()
            ->detach();
    }
}

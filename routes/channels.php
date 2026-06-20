<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.notifications', function ($user) {
    return in_array($user->role, ['super_admin', 'manager'], true)
        ? ['id' => $user->id, 'name' => $user->name]
        : false;
});

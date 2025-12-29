<?php

use Illuminate\Support\Facades\Broadcast;

// Register broadcasting auth route for API guard
Broadcast::routes(['middleware' => ['auth:api']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

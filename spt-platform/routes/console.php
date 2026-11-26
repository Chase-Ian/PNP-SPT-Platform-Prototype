<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Erase accounts deleted more than DELETED_ACCOUNT_DAYS ago (User::prunable()).
Schedule::command('model:prune', ['--model' => [User::class]])->daily();

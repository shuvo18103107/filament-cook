<?php

use Illuminate\Support\Facades\Route;


Route::get('/conference-sign-up', \App\Livewire\ConferenceSignUpPage::class)
    ->name('conference-sign-up');

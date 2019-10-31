<?php

use Illuminate\Support\Facades\Route;

Route::post('/report', 'SmsUpReportController@report')->name('smsup.report');
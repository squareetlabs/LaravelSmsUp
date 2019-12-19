<?php

namespace SquareetLabs\LaravelSmsUp\Http\Controllers;

use Illuminate\Support\Facades\Event;
use SquareetLabs\LaravelSmsUp\Events\SmsUpReportWasReceived;
use SquareetLabs\LaravelSmsUp\SmsUpReportResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class SmsUpReportController
 * @package SquareetLabs\LaravelSmsUp\Http\Controllers
 */
class SmsUpReportController extends Controller
{
    /**
     * @param Request $request
     */
    public function report(Request $request)
    {
        $responseReport = new SmsUpReportResponse($request->all());
        Event::dispatch(new SmsUpReportWasReceived($responseReport));
    }
}
<?php

use App\Mail\Transports\GoogleAppsScriptTransport;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

uses(Tests\TestCase::class);

test('GoogleAppsScriptTransport throws exception if endpoint is empty', function () {
    config(['mail.mailers.gas.endpoint' => '']);

    expect(fn () => Mail::mailer('gas')->raw('Hello', fn ($m) => $m->to('test@example.com')))
        ->toThrow(TransportException::class);
});

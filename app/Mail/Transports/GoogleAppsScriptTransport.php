<?php

namespace App\Mail\Transports;

use GuzzleHttp\Client;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class GoogleAppsScriptTransport extends AbstractTransport
{
    public function __construct(
        protected string $endpointUrl,
        protected ?string $secret = null
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if (empty($this->endpointUrl)) {
            throw new TransportException('Google Apps Script relay URL is not configured. Please set GMAIL_RELAY_URL.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = [];
        foreach ($email->getTo() as $address) {
            $to[] = $address->getAddress();
        }

        $html = $email->getHtmlBody();
        if (is_resource($html)) {
            $html = stream_get_contents($html);
        }

        $text = $email->getTextBody();
        if (is_resource($text)) {
            $text = stream_get_contents($text);
        }

        $payload = [
            'to' => implode(', ', $to),
            'subject' => $email->getSubject(),
            'html' => $html ?? $text,
            'text' => $text ?? strip_tags((string) $html),
            'from_name' => config('mail.from.name', 'Student Clearance System'),
        ];

        if ($this->secret) {
            $payload['secret'] = $this->secret;
        }

        $client = new Client([
            'allow_redirects' => true,
            'timeout' => 15,
        ]);

        try {
            $response = $client->post($this->endpointUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);
            if (isset($body['status']) && $body['status'] === 'error') {
                throw new TransportException('Google Apps Script relay error: ' . ($body['message'] ?? 'Unknown error'));
            }
        } catch (\Throwable $e) {
            if ($e instanceof TransportException) {
                throw $e;
            }
            throw new TransportException('Failed to communicate with Google Apps Script relay: ' . $e->getMessage(), 0, $e);
        }
    }

    public function __toString(): string
    {
        return 'gas';
    }
}

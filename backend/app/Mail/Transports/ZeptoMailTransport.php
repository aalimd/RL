<?php

namespace App\Mail\Transports;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\RawMessage;

class ZeptoMailTransport extends AbstractTransport
{
    protected HttpClient $client;

    public function __construct(
        protected string $apiKey,
        protected string $host
    ) {
        parent::__construct();
        $this->client = new HttpClient();
    }

    protected function doSend(SentMessage $message): void
    {
        $rawMessage = $message->getOriginalMessage();
        $email = MessageConverter::toEmail($rawMessage);
        $envelope = $message->getEnvelope();

        $payload = $this->getPayload($email, $envelope);
        $payload['from'] = $this->getFrom($rawMessage);

        try {
            $response = $this->client->post($this->getEndpoint(), [
                'headers' => [
                    'Authorization' => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel',
                ],
                'json' => $payload,
            ]);

            $this->assertSuccessfulResponse($response);
        } catch (ConnectException $e) {
            Log::error('ZeptoMail connection error: ' . $e->getMessage());
            throw new TransportException('Failed to connect to ZeptoMail.', 0, $e);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $statusCode = $e->getResponse()->getStatusCode();
                $errorBody = $e->getResponse()->getBody()->getContents();
                Log::error("ZeptoMail API error: HTTP {$statusCode}", ['response' => $errorBody]);
                throw new TransportException("ZeptoMail API error: {$errorBody}", 0, $e);
            }

            Log::error('ZeptoMail request error: ' . $e->getMessage());
            throw new TransportException('ZeptoMail request failed.', 0, $e);
        } catch (\Throwable $e) {
            Log::error('Unexpected ZeptoMail error: ' . $e->getMessage());
            throw new TransportException('An unexpected error occurred while sending mail.', 0, $e);
        }
    }

    public function __toString(): string
    {
        return 'zeptomail';
    }

    protected function getFrom(RawMessage $message): array
    {
        $from = [];

        if ($message instanceof Email) {
            $from = $message->getFrom();
        }

        if ($from !== []) {
            return [
                'name' => $from[0]->getName(),
                'address' => $from[0]->getAddress(),
            ];
        }

        return ['name' => '', 'address' => ''];
    }

    private function getEndpoint(): string
    {
        $domain = $this->domainMapping[$this->host] ?? $this->host;

        return 'https://zeptomail.' . $domain . '/v1.1/email';
    }

    private function getPayload(Email $email, Envelope $envelope): array
    {
        $recipients = $this->getRecipients($email, $envelope);
        $toAddress = $this->getEmailDetailsByType($recipients, 'to');
        $ccAddress = $this->getEmailDetailsByType($recipients, 'cc');
        $bccAddress = $this->getEmailDetailsByType($recipients, 'bcc');
        $attachments = [];

        $payload = [
            'subject' => $email->getSubject(),
            'htmlbody' => $email->getHtmlBody() ?? $email->getTextBody(),
        ];

        if ($toAddress !== []) {
            $payload['to'] = $toAddress;
        }

        if ($ccAddress !== []) {
            $payload['cc'] = $ccAddress;
        }

        if ($bccAddress !== []) {
            $payload['bcc'] = $bccAddress;
        }

        foreach ($email->getAttachments() as $attachment) {
            $headers = $attachment->getPreparedHeaders();
            $filename = $headers->getHeaderParameter('Content-Disposition', 'filename');

            $attachmentItem = [
                'content' => base64_encode((string) $attachment->getBody()),
                'name' => $filename,
                'mime_type' => $headers->get('Content-Type')->getBody(),
            ];

            if ($name = $headers->getHeaderParameter('Content-Disposition', 'name')) {
                $attachmentItem['name'] = $name;
            }

            $attachments[] = $attachmentItem;
        }

        if ($attachments !== []) {
            $payload['attachments'] = $attachments;
        }

        return $payload;
    }

    /**
     * @param array<int, array{email: string, type: string, name?: string}> $recipients
     * @return array<int, array{email_address: array{address: string, name?: string}}>
     */
    protected function getEmailDetailsByType(array $recipients, string $type): array
    {
        $emails = [];

        foreach ($recipients as $recipient) {
            if ($type !== $recipient['type']) {
                continue;
            }

            $emailDetail = [
                'address' => $recipient['email'],
            ];

            if (isset($recipient['name'])) {
                $emailDetail['name'] = $recipient['name'];
            }

            $emails[] = ['email_address' => $emailDetail];
        }

        return $emails;
    }

    /**
     * @return array<int, array{email: string, type: string, name?: string}>
     */
    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        $cc = array_flip(array_map(
            static fn (Address $address) => $address->getAddress(),
            $email->getCc()
        ));
        $bcc = array_flip(array_map(
            static fn (Address $address) => $address->getAddress(),
            $email->getBcc()
        ));

        $recipients = [];

        foreach ($envelope->getRecipients() as $recipient) {
            $address = $recipient->getAddress();
            $type = 'to';

            if (isset($bcc[$address])) {
                $type = 'bcc';
            } elseif (isset($cc[$address])) {
                $type = 'cc';
            }

            $recipientPayload = [
                'email' => $address,
                'type' => $type,
            ];

            if ($recipient->getName() !== '') {
                $recipientPayload['name'] = $recipient->getName();
            }

            $recipients[] = $recipientPayload;
        }

        return $recipients;
    }

    private function assertSuccessfulResponse(ResponseInterface $response): void
    {
        if ($response->getStatusCode() < 400) {
            return;
        }

        $body = (string) $response->getBody();

        Log::error('ZeptoMail returned an error response.', [
            'status' => $response->getStatusCode(),
            'response' => $body,
        ]);

        throw new TransportException('ZeptoMail returned an error response: ' . $body);
    }

    /**
     * @var array<string, string>
     */
    public array $domainMapping = [
        'zoho.com' => 'zoho.com',
        'zoho.eu' => 'zoho.eu',
        'zoho.in' => 'zoho.in',
        'zoho.com.cn' => 'zoho.com.cn',
        'zoho.com.au' => 'zoho.com.au',
        'zoho.jp' => 'zoho.jp',
        'zohocloud.ca' => 'zohocloud.ca',
        'zoho.sa' => 'zoho.sa',
    ];
}

<?php

namespace App\Tests\Integration\Mail;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[TestDox('Tests of Symfony Mailer integration')]
class MailerTest extends KernelTestCase
{
    private const string SMTP_API_BASE = 'http://host.docker.internal:1080/api/emails';

    private MailerInterface $mailer;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->mailer = static::getContainer()->get('testing.MailerInterface');
        $this->clearEmails();
    }

    protected function tearDown(): void
    {
        $this->clearEmails();
    }

    #[TestDox('Can send basic email via Symfony Mailer')]
    public function testCanSendBasicEmail(): void
    {
        $email = new Email()
            ->from('test@example.com')
            ->to('recipient@example.com')
            ->subject('Test Email from Symfony')
            ->text('This is a test email sent via Symfony Mailer.');

        $this->mailer->send($email);

        $capturedEmails = $this->getEmails();
        $this->assertCount(1, $capturedEmails, 'Should have captured exactly one email');

        $capturedEmail = $capturedEmails[0];
        $this->assertEquals('Test Email from Symfony', $capturedEmail['subject']);
        $this->assertEquals('test@example.com', $capturedEmail['from']['value'][0]['address']);
        $this->assertEquals('recipient@example.com', $capturedEmail['to']['value'][0]['address']);
        $this->assertStringContainsString('This is a test email sent via Symfony Mailer', $capturedEmail['text']);
    }

    #[TestDox('Can send HTML email')]
    public function _testCanSendHtmlEmail(): void
    {
        $email = new Email()
            ->from('test@example.com')
            ->to('recipient@example.com')
            ->subject('HTML Test Email')
            ->html('<h1>Test Email</h1><p>This is an <strong>HTML</strong> email.</p>');

        $this->mailer->send($email);

        $capturedEmails = $this->getEmails();
        $this->assertCount(1, $capturedEmails, 'Should have captured exactly one email');

        $capturedEmail = $capturedEmails[0];
        $this->assertEquals('HTML Test Email', $capturedEmail['subject']);

        $this->assertStringContainsString('<h1>Test Email</h1>', $capturedEmail['html']);
        $this->assertStringContainsString('<strong>HTML</strong>', $capturedEmail['html']);

        $contentTypeHeader = array_filter($capturedEmail['headerLines'], fn($header) => $header['key'] === 'content-type');
        $this->assertNotEmpty($contentTypeHeader, 'Should have Content-Type header');
        $contentType = reset($contentTypeHeader)['line'];
        $this->assertStringContainsString('text/html', $contentType);
    }

    #[TestDox('Can send email with multiple recipients')]
    public function _testCanSendEmailWithMultipleRecipients(): void
    {
        $email = new Email()
            ->from('test@example.com')
            ->to('recipient1@example.com', 'recipient2@example.com')
            ->cc('cc@example.com')
            ->subject('Multi-recipient Test')
            ->text('This email has multiple recipients.');

        $this->mailer->send($email);

        $capturedEmails = $this->getEmails();
        $this->assertCount(1, $capturedEmails, 'Should have captured exactly one email');

        $capturedEmail = $capturedEmails[0];
        $this->assertEquals('Multi-recipient Test', $capturedEmail['subject']);

        // Check recipients
        $toAddresses = array_column($capturedEmail['to']['value'], 'address');
        $this->assertContains('recipient1@example.com', $toAddresses);
        $this->assertContains('recipient2@example.com', $toAddresses);
    }

    private function getEmails(): array
    {
        $response = file_get_contents(self::SMTP_API_BASE);
        $this->assertNotFalse($response, 'Failed to fetch emails from SMTP API');

        $emails = json_decode($response, true);
        $this->assertIsArray($emails, 'SMTP API should return valid JSON array');

        return $emails;
    }

    private function clearEmails(): void
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
                'timeout' => 5
            ]
        ]);

        $response = @file_get_contents(self::SMTP_API_BASE, false, $context);
        $this->assertNotFalse($response, 'Failed to clear emails via SMTP API');
    }
}

<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use RuntimeException;

#[Route('/test-error')]
class TestErrorController extends AbstractController
{
    #[Route('/exception', name: 'test_error_exception')]
    public function testException(): Response
    {
        throw new RuntimeException('This is a deliberate test exception to verify error email notifications work properly.');
    }

    #[Route('/critical', name: 'test_error_critical')]
    public function testCritical(LoggerInterface $logger): Response
    {
        $logger->critical('This is a test critical error to trigger email notifications', [
            'test_data' => 'critical error context',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return new Response('Critical error logged (should trigger email)');
    }

    #[Route('/error', name: 'test_error_error')]
    public function testError(LoggerInterface $logger): Response
    {
        $logger->error('This is a test error that should only go to log files', [
            'test_data' => 'error context',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return new Response('Error logged (should NOT trigger email)');
    }
}

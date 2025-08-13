<?php

namespace App\MessageHandler;

use App\Entity\DynamicTaskMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\ScheduleFormatDetector;
use App\Service\ScheduleTimezoneConverter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendEmailsTaskHandler extends AbstractTaskHandler
{
    public function __construct(
        LoggerInterface $tasksLogger,
        EntityManagerInterface $entityManager,
        ScheduleFormatDetector $scheduleFormatDetector,
        ScheduleTimezoneConverter $scheduleTimezoneConverter,
        private readonly MailerInterface $mailer
    ) {
        parent::__construct($tasksLogger, $entityManager, $scheduleFormatDetector, $scheduleTimezoneConverter);
    }

    protected function handle(DynamicTaskMessage $task): string
    {
        $email = new Email()
            ->from($_ENV['EMAIL_NO_REPLY'])
            ->to($_ENV['EMAIL_DEV_TEAM'])
            ->subject('Scheduled task execution: ' . $task->getName())
            ->text("The scheduled task '{$task->getName()}' executed successfully");

        $this->mailer->send($email);

        return 'Email sent successfully';
    }
}

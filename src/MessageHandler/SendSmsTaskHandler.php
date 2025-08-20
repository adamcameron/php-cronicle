<?php

namespace App\MessageHandler;

use App\Entity\DynamicTaskMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\ScheduleFormatDetector;
use App\Service\ScheduleTimezoneConverter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;

class SendSmsTaskHandler extends AbstractTaskHandler
{
    public function __construct(
        LoggerInterface $tasksLogger,
        EntityManagerInterface $entityManager,
        ScheduleFormatDetector $scheduleFormatDetector,
        ScheduleTimezoneConverter $scheduleTimezoneConverter,
        MessageBusInterface $bus,
        private readonly MailerInterface $mailer
    ) {
        parent::__construct($tasksLogger, $entityManager, $scheduleFormatDetector, $scheduleTimezoneConverter, $bus);
    }

    protected function handle(DynamicTaskMessage $task): string
    {
        throw new \Exception("oooopsy");
        $email = new TemplatedEmail()
            ->from($_ENV['EMAIL_NO_REPLY'])
            ->to($_ENV['EMAIL_DEV_TEAM'])
            ->subject('SMS Task Completed: ' . $task->getName())
            ->htmlTemplate('email/sms_task_notification.html.twig')
            ->context([
                'task' => $task,
                'executed_at' => new DateTimeImmutable()
            ]);

        $this->mailer->send($email);

        return 'Templated email sent successfully';
    }
}

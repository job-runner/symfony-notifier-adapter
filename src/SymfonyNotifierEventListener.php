<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyNotifier;

use JobRunner\JobRunner\Event\JobEvent;
use JobRunner\JobRunner\Event\JobFailEvent;
use JobRunner\JobRunner\Event\JobSuccessEvent;
use JobRunner\JobRunner\Job\Job;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

use function count;
use function sprintf;

final class SymfonyNotifierEventListener implements JobEvent, JobSuccessEvent, JobFailEvent
{
    public const NOTIFICATION_SUBJECT_FORMAT = 'job %s : %s';
    public const NOTIFICATION_MESSAGE_FORMAT = '%s';

    /** @var array<array-key, RecipientInterface> */
    private array $recipients;

    /**
     * @param list<string> $notificationChannelSuccess
     * @param list<string> $notificationChannelFail
     */
    public function __construct(
        private readonly NotifierInterface $notifier,
        private readonly array $notificationChannelSuccess = [],
        private readonly array $notificationChannelFail = [],
        RecipientInterface ...$recipients,
    ) {
        $this->recipients = $recipients;
    }

    /** @param list<string> $notificationChannelSuccess */
    public function withNotificationChannelSuccess(array $notificationChannelSuccess): self
    {
        return new self(
            $this->notifier,
            $notificationChannelSuccess,
            $this->notificationChannelFail,
            ...$this->recipients,
        );
    }

    /** @param list<string> $notificationChannelFail */
    public function withNotificationChannelFail(array $notificationChannelFail): self
    {
        return new self(
            $this->notifier,
            $this->notificationChannelSuccess,
            $notificationChannelFail,
            ...$this->recipients,
        );
    }

    public function withRecipient(RecipientInterface ...$recipients): self
    {
        return new self(
            $this->notifier,
            $this->notificationChannelSuccess,
            $this->notificationChannelFail,
            ...$recipients,
        );
    }

    public function fail(Job $job, string $output): void
    {
        $this->doNotify($job, $output, $this->notificationChannelFail);
    }

    public function success(Job $job, string $output): void
    {
        $this->doNotify($job, $output, $this->notificationChannelSuccess);
    }

    /** @param list<string> $channel */
    private function doNotify(Job $job, string $output, array $channel): void
    {
        if (count($channel) === 0) {
            return;
        }

        $notification = new Notification(sprintf(self::NOTIFICATION_SUBJECT_FORMAT, $job->getName(), $output), $channel);
        $notification->content(sprintf(self::NOTIFICATION_MESSAGE_FORMAT, $output));
        $this->notifier->send($notification, ...$this->recipients);
    }
}

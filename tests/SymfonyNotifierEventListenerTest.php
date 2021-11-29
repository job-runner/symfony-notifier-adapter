<?php

declare(strict_types=1);

namespace JobRunner\JobRunner\SymfonyNotifier\Tests\Unit\EventListener;

use JobRunner\JobRunner\Job\Job;
use JobRunner\JobRunner\SymfonyNotifier\SymfonyNotifierEventListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @covers \JobRunner\JobRunner\SymfonyNotifier\SymfonyNotifierEventListener
 */
class SymfonyNotifierEventListenerTest extends TestCase
{
    public function testSuccess(): void
    {
        $notifier = self::createMock(NotifierInterface::class);
        $job      = self::createMock(Job::class);

        $notifier->expects($this->once())->method('send');

        $sUT = new SymfonyNotifierEventListener($notifier, ['chat']);

        $sUT->success($job, 'toto');
    }

    public function testWithoutNotificationChannel(): void
    {
        $notifier = self::createMock(NotifierInterface::class);
        $job      = self::createMock(Job::class);

        $notifier->expects($this->never())->method('send');

        $sUT = new SymfonyNotifierEventListener($notifier);

        $sUT->success($job, 'toto');
        $sUT->fail($job, 'toto');
    }

    public function testFail(): void
    {
        $notifier = self::createMock(NotifierInterface::class);
        $job      = self::createMock(Job::class);

        $job->expects($this->any())->method('getName')->willReturn('hello');
        $notifier->expects($this->once())->method('send')->with($this->callback(static function (Notification $param) {
            self::assertSame('titit', $param->getContent());
            self::assertSame('job hello : titit', $param->getSubject());

            return true;
        }));

        $sUT = new SymfonyNotifierEventListener($notifier, [], ['chat']);

        $sUT->fail($job, 'titit');
    }
}

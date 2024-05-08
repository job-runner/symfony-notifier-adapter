# symfony/notifier for JobRunner #

[![Build Status](https://github.com/job-runner/symfony-notifier-adapter/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/job-runner/symfony-notifier-adapter/actions/workflows/continuous-integration.yml)
[![Type Coverage](https://shepherd.dev/github/job-runner/symfony-notifier-adapter/coverage.svg)](https://shepherd.dev/github/job-runner/symfony-notifier-adapter)
[![Type Coverage](https://shepherd.dev/github/job-runner/symfony-notifier-adapter/level.svg)](https://shepherd.dev/github/job-runner/symfony-notifier-adapter)
[![Latest Stable Version](https://poser.pugx.org/job-runner/symfony-notifier-adapter/v/stable)](https://packagist.org/packages/job-runner/symfony-notifier-adapter)
[![License](https://poser.pugx.org/job-runner/symfony-notifier-adapter/license)](https://packagist.org/packages/job-runner/symfony-notifier-adapter)

This package provides a symfony/notifier adapter for JobRunner.

## Installation

```bash
composer require job-runner/symfony-notifier-adapter
```

## Usage

````php
<?php

declare(strict_types=1);

use JobRunner\JobRunner\Job\CliJob;
use JobRunner\JobRunner\Job\JobList;
use JobRunner\JobRunner\CronJobRunner;
use Symfony\Component\Notifier\Bridge\RocketChat\RocketChatTransport;
use Symfony\Component\Notifier\Channel\ChatChannel;
use Symfony\Component\Notifier\Notifier;
use JobRunner\JobRunner\SymfonyNotifier\SymfonyNotifierEventListener;

require 'vendor/autoload.php';


$rocket = new RocketChatTransport('mytoken', '#mychannel');
$rocket->setHost('chat.myhost.com');
$chat     = new ChatChannel($rocket);
$notifier = new Notifier(['chat' => $chat]);

$jobCollection = new JobList();
$jobCollection->push(new CliJob('php ' . __DIR__ . '/tutu.php', '* * * * *'));

CronJobRunner::create()
    ->withEventListener((new SymfonyNotifierEventListener($notifier))->withNotificationChannelFail(['chat']))
    ->run($jobCollection);

````

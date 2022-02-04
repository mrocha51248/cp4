<?php

namespace App\Tests\Service;

use App\Service\DateIntervalConverter;
use DateInterval;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DateIntervalConverterTest extends KernelTestCase
{
    public function testMillisecondsTotal(): void
    {
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $dateInterval = DateInterval::createFromDateString('150 seconds');

        /** @var DateIntervalConverter $converter */
        $converter = static::getContainer()->get(DateIntervalConverter::class);
        $this->assertSame(150000, $converter->getMillisecondsTotal($dateInterval));
    }
}

<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Sylius\InvoicingPlugin\Generator;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceSequenceInterface;
use Sylius\InvoicingPlugin\Generator\InvoiceNumberGenerator;
use Symfony\Component\Clock\ClockInterface;

final class SequentialInvoiceNumberGeneratorSpec extends ObjectBehavior
{
    function let(
        RepositoryInterface $sequenceRepository,
        FactoryInterface $sequenceFactory,
        EntityManagerInterface $sequenceManager,
        ClockInterface $clock,
    ): void {
        $this->beConstructedWith(
            $sequenceRepository,
            $sequenceFactory,
            $sequenceManager,
            $clock,
            1,
            9,
        );
    }

    function it_implements_invoice_number_generator_interface(): void
    {
        $this->shouldImplement(InvoiceNumberGenerator::class);
    }

    function it_generates_invoice_number(
        RepositoryInterface $sequenceRepository,
        EntityManagerInterface $sequenceManager,
        ClockInterface $clock,
        InvoiceSequenceInterface $sequence,
    ): void {
        $dateTime = new \DateTimeImmutable('now');
        $clock->now()->willReturn($dateTime);

        $sequenceRepository->findOneBy([])->willReturn($sequence);

        $sequence->getVersion()->willReturn(1);
        $sequence->getIndex()->willReturn(0);

        $sequenceManager->lock($sequence, LockMode::OPTIMISTIC, 1)->shouldBeCalled();

        $sequence->incrementIndex()->shouldBeCalled();

        $this->generate()->shouldReturn($dateTime->format('Y/m') . '/000000001');
    }

    function it_generates_invoice_number_when_sequence_is_null(
        RepositoryInterface $sequenceRepository,
        FactoryInterface $sequenceFactory,
        EntityManagerInterface $sequenceManager,
        ClockInterface $clock,
        InvoiceSequenceInterface $sequence,
    ): void {
        $dateTime = new \DateTimeImmutable('now');
        $clock->now()->willReturn($dateTime);

        $sequenceRepository->findOneBy([])->willReturn(null);

        $sequenceFactory->createNew()->willReturn($sequence);

        $sequenceManager->persist($sequence)->shouldBeCalled();

        $sequence->getVersion()->willReturn(1);
        $sequence->getIndex()->willReturn(0);

        $sequenceManager->lock($sequence, LockMode::OPTIMISTIC, 1)->shouldBeCalled();

        $sequence->incrementIndex()->shouldBeCalled();

        $this->generate()->shouldReturn($dateTime->format('Y/m') . '/000000001');
    }
}

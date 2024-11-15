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

namespace Sylius\InvoicingPlugin\Security\Voter;

use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Customer\Model\CustomerAwareInterface;
use Sylius\Component\User\Model\UserInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Webmozart\Assert\Assert;

final class InvoiceVoter extends Voter
{
    public const ACCESS = 'access';

    private const ATTRIBUTES = [self::ACCESS];

    public function __construct(private readonly OrderRepositoryInterface $orderRepository)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, self::ATTRIBUTES, true)) {
            return false;
        }

        if (!$subject instanceof InvoiceInterface) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        Assert::isInstanceOf($subject, InvoiceInterface::class);

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::ACCESS => $this->canAccess($user, $subject),
            default => throw new \LogicException(sprintf('Unknown attribute "%s" passed.', $attribute)),
        };
    }

    private function canAccess(UserInterface $user, InvoiceInterface $invoice): bool
    {
        if ($user instanceof AdminUserInterface) {
            return true;
        }

        if ($user instanceof CustomerAwareInterface) {
            $customer = $user->getCustomer();

            Assert::isInstanceOf($customer, CustomerInterface::class);

            return null !== $this->orderRepository->findOneByNumberAndCustomer($invoice->order()->getNumber(), $customer);
        }

        return false;
    }
}

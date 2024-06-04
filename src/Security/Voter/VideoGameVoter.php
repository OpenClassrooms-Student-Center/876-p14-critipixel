<?php

namespace App\Security\Voter;

use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class VideoGameVoter extends Voter
{
    public const REVIEW = 'review';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::REVIEW && $subject instanceof VideoGame;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var VideoGame $subject */
        return !$subject->hasAlreadyReview($user);
    }
}

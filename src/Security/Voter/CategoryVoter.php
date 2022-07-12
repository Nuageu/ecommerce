<?php

namespace App\Security\Voter;

use App\Repository\CategoryRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryVoter extends Voter
{
    // protected $categoryRepository;

    // public function __construct(CategoryRepository $categoryRepository)
    // {
    //     $this->categoryRepository = $categoryRepository;
    // }
    public const EDIT = 'CAN_EDIT';
    // public const VIEW = 'POST_VIEW';

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT])
            // && is_numeric($subject);
            && $subject instanceof \App\Entity\Category;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        // dd("je travaille avec annot is granted");
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // $category = $this->categoryRepository->find($subject);

        // if (!$category) {
        //     return false;
        // }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                // logic to determine if the user can EDIT
                // return true or false
                // return $category->getOwner() === $user;
                return $subject->getOwner() === $user;
                // case self::VIEW:
                //     // logic to determine if the user can VIEW
                //     // return true or false
                //     break;
        }

        return false;
    }
}

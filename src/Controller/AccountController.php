<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class AccountController extends AbstractController
{
    public function profile(): Response
    {
        return $this->render(
            view: '@CoddinIdentityProvider/account/profile.html.twig',
            parameters: [
                'companyName' => $this->getParameter('idp.company_name'),
            ],
        );
    }
}

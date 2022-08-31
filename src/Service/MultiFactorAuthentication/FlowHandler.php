<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Service\MultiFactorAuthentication;

use Coddin\IdentityProvider\Entity\OpenIDConnect\Enum\MfaType;
use Coddin\IdentityProvider\Entity\OpenIDConnect\User;
use Coddin\IdentityProvider\Entity\OpenIDConnect\UserMfaMethod;
use Coddin\IdentityProvider\Repository\UserMfaMethodRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

final class FlowHandler
{
    public function __construct(
        private readonly UserMfaMethodRepository $userMfaMethodRepository,
        private readonly MethodHandlerDeterminator $methodHandlerDeterminator,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function handleActiveMfa(
        Request $request,
        Security $security,
    ): void {
        $user = $security->getUser();
        if (!$user instanceof User) {
            throw new \LogicException('Incorrect User type provided');
        }

        $userMfaMethod = $this->userMfaMethodRepository->getActiveMfaMethodForUser($user);

        switch ($userMfaMethod->getMfaMethod()->getType()) {
            case MfaType::TYPE_TOTP->value:
                $this->handleTotp($request, $userMfaMethod);
                break;
            case MfaType::TYPE_U2F->value:
                $this->handleU2f($request, $userMfaMethod);
                break;
            default:
                throw new \Exception(
                    sprintf(
                        'Unsupported MFA type `%s`',
                        $userMfaMethod->getMfaMethod()->getType(),
                    ),
                );
        }
    }

    /**
     * @throws \Exception
     */
    private function handleTotp(
        Request $request,
        UserMfaMethod $userMfaMethod,
    ): void {
        // Todo: Less "skeer" validation.
        if (!$request->request->has('otp')) {
            throw new \Exception('The request is missing the required `otp` value');
        }

        $submittedOtp = $request->request->get('otp');

        $mfaMethodHandler = $this->methodHandlerDeterminator->execute($userMfaMethod);

        // TODO: Not use an unstructured array.
        $verificationData = [];
        // TODO: Not use string as key.
        $verificationData['otp'] = $submittedOtp;

        if ($mfaMethodHandler->verifyAuthentication($verificationData) === false) {
            throw new \Exception('shit went wrong yo');
        }
    }

    private function handleU2f(
        Request $request,
        UserMfaMethod $userMfaMethod,
    ): void {
        throw new \LogicException('This type of MFA is not implemented yet');
    }
}

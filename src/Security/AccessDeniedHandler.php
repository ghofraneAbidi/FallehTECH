<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function handle(Request $request, \Symfony\Component\Security\Core\Exception\AccessDeniedException $accessDeniedException): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_access_denied'));
    }
}

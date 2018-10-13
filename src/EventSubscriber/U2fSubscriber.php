<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

class U2fSubscriber implements EventSubscriberInterface
{
    const U2F_SECURITY_KEY = 'u2f_must_validate';

    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof User && $user->getU2fKeys()->count()) {
            $event->getRequest()->getSession()->set(static::U2F_SECURITY_KEY, true);
        }
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (
            $event->isMasterRequest() &&
            $event->getRequest()->getSession()->get(static::U2F_SECURITY_KEY)
        ) {
            $route = $event->getRequest()->get('_route');
            $whitelist = [
                'user_authenticate_u2f',
                'login',
                'logout',
                '_wdt',
                '_profiler_home',
                '_profiler_search',
                '_profiler_search_bar',
                '_profiler_phpinfo',
                '_profiler_search_results',
                '_profiler_open_file',
                '_profiler',
                '_profiler_router',
                '_profiler_exception',
                '_profiler_exception_css'
            ];
            if (!in_array($route, $whitelist)) {
                $event->setResponse(new RedirectResponse($this->router->generate('user_authenticate_u2f')));
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}

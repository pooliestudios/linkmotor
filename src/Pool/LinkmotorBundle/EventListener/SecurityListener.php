<?php

namespace Pool\LinkmotorBundle\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityListener
{
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $locale = $event->getAuthenticationToken()->getUser()->getLocale();
        $event->getRequest()->setLocale($locale);

        $session = $event->getRequest()->getSession();
        if ($session->has('_security.secured_area.target_path')) {
            // @todo Das ist fehleranfällig
            $targetPath = $session->get('_security.secured_area.target_path');
            $localizedTargetPath = str_replace('/de/', "/{$locale}/", $targetPath);
            $localizedTargetPath = str_replace('/en/', "/{$locale}/", $localizedTargetPath);
            $session->set('_security.secured_area.target_path', $localizedTargetPath);
        }
        $session->set('_locale', $locale);
    }
}

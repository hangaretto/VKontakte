<?php

namespace Magnetar\EnDo_OAuth;

use SocialiteProviders\Manager\SocialiteWasCalled;

class EnDoExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'endo', __NAMESPACE__.'\Provider'
        );
    }
}

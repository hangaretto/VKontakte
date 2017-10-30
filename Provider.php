<?php

namespace Magnetar\EnDo_OAuth;

use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\User;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\InvalidStateException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider implements ProviderInterface
{
    protected $fields = ['first_name', 'last_name'];

    /**
     * API URL.
     */
//    private $api_url = 'https://api.app.endo.im/api/v1'; // TODO:
    private $api_url = 'http://btchain.gofocus.ru/api/v1';

    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'ENDO';

    /**
     * {@inheritdoc}
     */
    protected $scopes = ['user'];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            $this->api_url.'/oauth', $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->api_url.'/oauth/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $lang = $this->getConfig('lang');

        $response = $this->getHttpClient()->get(
            $this->api_url.'/oauth/user?',
            [
                'query' => [
                    'access_token' => $token['access_token'],
                ],
            ]
        );

        $response = json_decode($response->getBody()->getContents(), true)['user'];

        return array_merge($response, [
            'email' => Arr::get($token, 'email'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => Arr::get($user, 'id'), 'first_name' => trim(Arr::get($user, 'first_name')),
            'last_name' => trim(Arr::get($user, 'last_name')), 'email' => Arr::get($user, 'email'),
            'nickname' => trim(Arr::get($user, 'first_name').' '.Arr::get($user, 'first_name')),
            'avatar' => Arr::get($user, 'avatar'), 'name' => trim(Arr::get($user, 'first_name'))
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'scope' => 'user',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessToken($body)
    {
        return json_decode($body, true);
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException();
        }

        $user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->getAccessTokenResponse($this->getCode())
        ));

        return $user->setToken(Arr::get($token, 'access_token'))
                    ->setExpiresIn(Arr::get($token, 'expires_in'));
    }

    /**
     * Set the user fields to request from Vkontakte.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function additionalConfigKeys()
    {
        return ['lang'];
    }
}

<?php

class OAuth2Server
{

    private static $instance;
    private $server;
    private $storage;

    private function __construct()
    {
        $this->storage = $this->configureStorage();

        $this->server = new \OAuth2\Server($this->storage, [
            'allow_implicit' => true,
            'enforce_state' => true
        ]);
        $this->server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($this->storage));
        $this->server->addGrantType(new \OAuth2\GrantType\ClientCredentials($this->storage));
    }

    private function configureStorage()
    {

        global $globals;

        $dsn = "mysql:host={$globals['db_server']};dbname={$globals['db_name']}";

        $storage = new Storage([
            'dsn' => $dsn,
            'username' => $globals['db_user'],
            'password' => $globals['db_password'],
            'options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ]
        ]);

        return $storage;
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function manageAuthorization()
    {

        global $current_user;

        $request = OAuth2\Request::createFromGlobals();
        $response = new OAuth2\Response();

        if (!$this->getServer()->validateAuthorizeRequest($request, $response)) {
            $response->send();
            exit;
        }

        if (0 === $current_user->user_id) {
            $return_url = urlencode($_SERVER['REQUEST_URI']);
            header('Location: /login?return=' . $return_url);
            exit;
        }

        $client_id = $request->query('client_id', $request->request('client_id'));
        $scope = $request->query('scope', $request->request('scope'));

        $is_authorized = $this->getStorage()->alreadyAcceptedAuthorizationForClient($client_id, $current_user->user_id);

        if (!$is_authorized and empty($_POST)) {
            Haanga::Load('oauth2/authorize.html', compact('scope'));
            exit;
        }

        $is_authorized = $is_authorized || ('yes' === $_POST['authorized']);
        $this->server->handleAuthorizeRequest($request, $response, $is_authorized, $current_user->user_id);
        $response->send();

    }

    public function getServer()
    {
        return $this->server;
    }

    public function getStorage()
    {
        return $this->storage;
    }

    public function manageTokenRequest()
    {
        $this->getServer()->handleTokenRequest(OAuth2\Request::createFromGlobals())->send();
    }

    public function checkAccess()
    {
        if (!$this->getServer()->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
            $this->getServer()->getResponse()->setParameters([
                'code' => $this->getServer()->getResponse()->getStatusCode(),
                'message' => $this->getServer()->getResponse()->getStatusText()
            ]);
            $this->getServer()->getResponse()->send();
            die;
        }
    }
}
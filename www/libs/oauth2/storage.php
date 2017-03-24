<?php

class Storage extends \OAuth2\Storage\Pdo
{
    public function __construct($connection, $config = array())
    {
        parent::__construct($connection, $config = array());
    }

    public function alreadyAcceptedAuthorizationForClient($client_id, $user_id)
    {
        $stmt = $this->db->prepare(sprintf('SELECT * FROM %s WHERE client_id = :client_id AND user_id = :user_id LIMIT 1', $this->config['access_token_table']));
        $stmt->execute(compact('client_id', 'user_id'));
        return $stmt->fetch() ? true : false;
    }

}
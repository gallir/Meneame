<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Mafia
{
    public $link;

    protected $url;
    protected $valid;
    protected $error;

    protected $current = [];
    protected $previous = [];
    protected $next = [];

    public function __construct($url)
    {
        $this->url = $url;

        $this->validate();
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getLink()
    {
        return $this->link;
    }

    protected function validate()
    {
        global $globals;

        if (empty($this->url)) {
            return $this->setError('No se ha indicado una URL');
        }

        if (parse_url($this->url, PHP_URL_HOST) !== $globals['server_name']) {
            return $this->setError(sprintf('Sólo se permiten links de %s', $globals['server_name']));
        }

        $path = array_values(array_filter(explode('/', parse_url($this->url, PHP_URL_PATH))));

        if ($path[0] !== 'story') {
            return $this->setError('La URL no parece correcta');
        }

        $this->link = Link::from_db($path[1], 'uri');

        if (empty($this->link)) {
            return $this->setError('Envío no encontrado');
        }

        $this->valid = true;

        $this->getCurrent();
        $this->getPrevious();
        $this->getNext();

        $this->filterCommonUsers();
    }

    public function getCurrent()
    {
        if ($this->valid !== true) {
            return [];
        }

        if ($this->current) {
            return $this->current;
        }

        $this->current['link'] = $this->link;
        $this->current['users'] = $this->getUsers($this->link->id);

        return $this->current;
    }

    public function getPrevious()
    {
        if ($this->valid !== true) {
            return [];
        }

        if ($this->previous) {
            return $this->previous;
        }

        global $db, $globals;

        $this->previous['link'] = null;
        $this->previous['users'] = [];

        $link = $db->get_var('
            SELECT `link_id`
            FROM `links`
            WHERE (
                `link_id` < "'.$this->link->id.'"
                AND `link_url` LIKE "%'.$this->getDomain($this->link->url).'/%"
                AND `link_status` = "published"
            )
            ORDER BY `link_id` DESC
            LIMIT 1;
        ');

        if (empty($link)) {
            return $this->previous;
        }

        $link = Link::from_db($link);

        $this->previous['link'] = $link;
        $this->previous['users'] = $this->getUsers($link->id);

        return $this->previous;
    }

    public function getNext()
    {
        if ($this->valid !== true) {
            return [];
        }

        if ($this->next) {
            return $this->next;
        }

        global $db, $globals;

        $this->next['link'] = null;
        $this->next['users'] = [];

        $link = $db->get_var('
            SELECT `link_id`
            FROM `links`
            WHERE (
                `link_id` > "'.$this->link->id.'"
                AND `link_url` LIKE "%'.$this->getDomain($this->link->url).'/%"
                AND `link_status` = "published"
            )
            ORDER BY `link_id` ASC
            LIMIT 1;
        ');

        if (empty($link)) {
            return $this->next;
        }

        $link = Link::from_db($link);

        $this->next['link'] = $link;
        $this->next['users'] = $this->getUsers($link->id);

        return $this->next;
    }

    protected function filterCommonUsers()
    {
        $map = function ($value) {
            return $value->user_id;
        };

        $ids = array_values(array_filter(array_intersect(
            array_map($map, $this->current['users']),
            array_map($map, $this->previous['users']),
            array_map($map, $this->next['users'])
        )));

        $filter = function ($value) use ($ids) {
            return in_array($value->user_id, $ids);
        };

        $this->current['users'] = array_filter($this->current['users'], $filter);
        $this->previous['users'] = array_filter($this->previous['users'], $filter);
        $this->next['users'] = array_filter($this->next['users'], $filter);

    }

    protected function setError($error)
    {
        $this->error = $error;

        return $this->valid = false;
    }

    protected function getDomain($url)
    {
        return implode('.', array_slice(explode('.', parse_url($url, PHP_URL_HOST)), -2));
    }

    protected function getUsers($link_id)
    {
        global $db, $globals;

        $users = $db->get_results('
            SELECT `users`.`user_id`, `users`.`user_login`, `votes`.`vote_date`, INET_NTOA(`votes`.`vote_ip_int`) `vote_ip`
            FROM `users`, `votes`
            WHERE (
                `votes`.`vote_type` = "links"
                AND `votes`.`vote_link_id` = "'.$link_id.'"
                AND `users`.`user_id` = `votes`.`vote_user_id`
            )
            ORDER BY `votes`.`vote_date` ASC;
        ');

        foreach ($users as $user) {
            $user->user_link = $globals['base_url_general'].'user/'.htmlspecialchars($user->user_login).'/'.$user->user_id;
            $user->vote_ip = preg_replace('/[0-9]+$/', 'XXX', $user->vote_ip);
        }

        return $users;
    }
}

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
    protected $published;

    protected $current = [];
    protected $previous = [];
    protected $next = [];

    public function __construct($url, $published = false)
    {
        $this->url = $url;
        $this->published = (bool)$published;

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

        $this->previous['link'] = $this->getRelatedLink($this->link->id, $this->link->url, '<');

        if ($this->previous['link']) {
            $this->previous['users'] = $this->getUsers($this->previous['link']->id);
        } else {
            $this->previous['users'] = [];
        }

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

        $this->next['link'] = $this->getRelatedLink($this->link->id, $this->link->url, '>');

        if ($this->next['link']) {
            $this->next['users'] = $this->getUsers($this->next['link']->id);
        } else {
            $this->next['users'] = [];
        }

        return $this->next;
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

    protected function filterCommonUsers()
    {
        $map = function ($value) {
            return $value->user_id;
        };

        $ids = call_user_func_array('array_intersect', array_filter([
            array_map($map, $this->current['users']),
            array_map($map, $this->previous['users']),
            array_map($map, $this->next['users'])
        ]));

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

    protected function getRelatedLink($id, $url, $relation)
    {
        global $db;

        $link = $db->get_var('
            SELECT `link_id`
            FROM `links`
            WHERE (
                `link_id` '.$relation.' "'.$id.'"
                AND `link_url` LIKE "%'.$this->getDomain($url).'/%"
                '.($this->published ? 'AND `link_status` = "published"' : '').'
            )
            ORDER BY `link_id` '.(($relation === '>') ? 'ASC' : 'DESC').'
            LIMIT 1;
        ');

        return $link ? Link::from_db($link) : null;
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

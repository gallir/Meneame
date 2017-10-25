<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Mafia
{
    protected $valid;
    protected $error;
    protected $published;

    protected $ids = [];
    protected $links = [];
    protected $users = [];

    public function __construct($uri, $published, $ids)
    {
        if (!$this->validate($uri)) {
            return $this;
        }

        $this->published = $published;
        $this->links = $this->loadRelatedLinks($this->link);

        if (empty($ids)) {
            return;
        }

        array_map(function ($value) use ($ids) {
            $value->selected = in_array($value->link_id, $ids);
        }, $this->links);
    }

    public function isValid()
    {
        return $this->valid;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function getUsers()
    {
        if ($this->valid !== true) {
            return [];
        }

        if ($this->users) {
            return $this->users;
        }

        $ids = array_filter(array_map(function ($value) {
            return $value->selected ? $value->link_id : null;
        }, $this->links));

        return $this->getUsersByLinks($ids);
    }

    protected function validate($uri)
    {
        global $globals;

        if (empty($uri)) {
            return $this->setError('No se ha indicado una URL');
        }

        if (strstr($uri, '/')) {
            if (!preg_match('#/story/([^/]+)#', $uri, $uri)) {
                return $this->setError('La URL indicada no es correcta');
            }

            $uri = $uri[1];
        }

        $this->link = $this->getLinkByUri($uri);

        if (empty($this->link)) {
            return $this->setError('Envío no encontrado');
        }

        return $this->valid = true;
    }

    protected function getLinkByUri($uri)
    {
        global $db;

        return $db->get_row('
            SELECT *, false AS `selected`
            FROM `links`
            WHERE `link_uri` = "'.$db->escape($uri).'"
            LIMIT 1;
        ');
    }

    protected function loadRelatedLinks($link)
    {
        global $db;

        $domain = $this->getDomain($link->link_url);

        return array_merge(
            [$link],

            $db->get_results('
                SELECT *, false AS `selected`
                FROM `links`
                WHERE (
                    `link_id` < "'.(int)$link->link_id.'"
                    AND `link_url` LIKE "%'.$domain.'/%"
                    '.($this->published ? 'AND link_status = "published"' : '').'
                )
                ORDER BY `link_id` DESC
                LIMIT 5;
            '),

            $db->get_results('
                SELECT *, false AS `selected`
                FROM `links`
                WHERE (
                    `link_id` > "'.(int)$link->link_id.'"
                    AND `link_url` LIKE "%'.$domain.'/%"
                    '.($this->published ? 'AND link_status = "published"' : '').'
                )
                ORDER BY `link_id` ASC
                LIMIT 5;
            ')
        );
    }

    protected function setError($error)
    {
        $this->error = $error;

        return $this->valid = false;
    }

    protected function getDomain($uri)
    {
        return implode('.', array_slice(explode('.', parse_url($uri, PHP_URL_HOST)), -2));
    }

    protected function getUsersByLinks(array $link_ids)
    {
        global $db, $globals;

        $users = $db->get_results('
            SELECT `users`.`user_id`, `users`.`user_login`
            FROM `users`, `votes`
            WHERE (
                `votes`.`vote_type` = "links"
                AND `votes`.`vote_link_id` IN ("'.implode('","', $link_ids).'")
                AND `users`.`user_id` = `votes`.`vote_user_id`
                AND `votes`.`vote_value` > 0
            )
            GROUP BY `users`.`user_id`
            HAVING COUNT(`users`.`user_id`) = "'.count($link_ids).'";
        ');

        foreach ($users as $user) {
            $user->user_link = $globals['base_url_general'].'user/'.htmlspecialchars($user->user_login).'/'.$user->user_id;
        }

        usort($users, function ($a, $b) {
            return ($a->user_login > $b->user_login) ? 1 : -1;
        });

        return $users;
    }
}

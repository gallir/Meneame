<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class Poll
{
    public $id;
    public $question = '';
    public $votes = 0;
    public $created_at;
    public $end_at;
    public $link_id;
    public $post_id;

    public $voted;
    public $finished;

    private $options = array();
    private $options_limit = 5;
    private $durations_valid = array(1, 5, 10, 15, 30);

    public function resetOptions()
    {
        $this->options[] = array();
    }

    public function setOptionsFromArray(array $options)
    {
        $this->options = array();

        foreach (DbHelper::stringsUnique($options) as $text) {
            if (empty($text)) {
                continue;
            }

            $option = new PollOption;
            $option->option = $text;

            $this->options[] = $option;
        }
    }

    public function setOption(PollOption $option)
    {
        $option->voted = $this->voted == $option->id;
        $option->percent = (int)round(((int)$option->votes / (int)$this->votes) * 100);

        $this->options[$option->id] = $option;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($id)
    {
        if (isset($this->options[$id])) {
            return $this->options[$id];
        }
    }

    public function reloadOptions()
    {
        foreach ($this->options as $option) {
            $this->setOption($option);
        }
    }

    public function areOptionsValid()
    {
        $count = count($this->options);

        return ($count === 0) || (($count > 1) && ($count <= $this->options_limit));
    }

    public function setDuration($days)
    {
        $days = (int)$days;

        if (in_array($days, $this->durations_valid)) {
            $this->end_at = date('Y-m-d H:i:s', strtotime('+'.$days.' days'));
        } else {
            $this->end_at = null;
        }
    }

    public function getTimeToFinish()
    {
        $interval = date_create('now')->diff(date_create($this->end_at));

        if (($interval->d >= 3) || (($interval->d > 1) && ($interval->h === 0))) {
            return sprintf(_('Faltan %s días'), $interval->d);
        }

        if ($interval->d > 1) {
            return sprintf(_('Falta %s días y %s horas'), $interval->d, $interval->h);
        }

        if (($interval->d === 1) && ($interval->h === 0)) {
            return _('Falta 1 día');
        }

        if ($interval->d === 1) {
            return sprintf(_('Falta 1 día y %s horas'), $interval->h);
        }

        if (($interval->h > 1) && ($interval->i === 0)) {
            return sprintf(_('Faltan %s horas'), $interval->h);
        }

        if ($interval->h > 1) {
            return sprintf(_('Faltan %s horas y %s minutos'), $interval->h, $interval->i);
        }

        if (($interval->h === 1) && ($interval->i === 0)) {
            return _('Falta 1 hora');
        }

        if ($interval->h === 1) {
            return sprintf(_('Falta 1 hora y %s minutos'), $interval->i);
        }

        if ($interval->i > 1) {
            return sprintf(_('Faltan %s minutos'), $interval->i);
        }

        if ($interval->i === 1) {
            return _('Falta 1 minuto');
        }

        return sprintf(_('Faltan %s segundos'), $interval->s);
    }

    public function read()
    {
        if (empty($this->id)) {
            return;
        }

        global $db, $current_user;

        if ($current_user) {
            $query = '
                SELECT SQL_NO_CACHE `p`.*, `v`.`vote_value` AS `voted`,
                    IF (`p`.`end_at` < NOW(), TRUE, FALSE) AS `finished`
                FROM `polls` AS `p`
                LEFT JOIN `votes` AS `v` ON (
                    `v`.`vote_link_id` = `p`.`id`
                    AND `v`.`vote_user_id` = "'.(int)$current_user->user_id.'"
                    AND `v`.`vote_type` = "polls"
                )
                WHERE `p`.`id` = "'.(int)$this->id.'"
                LIMIT 1;
            ';
        } else {
            $query = '
                SELECT SQL_NO_CACHE *, NULL AS `voted`,
                    IF (`end_at` < NOW(), TRUE, FALSE) AS `finished`
                FROM `polls`
                WHERE `p`.`id` = "'.(int)$this->id.'"
                LIMIT 1;
            ';
        }

        if (!($result = $db->get_row(DbHelper::queryPlain($query)))) {
            return;
        }

        foreach (get_object_vars($result) as $key => $value) {
            $this->$key = $value;
        }

        $this->readOptions();

        return true;
    }

    private function readOptions()
    {
        $this->options = array();

        foreach (PollOption::selectFromPollId($this->id) as $option) {
            $this->setOption($option);
        }
    }

    public function store()
    {
        if (empty($this->options)) {
            return;
        }

        $this->question = $this->normalize($this->question);

        if ($this->id) {
            $response = $this->update();
        } else {
            $response = $this->insert();
        }

        if ($response === false) {
            return false;
        }

        $this->storeOptions();

        return true;
    }

    private function storeOptions()
    {
        foreach ($this->options as $option) {
            $option->poll_id = $this->id;
            $option->store();
        }
    }

    private function insert()
    {
        global $db;

        $response = $db->query(DbHelper::queryPlain('
            INSERT INTO `polls`
            SET
                `question` = "'.$this->question.'",
                `end_at` = "'.$this->end_at.'",
                `link_id` = '.($this->link_id ?: 'NULL').',
                `post_id` = '.($this->post_id ?: 'NULL').';
        '));

        if (empty($response)) {
            return false;
        }

        $this->id = $db->insert_id;

        return true;
    }

    private function update()
    {
        global $db;

        $response = $db->query(DbHelper::queryPlain('
            UPDATE `polls`
            SET
                `question` = "'.$this->question.'",
                `end_at` = "'.$this->end_at.'",
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        '));

        return $response ? true : false;
    }

    public function vote(PollOption $option)
    {
        global $current_user, $db;

        if (empty($this->id) || empty($current_user->user_id)) {
            return;
        }

        $vote = new Vote('polls', $this->id, $current_user->user_id);

        if ($vote->exists(false)) {
            return;
        }

        $vote->value = (int)$option->id;

        $db->transaction();

        if (!$vote->insert() || !$option->vote()) {
            syslog(LOG_INFO, 'failed insert poll vote for '.$this->id);

            $db->commit();

            return;
        }

        $query = '
            UPDATE `polls`
            SET `votes` = `votes` + 1
            WHERE `id` = "'.$this->id.'"
            LIMIT 1;
        ';

        if (!$db->query(DbHelper::queryPlain($query))) {
            $db->commit();
            return;
        }

        $this->votes++;
        $this->voted = $option->id;

        $this->reloadOptions();

        $db->commit();

        return true;
    }

    private function normalize($value)
    {
        return clean_lines(clear_whitespace($value));
    }

    public static function selectFromRelatedIds($related, array $related_ids)
    {
        global $db, $current_user;

        if ($current_user) {
            $query = '
                SELECT SQL_NO_CACHE `p`.*, `v`.`vote_value` AS `voted`,
                    IF (`p`.`end_at` < NOW(), TRUE, FALSE) AS `finished`
                FROM `polls` AS `p`
                LEFT JOIN `votes` AS `v` ON (
                    `v`.`vote_link_id` = `p`.`id`
                    AND `v`.`vote_user_id` = "'.(int)$current_user->user_id.'"
                    AND `v`.`vote_type` = "polls"
                )
                WHERE `p`.`'.$related.'` IN ('.DbHelper::implodedIds($related_ids).')
                LIMIT '.count($related_ids).';
            ';
        } else {
            $query = '
                SELECT SQL_NO_CACHE *, NULL AS `voted`,
                    IF (`p`.`end_at` < NOW(), TRUE, FALSE) AS `finished`
                FROM `polls`
                WHERE `'.$related.'` IN ('.DbHelper::implodedIds($related_ids).')
                LIMIT '.count($related_ids).';
            ';
        }

        return $db->object_iterator(DbHelper::queryPlain($query), 'Poll');
    }
}

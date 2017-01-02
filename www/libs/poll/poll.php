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
    public $duration = 0;
    public $votes = 0;
    public $created_at;
    public $end_at;
    public $link_id;
    public $post_id;

    public $voted;
    public $finished;

    private $index = 0;

    private $options = array();
    private $options_limit = 5;
    private $durations_valid = array(6, 12, 24, 48);

    /** SETTERS **/

    public function readFromArray(array $data)
    {
        $question = $this->normalize($data['poll_question']);

        if (empty($question)) {
            return true;
        }

        $this->question = $question;
        $this->setOptionsFromArray($data['poll_options']);

        if (!$this->validateOptions()) {
            throw new Exception(_('Las opciones de la encuesta no son válidas'));
        }

        $duration = (int)$data['poll_duration'];

        if (!$this->validateDuration($duration)) {
            throw new Exception(_('La duración indicada en la encuesta no es válida'));
        }

        $this->setDuration($duration);

        return true;
    }

    public function resetOptions()
    {
        $this->options = array();
    }

    public function setOptionsFromArray(array $options)
    {
        if ($this->options) {
            $ids = array_map(function($value) {
                return (int)$value->id;
            }, $this->options);
        } else {
            $ids = array();
        }

        $this->options = array();

        foreach ($options as $data) {
            if (empty($data['option'])) {
                continue;
            }

            $option = new PollOption;
            $option->id = (int)$data['id'];

            if ($ids && $option->id && !in_array($option->id, $ids)) {
                throw new Exception(_('Las opciones de la encuesta no son válidas'));
            }

            $option->option = $data['option'];

            $this->options[] = $option;
        }
    }

    public function setOption(PollOption $option)
    {
        $option->index = ++$this->index;
        $option->voted = $this->voted == $option->id;
        $option->percent = (int)round(((int)$option->votes / (int)$this->votes) * 100);

        $this->options[$option->id] = $option;
    }

    public function reloadOptions()
    {
        $this->index = 0;

        foreach ($this->options as $option) {
            $this->setOption($option);
        }
    }

    public function setDuration($hours)
    {
        if ($this->duration && ($this->duration == $hours)) {
            return;
        }

        $this->duration = (int)$hours;
        $this->end_at = date('Y-m-d H:i:s', strtotime('+'.$this->duration.' hours'));
    }

    /** GETTERS **/

    public function getOptions()
    {
        return $this->options;
    }

    public function getOptionsWithEmpty()
    {
        $options = $this->options;
        $index = $this->index;

        for ($i = count($options); $i < $this->options_limit; $i++) {
            $option = new PollOption;
            $option->index = ++$index;

            $options[] = $option;
        }

        return $options;
    }

    public function getOption($id)
    {
        if (isset($this->options[$id])) {
            return $this->options[$id];
        }
    }

    public function getDurationsValid()
    {
        return $this->durations_valid;
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

    /** VALIDATORS **/

    public function validateOptions()
    {
        $count = count($this->options);

        if (($count < 2) || ($count > $this->options_limit)) {
            return false;
        }

        $duplicated = array();

        foreach ($this->options as $option) {
            if (in_array($option->option, $duplicated)) {
                return false;
            }

            $duplicated[] = $option->option;
        }

        return true;
    }

    public function validateDuration($hours)
    {
        return in_array($hours, $this->durations_valid);
    }

    public function isStorable()
    {
        return $this->question && $this->options;
    }

    /** READ **/

    public function read($related = null, $related_id = null)
    {
        if (empty($related) || empty($related_id)) {
            $related = 'id';
            $related_id = $this->id;
        }

        if (empty($related_id)) {
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
                WHERE `p`.`'.$related.'` = "'.(int)$related_id.'"
                LIMIT 1;
            ';
        } else {
            $query = '
                SELECT SQL_NO_CACHE *, NULL AS `voted`,
                    IF (`end_at` < NOW(), TRUE, FALSE) AS `finished`
                FROM `polls`
                WHERE `p`.`'.$related.'` = "'.(int)$related_id.'"
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
        $this->index = 0;

        foreach (PollOption::selectFromPollId($this->id) as $option) {
            $this->setOption($option);
        }
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

    /** WRITE **/

    public function store()
    {
        if (!$this->isStorable()) {
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

    public function storeFromArray(array $data)
    {
        $this->readFromArray($data);

        if (empty($this->id) && empty($this->question)) {
            return true;
        }

        if (empty($this->question)) {
            return $this->delete();
        }

        return $this->store();
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
                `duration` = "'.(int)$this->duration.'",
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
                `duration` = "'.(int)$this->duration.'",
                `end_at` = "'.$this->end_at.'"
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        '));

        return $response ? true : false;
    }

    private function delete()
    {
        global $db;

        $response = $db->query(DbHelper::queryPlain('
            DELETE FROM `polls`
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
        return trim(clean_lines(clear_whitespace($value)));
    }
}

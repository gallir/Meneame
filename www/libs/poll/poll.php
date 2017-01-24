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
        $this->setQuestion($data['poll_question']);

        if (empty($this->question)) {
            return true;
        }

        $this->validateQuestion();

        $this->setOptionsFromArray($data['poll_options']);
        $this->validateOptions();

        $duration = (int)$data['poll_duration'];

        $this->validateDuration($duration);
        $this->setDuration($duration);

        return true;
    }

    public function setQuestion($question)
    {
        $this->question = $this->normalize($question);
    }

    public function resetOptions()
    {
        $this->options = array();
        $this->index = 0;
    }

    public function setOptionsFromArray(array $options)
    {
        global $current_user;

        foreach ($options as $data) {
            $id = (int)$data['id'];

            if (empty($id) && empty($data['option'])) {
                continue;
            }

            if ($id) {
                if (empty($this->options[$id])) {
                    syslog(LOG_WARNING, trim(preg_replace('/\s+/', ' ', '
                        HACKING: User '.$current_user->user_login.' ('.$current_user->user_id.')
                        is triying to modify an option ('.$id.') that not correspond to
                        current poll ('.$this->id.')
                    ')));

                    throw new Exception(_('Las opciones de la encuesta no son válidas'));
                }

                $option = $this->options[$id];
            } else {
                $option = new PollOption;
                $option->id = -$this->index;
            }

            $option->setOption($data['option']);

            $this->setOption($option);
        }
    }

    public function setOption(PollOption $option)
    {
        $option->votes = (int)$option->votes;
        $option->karma = (float)$option->karma;
        $option->index = ++$this->index;
        $option->voted = $this->voted == $option->id;

        if ($option->votes && $this->votes) {
            $option->percent = (int)round(($option->votes / (int)$this->votes) * 100);
        } else {
            $option->percent = 0;
        }

        $this->options[$option->id] = $option;

        if ($this->finished) {
            $this->setOptionWinner();
        }
    }

    private function setOptionWinner()
    {
        $maxVotes = max(array_map(function ($value) {
            return $value->votes;
        }, $this->options));

        $maxKarma = max(array_map(function ($value) use ($maxVotes) {
            return ($value->votes === $maxVotes) ? $value->karma : 0;
        }, $this->options));

        foreach ($this->options as $option) {
            $option->winner = ($option->votes === $maxVotes) && ($option->karma === $maxKarma);
        }
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
        return array_filter($this->options, function ($value) {
            return $value->option ? true : false;
        });
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

    public function validateQuestion()
    {
        global $globals;

        if (mb_strlen($this->question) > $globals['polls_question_len_limit']) {
            throw new Exception(sprintf(_('El límite de longitud de la pregunta es de %s caracteres'), $globals['polls_question_len_limit']));
        }
    }

    public function validateOptions()
    {
        global $globals;

        $count = count($this->options);

        if (($count < 2) || ($count > $this->options_limit)) {
            throw new Exception(sprintf(_('Se debe indicar un mínimo de %s opciones y un máximo de %s'), 2, $this->options_limit));
        }

        $duplicated = array();

        foreach ($this->getOptions() as $option) {
            if (mb_strlen($option->option) > $globals['polls_option_len_limit']) {
                throw new Exception(sprintf(_('El límite de longitud por opción es de %s caracteres'), $globals['polls_option_len_limit']));
            }

            if (in_array($option->option, $duplicated)) {
                throw new Exception(_('Las opciones de la encuesta están duplicadas'));
            }

            $duplicated[] = $option->option;
        }
    }

    public function validateDuration($hours)
    {
        if (!in_array($hours, $this->durations_valid)) {
            throw new Exception(_('La duración indicada en la encuesta no es válida'));
        }
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
                WHERE `'.$related.'` = "'.(int)$related_id.'"
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
        $this->resetOptions();
        $this->index = 0;

        foreach (PollOption::selectFromPollId($this->id) as $option) {
            $this->setOption($option);
        }
    }

    public static function selectSimpleFromRelatedIds($related, array $related_ids)
    {
        global $db;

        $query = '
            SELECT SQL_NO_CACHE *, NULL AS `voted`, TRUE AS `simple`,
                IF (`end_at` < NOW(), TRUE, FALSE) AS `finished`
            FROM `polls`
            WHERE `'.$related.'` IN ('.DbHelper::implodedIds($related_ids).')
            LIMIT '.count($related_ids).';
        ';

        return $db->get_results(DbHelper::queryPlain($query), 'Poll');
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
                    IF (`end_at` < NOW(), TRUE, FALSE) AS `finished`
                FROM `polls`
                WHERE `'.$related.'` IN ('.DbHelper::implodedIds($related_ids).')
                LIMIT '.count($related_ids).';
            ';
        }

        return $db->get_results(DbHelper::queryPlain($query), 'Poll');
    }

    /** WRITE **/

    public function store()
    {
        if (!$this->isStorable()) {
            return;
        }

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

        if ($this->votes || (empty($this->id) && empty($this->question))) {
            return true;
        }

        if (empty($this->question)) {
            return $this->delete();
        }

        return $this->store();
    }

    private function storeOptions()
    {
        global $db;

        $ids = array_unique(array_map(function ($value) {
            return (int)$value->id;
        }, $this->options));

        $db->query(DbHelper::queryPlain('
            DELETE FROM `polls_options`
            WHERE (
                `poll_id` = "'.$this->id.'"
                AND `id` NOT IN ('.implode(',', $ids).')
            );
        '));

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
                `question` = "'.$db->escape($this->question).'",
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
                `question` = "'.$db->escape($this->question).'",
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

        if ($response) {
            $this->id = null;
        }

        return $response ? true : false;
    }

    public function deleteOptions()
    {
        global $db;

        $response = $db->query(DbHelper::queryPlain('
            DELETE FROM `polls_options`
            WHERE `poll_id` = "'.(int)$this->id.'";
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

            $db->rollback();

            return;
        }

        $query = '
            UPDATE `polls`
            SET `votes` = `votes` + 1
            WHERE `id` = "'.$this->id.'"
            LIMIT 1;
        ';

        if (!$db->query(DbHelper::queryPlain($query))) {
            $db->rollback();
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
        return htmlspecialchars(trim(preg_replace('/[\n|\r]/', '', strip_tags($value))));
    }
}

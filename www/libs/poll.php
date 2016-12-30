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

    private $options = array();
    private $options_limit = 5;
    private $durations_valid = array(1, 5, 10, 15, 30);

    public function setOptions(array $options)
    {
        $options = array_filter(array_unique(array_map('trim', $options)));

        $this->options = array();

        foreach ($options as $text) {
            if (empty($text)) {
                continue;
            }

            $option = new PollOption;
            $option->option = $text;

            $this->options[] = $option;
        }
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function areOptionsValid()
    {
        return count($this->options) <= $this->options_limit;
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

        $response = $db->query(str_replace("\n", ' ', '
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

        $response = $db->query(str_replace("\n", ' ', '
            UPDATE `polls`
            SET `question` = "'.$this->question.'"
            WHERE `id` = "'.(int)$this->id.'"
            LIMIT 1;
        '));

        return $response ? true : false;
    }

    private function normalize($value)
    {
        return clean_lines(clear_whitespace($value));
    }
}

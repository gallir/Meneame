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

    public $options = array();

    public function setOptions(array $options)
    {
        foreach ($options as $text) {
            if (empty(trim($text))) {
                continue;
            }

            $option = new PollOption;
            $option->option = $text;

            $this->options[] = $option;
        }
    }

    public function store()
    {
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

<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//      http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

class PollCollection
{
    public $rows = array();

    public function loadFromRelatedIds($related, array $related_ids)
    {
        $this->rows = $rows = array();

        foreach (Poll::selectFromRelatedIds($related, $related_ids) as $row) {
            $rows[$row->id] = $row;
        }

        foreach (PollOption::selectFromPollIds(array_keys($rows)) as $row) {
            $rows[$row->poll_id]->setOption($row);
        }

        foreach ($rows as $row) {
            $this->rows[$row->$related] = $row;
        }
    }

    public function get($related_id)
    {
        if (isset($this->rows[$related_id])) {
            return $this->rows[$related_id];
        }
    }
}

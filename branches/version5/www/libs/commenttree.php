<?php


class CommentTreeNode {

	public function __construct($id, $child = false) {
		$this->children = array();
		$this->id = $id;
		$this->level = 0;
		if ($child) {
			$this->addChild($child);
		}
	}


	public function addChild($child) {
		if (! isset($this->children[$child->id])) {
			$this->children[$child->id] = $child;
			return True;
		}
		return False;
	}

	public function deepFirst($max = 100, $level = 0, &$seen = False) {
		if ($seen === false) {
			$seen = array();
		}

		if ($level >= $max || isset($seen[$this->id])) {
			return $seen;
		}

		$this->level = $level;
		$seen[$this->id] = true;
		foreach ($this->children as $id => $child) {
			if (! in_array($child->id, $seen, true)) {
				$seen = $child->deepFirst($max, $level + 1, $seen);
			}
		}
		return $seen;
	}
}

class CommentTree {
	public $rootsIds;
	public $roots;
	public $nodesIds;

	public function __construct() {
		$this->nodesIds = array();
		$this->rootsIds = array();
		$this->roots = array();
	}

	protected function getNodeById($id) {
		if (isset($this->nodesIds[$id])) {
			return $this->nodesIds[$id];
		} else {
			$node = new CommentTreeNode($id);
			return $node;
		}
	}

	protected function addToIndexes($node, $parent = false) {
		if (! isset($this->nodesIds[$node->id])) {
			$this->nodesIds[$node->id] = $node;
			if (! $parent) {
				if( ! isset($this->rootsIds[$node->id])) {
					$this->rootsIds[$node->id] = $node;
					$this->roots[] = $node;
				}
			}
		}

		if ($parent && isset($this->rootsIds[$node->id])
			&& isset($this->rootsIds[$parent->id]) ) { // Only remove if the parent is root
			unset($this->rootsIds[$node->id]);
			if(($key = array_search($node, $this->roots)) !== false) {
				unset($this->roots[$key]);
			}
		}
	}

	public function addByIds($parent_id, $child_id = 0) {
		$parent_id = (int) $parent_id;
		$child_id = (int) $child_id;

		if ($parent_id == $child_id) {
			$child_id = 0;
		}

		if ($parent_id > 0) {
			$parent = $this->getNodeById($parent_id);
			$this->addToIndexes($parent);
		} else {
			$parent = false;
		}

		if ($child_id > 0) {
			$child = $this->getNodeById($child_id);
			if ($parent) {
				$parent->addChild($child);
			}
			$this->addToIndexes($child, $parent);
		}
	}

	public function in($id) {
		return isset($nodesIds[$id]);
	}

	public function size() {
		return count($this->nodesIds);
	}

	public function deepFirst($max = 100, $sort_roots = false) {
		$seen = array();
		if ($sort_roots) {
			ksort($this->rootsIds);
		}
		foreach ($this->rootsIds as $n) {
			$seen = $n->deepFirst($max, 0, $seen);
		}
		return $seen;
	}
}

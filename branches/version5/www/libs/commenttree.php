<?php


class CommentTreeNode {
	public $id;
	public $children;
	public $level;

	public function __construct($id, $child = false) {
		$this->children = array();
		$this->id = $id;
		if ($child) {
			$this->addChild($child);
		}
	}

	public function addChild($child) {
		if (! in_array($child, $this->children)) {
			$this->children[] = $child;
			return True;
		}
		return False;
	}

	public function deepFirst($max = 5, $level = 0, $seen = False) {
		if ($seen === false) {
			$seen = array();
		}

		if ($level >= $max || in_array($this, $seen)) {
			return $seen;
		}

		$this->level = $level;
		$seen[] = $this;
		foreach ($this->children as $child) {
			if (! in_array($child, $seen)) {
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

		if ($parent && isset($this->rootsIds[$node->id]) ) {
			unset($this->rootsIds[$node->id]);
			if(($key = array_search($node, $this->roots)) !== false) {
				unset($this->roots[$key]);
			}
		}
	}

	public function addByIds($parent_id, $child_id = 0) {
		if ($parent_id == $child_id) {
			return false;
		}

		$parent = $this->getNodeById($parent_id);
		$this->addToIndexes($parent);

		if ($child_id > 0) {
			$child = $this->getNodeById($child_id);
			$parent->addChild($child);
			$this->addToIndexes($child, $parent);
		}
	}

	public function deepFirst($max = 5) {
		$seen = array();
		ksort($this->rootsIds);
		foreach ($this->rootsIds as $n) {
			$seen = $n->deepFirst($max, 0, $seen);
		}
		return $seen;
	}
}

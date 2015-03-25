<?php

	/**
	 * Represents an "update" type query.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class UpdateQuery extends Query {

		/**
		 * The ORM object we're going to update.
		 */
		private $ormObject;

		/**
		 * Extended constructor which also takes the ORM object we are updating.
		 *
		 * @param String $table The table name this query should select from
		 * @param Object Something which extends class Atlas and is the object we are updating.
		 */
		public function __construct($table, $ormObject) {
			parent::__construct($table);
			$this->ormObject = $ormObject;
		}

		/**
		 * Performs the update query.
		 */
		public function execute() {
			// We need to check what columns changed since the object was hydrated.
			$changedColumns = array();
			foreach ($this->ormObject->originalValues as $col => $val) {
				if ($this->ormObject->{$col} != $val) {
					$changedColumns[$col] = $this->ormObject->{$col};
				}
			}

			$this->queryString = "UPDATE ".$this->table." SET ";

			$i = 0;
			foreach ($changedColumns as $col => $val) {
				if (($i + 1) < count($changedColumns)) {
					$this->queryString .= "$col = ?, ";
				} else {
					$this->queryString .= "$col = ? ";
				}
				$this->queryParams[] = $val;
				$i++;
			}

			// Setup the where id = clause
			$this->queryString .= "WHERE id = ?";
			$this->queryParams[] = $this->ormObject->id;

			return $this->executeRawQuery();
		}
	}

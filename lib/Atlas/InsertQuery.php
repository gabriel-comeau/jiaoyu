<?php

	/**
	 * Represents an "insert" type query.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class InsertQuery extends Query {

		/**
		 * The ORM object we're going to insert.
		 */
		private $ormObject;

		/**
		 * Extended constructor which also takes the ORM object we are inserting.
		 *
		 * @param String $table The table name this query should select from
		 * @param Object Something which extends class Atlas and is the object we are inserting.
		 */
		public function __construct($table, $ormObject) {
			$this->ormObject = $ormObject;
			parent::__construct($table);
		}

		/**
		 * Performs the insert query.
		 */
		public function execute() {

			$this->queryString = "INSERT INTO ".$this->table." (";

			// Remove the ID column from the map since we aren't going to be inserting it.
			$idLessColMap = $this->ormObject->colMap;
			$idKey = array_search("id", $idLessColMap);
			if ($idKey !== false) {
				unset($idLessColMap[$idKey]);
			}

			// Find all of the column names from the orm object and append them to
			// the query string for the VALUES part
			foreach ($idLessColMap as $i => $col) {
				if (($i) < count($idLessColMap)) {
					$this->queryString .= $col.",";
				} else {
					$this->queryString .= $col.") VALUES (";
				}
			}

			// For each one of those columns in values let's put a ? in the spot
			// where the actual value will go
			for ($i = 0; $i < count($idLessColMap); $i++) {
				if (($i + 1) == count($idLessColMap)) {
					$this->queryString .= "?)";
				} else {
					$this->queryString .= "?,";
				}
			}

			// Finally add all of the real values to substitute for the ? in the query text on
			// execution.
			foreach ($idLessColMap as $col) {
				$this->queryParams[] = $this->ormObject->{$col};
			}

			// execute
			$res = $this->executeRawQuery();

			if ($res) {
				// Set the last id on the query object
				$this->lastId = DBManager::getInstance()->lastInsertId();
			}

			return $res;
		}
	}

<?php

	/**
	 * Represents a database query.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	abstract class Query {

		/**
		 * Table name this query will operate on
		 */
		protected $table;

		/**
		 * The text of the query
		 */
		protected $queryString;

		/**
		 * The parameterized values for the query (what goes into the ?)
		 */
		protected $queryParams = array();

		/**
		 * The last insert id.  Will only be set for an insert query that was successful.
		 */
		protected $lastId = null;

		/**
		 * Constructor function
		 *
		 * @param String $table The name of the table this query should operate on
		 */
		public function __construct($table) {
			$this->table = $table;
		}


		/**
		 * Master public method to execute a query.
		 *
		 * @return Mixed - See the individual methods instead!
		 */
		abstract public function execute();

		/**
		 * Executes the raw PDO query and return an associative array of the results.
		 *
		 * @todo Lots of error handling!
		 *
		 * @param Boolean $getRows Whether or not to return the rows (from a select)
		 * @return Mixed - Either the results of the query in row form or raw results form
		 */
		protected function executeRawQuery($getRows = false) {
			$stmt = DBManager::getInstance()->prepare($this->queryString);
			$res = $stmt->execute($this->queryParams);
			if ($getRows) {
				$rawResults = array();
				if ($res) {
					while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
						$rawResults[] = $row;
					}
					return $rawResults;
				} else {
					return null;
				}
			} else {
				return $res;
			}
		}

		/**
		 * Gets the last id from a successfully executed insert query.
		 *
		 * Throws an exception if this is called at the wrong time or from the wrong query type.
		 *
		 * @return Int The last ID.
		 */
		public function getLastId() {
			if ($this->lastId) {
				return $this->lastId;
			} else {
				throw new AtlasException("Can't get last ID, this query wasn't executed or isn't an insert!");
			}
		}
	}

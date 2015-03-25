<?php

	/**
	 * Represents a "delete" type query.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	 class DeleteQuery extends Query {

	 	/**
	 	 * Primary key of the object to be deleted.
	 	 */
	 	private $objectId;

	 	/**
		 * Extended constructor which also takes id of the object to be deleted.
		 *
		 * @param String $table The table name this query should select from
		 * @param Integer $objectId The primary key of the row to be deleted.
		 */
		public function __construct($table, $objectId) {
			$this->objectId = $objectId;
			parent::__construct($table);
		}

		/**
		 * Executes the delete query.
		 *
		 * @return Boolean Whether or not the delete was successful.
		 */
		public function execute() {
			$this->queryString = "DELETE FROM ".$this->table." WHERE id = ?";
			$this->queryParams[] = $this->objectId;
			return $this->executeRawQuery();
		}
	 }

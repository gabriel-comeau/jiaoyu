<?php

	/**
	 * Represents the "order by" clause of a select query.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class OrderClause {

		/**
		 * Column to order by
		 */
		private $column;

		/**
		 * Direction to order by
		 */
		private $direction;

		/**
		 * Represents descending order
		 */
		const DESC = 'DESC';

		/**
		 * Represents ascending order
		 */
		const ASC = 'ASC';

		/**
		 * Constructor
		 *
		 * @param String $col The name of the column to order by
		 * @param String $dir The direction to order by - asc or desc
		 */
		public function __construct($col, $dir = 'desc') {
			$this->column = $col;

			if (strtolower($dir) == 'desc' || strtolower($dir) == 'descending') {
				$this->direction = self::DESC;
			} else if (strtolower($dir) == 'asc' || strtolower($dir) == 'ascending') {
				$this->direction = self::ASC;
			} else {
				throw new AtlasException("Sort direction must be 'asc' or 'desc', $dir given");
			}
		}

		/**
		 * Gets the order by clause query text to be appended to the rest of the query.
		 *
		 * @return String The clauses's text.
		 */
		public function getClauseString() {
			return "ORDER BY ".$this->column.' '.$this->direction;
		}
	}

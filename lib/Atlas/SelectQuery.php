<?php

	/**
	 * Represents a "select" type query.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class SelectQuery extends Query {

		/**
		 * The ORM class name to hyrdrate (should be subclassed from Atlas)
		 */
		private $ormClass;

		/**
		 * Array of Condition objects (where clauses) to be run when the query is executed.
		 */
		private $conditions = array();

		/**
		 * Int value to limit number of results
		 */
		private $limit = null;

		/**
		 * Offsets
		 */
		private $offset = null;

		/**
		 * An OrderClause
		 */
		private $orderBy;

		/**
		 * This is a gross hack because mysql doesn't allow offsets without
		 * defined limits so this value represents the maximum BIG_INT value
		 * that a row can contain.
		 */
		const MYSQL_MAX_ROW = 18446744073709551615;

		/**
		 * Extended constructor which also takes the name of the ORM class
		 * to hyrdrate to.
		 *
		 * @param String $table The table name this query should select from
		 * @param String $ormClass The class name this query should hydrate results into
		 */
		public function __construct($table, $ormClass) {
			parent::__construct($table);
			$this->ormClass = $ormClass;
		}

		/**
		 * Executes this select query and return hydrated results
		 *
		 * @return Array The collection of hydrated objects (or null)
		 */
		public function execute() {
			// Select is the default - maybe it shouldn't be ?
			$this->queryString = "SELECT * FROM ".$this->table." ";
			$this->appendWhereClause();

			if ($this->orderBy) {
				$this->queryString .= $this->orderBy->getClauseString();
			}

			if ($this->limit || $this->offset) {
				$this->addOffsetAndLimit();
			}

			$rawResults = $this->executeRawQuery(true);
			return $this->hydrate($rawResults);
		}

		/**
		 * Shortcut for andWhere()
		 *
		 * @param String $col The column name
		 * @param String $op The conditional operator
		 * @param Mixed $arg
		 * @return SelectQuery The query with the added where clause
		 */
		public function where($col, $op, $arg) {
			return $this->andWhere($col, $op, $arg);
		}

		/**
		 * The "default" where clause - this one operates as "and".
		 *
		 * @param String $col The column name
		 * @param String $op The conditional operator
		 * @param Mixed $arg
		 * @return SelectQuery The query with the added where clause
		 */
		public function andWhere($col, $op, $arg) {
			$cond = new Condition($col, $op, $arg, true);
			$this->conditions[] = $cond;
			return $this;
		}

		/**
		 * The "or" where clause.
		 *
		 * @param String $col The column name
		 * @param String $op The conditional operator
		 * @param Mixed $arg
		 * @return SelectQuery The query with the added where clause
		 */
		public function orWhere($col, $op, $args) {
			$cond = new Condition($col, $op, $arg, false);
			$this->conditions[] = $cond;
			return $this;
		}

		/**
		 * Add a limit to the query.
		 *
		 * @param Integer $lim The maximum number of results to return
		 * @return SelectQuery The query with the added limit clause
		 */
		public function limit($lim) {
			if (!is_integer($lim) || $lim < 1) {
				throw new AtlasException("limit() argument must be positive integer, $lim given");
			}
			$this->limit = $lim;
			return $this;
		}

		/**
		 * Add an offset to the query.
		 *
		 * @param Integer $off The position to start from in the results.
		 * @return SelectQuery The query with the added offset clause
		 */
		public function offset($off) {
			if (!is_integer($off) || $off < 1) {
				throw new AtlasException("offset() argument must be positive integer, $off given");
			}
			$this->offset = $off;
			return $this;
		}

		/**
		 * Adds an order by clause to the select query.
		 *
		 * @param String $col Column name to order by
		 * @param String $dir Direction to order in.  Defaults to 'desc' (can also be asc)
		 * @return SelectQuery The query with the added order by clause
		 */
		public function orderBy($col, $dir = 'desc') {
			$this->orderBy = new OrderClause($col, $dir);
			return $this;
		}

		/**
		 * Appends the limit and/or offset clauses to the mysql query.
		 */
		private function addOffsetAndLimit() {
			if ($this->limit) {
				// Do we also have an offset?
				if ($this->offset) {
					$this->queryString .= " LIMIT ".$this->limit." OFFSET ".$this->offset;
				} else {
					$this->queryString .= " LIMIT ".$this->limit;
				}
			} else {
				// Only an offset then - use the mysql max row value here to ensure
				// that all remaining rows after offset are returned.
				$this->queryString .= " LIMIT ".self::MYSQL_MAX_ROW." OFFSET ".$this->offset;
			}
		}

		/**
		 * Go through the conditions array and build the where clause of the query.
		 *
		 * This method alters the object in place - it does not return any results so
		 * it is not very nice from a referential transparency point of view.
		 */
		private function appendWhereClause() {
			if (count($this->conditions)) {
				$first = true;
				foreach ($this->conditions as $cond) {
					if ($first) {
						$this->queryString .= "WHERE ".$cond->column." ".$cond->operator." ?";
						$this->queryParams[] = $cond->arg;
						$first = false;
					} else {
						if ($cond->isAnd) {
							$this->queryString .= "AND WHERE ".$cond->column." ".$cond->operator." ?";
							$this->queryParams[] = $cond->arg;
						} else {
							$this->queryString .= "OR WHERE ".$cond->column." ".$cond->operator." ?";
							$this->queryParams[] = $cond->arg;
						}
					}
				}
			}
		}

		/**
		 * Creates the hydrated objects from the raw query and return
		 * them in a collection.
		 *
		 * @param Array The rows from the raw PDO query
		 * @return Array A collection of the hydrated results (or null if nothing)
		 */
		private function hydrate($rawResults) {
			$hydratedResults = array();
			if (count($rawResults)) {
				foreach ($rawResults as $r) {
					$obj = new $this->ormClass(true);
					foreach ($r as $k => $v) {
						$obj->{$k} = $v;
						$obj->originalValues[$k] = $v;
					}
					$hydratedResults[] = $obj;
				}
				return $hydratedResults;
			} else {
				return null;
			}
		}
	}

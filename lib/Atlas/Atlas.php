<?php

	/**
	 * Main class to be inherited by Model objects.
	 *
	 * The Atlas ORM works by representing rows as objects and the table as the class -
	 * you use static methods on the class for table level operations which return collections
	 * of instances, which each represent a single row.
	 *
	 * Of course any time you need to bypass the ORM lib to do a more complex query (especially when
	 * you don't care about hydrated results) you are free to call DBManager::getInstance() to get
	 * a PDO object back.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class Atlas {

		/**
		 * The table name - meant to be set on the inheriting object
		 */
		protected static $table;

		/**
		 * Flag to see if this is a new object instance (without an id) or one that already
		 * exists.
		 */
		protected $existingObject;

		/**
		 * Track the column names
		 */
		public $colMap = array();

		/**
		 * Whenever one of these objects gets instantiated we keep track of the original values
		 * so that if they get changed we can keep track of it.
		 */
		public $originalValues = array();



		/**
		 * Constructor - basically builds all of the dynamic properties for the object by
		 * introspecting the table and assign each column to a publicly accessible property
		 * on the object.
		 *
		 * Note it doesn't set any values since this can be used to create new
		 * objects which have never been saved yet.
		 */
		public function __construct($existing = false) {
			$caller = get_called_class();
			$q = "DESC ".$caller::$table;
			$stmt = DBManager::getInstance()->prepare($q);
			$res = $stmt->execute();
			if ($res) {
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$colName = $row['Field'];
					$this->{$colName} = null;
					$this->colMap[] = $colName;
					if ($existing) {
						$this->originalValues[$colName] = null;
					}
				}

				$this->existingObject = $existing;
			} else {
				throw new AtlasException("Couldn't read table ".$caller::$table);
			}
		}

		/**
		 * Writes this object instance to the database.
		 *
		 * If this is called on a newly created object, it will run an insert query
		 * and the object will be given an ID.  Otherwise it will update the existing object.
		 *
		 * @return Boolean Whether or not this was successful (though we'll usually throw an exception if not)
		 */
		public function save() {

			$caller = get_called_class();

			if (!$this->existingObject) {
				// We're performing an insert, this object has never been saved before.
				$query = new InsertQuery($caller::$table, $this);
				$result = $query->execute();
				if ($result) {
					$this->id = $query->getLastId();
					return $this;
				} else {
					throw new AtlasException("Error occurred while trying to insert $caller object!");
				}

			} else {
				$query = new UpdateQuery($caller::$table, $this);
				$result = $query->execute();
				if ($result) {
					return $this;
				} else {
					throw new AtlasException("Error occurred while trying to update a $caller object!");
				}
			}
		}

		/**
		 * Delete this object from the database.
		 *
		 * @return Boolean Whether or not this was successful (though we'll usually throw an exception if not)
		 */
		public function delete() {
			$caller = get_called_class();

			if (!$this->existingObject) {
				// Nothing in the database to destroy

				// Not sure what to do here - we can either throw an exception because trying to
				// delete a non-serialized ORM object is dumb.  Or we can unset all of the property's
				// on the object to make it effectively useless.  We'll do the latter for now

				$props = get_object_vars($this);
				foreach ($props as $prop) {
					unset ($this->prop);
				}
				return true;
			} else {
				$query = new DeleteQuery($caller::$table, $this->id);
				$result = $query->execute();
				if ($result) {
					return $result;
				} else {
					throw new AtlasException("Error occured while trying to delete $caller object");
				}
			}
		}

		/**
		 * Find a single instance of this object by it's PK
		 * and return it.
		 *
		 * @param Integer $id The PK of the object
		 * @return Mixed The Atlas-extending hydrated ORM object
		 */
		public static function one($id) {
			$caller = get_called_class();
			$objects = $caller::where('id', '=', $id)->limit(1)->execute();
			if (count($objects)) {
				return $objects[0];
			} else {
				return null;
			}
		}

		/**
		 * Creates a query object on this table and returns it (without executing)
		 *
		 * @param String $column The name of the column for the where condition
		 * @param String $op The operation used in the where clause (=, !=, <, > etc)
		 * @param Mixed $args The arguments for the where condition
		 * @return SelectQuery The query object
		 */
		public static function where($column, $op, $args) {
			$caller = get_called_class();
			$query = new SelectQuery($caller::$table, $caller);
			$query->where($column, $op, $args);
			return $query;
		}

		/**
		 * Get the query for "all" - the entirety of the results
		 * for the table.  This method doesn't actually execute
		 * the query, only returns it.
		 *
		 * @return SelectQuery A query which will select all of the rows from the table.
		 */
		public static function all() {
			$caller = get_called_class();
			$query = new SelectQuery($caller::$table, $caller);
			return $query;
		}

		/**
		 * Gets all of the results from the table with no where clause, hydrated as object.
		 *
		 * WARNING On a large table this will be slow and take up lots of memory so use it
		 * carefully!
		 *
		 * @return Array The hydrated collection of objects for the entire table.
		 */
		public static function getAll() {
			return self::all()->execute();
		}
	}

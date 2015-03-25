<?php

	/**
	 * Represents a piece of the overall "where" clause.
	 *
	 * @author Gabriel Comeau
	 * @package Atlas
	 */
	class Condition {

		/**
		 * Column to operate on
		 */
		public $column;

		/**
		 * Conditional operator - =, !=, <, > etc
		 */
		public $operator;

		/**
		 * Argument
		 */
		public $arg;

		/**
		 * Logical AND or OR
		 */
		public $isAnd;

		public function __construct($col, $op, $arg, $isAnd) {
			$this->column = $col;
			$this->operator = $op;
			$this->arg = $arg;
			$this->isAnd = $isAnd;
		}
	}

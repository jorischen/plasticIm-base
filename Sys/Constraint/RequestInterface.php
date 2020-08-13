<?php

namespace Sys\Constraint;

interface RequestInterface {

	/**
	 * Set request data
	 */
	public function setData();

	/**
	 * Get all data fields as an array
	 */
	public function all(): array;

	/**
	 * Serialize to JSON
	 */
	public function toJson(): string;

}
<?php

namespace Knowledge\Domain;

class Article {
	private int $id;
	private string $title;
	private string $slug;

	public function __construct( int $id, string $title, string $slug = '' ) {
		$this->id    = $id;
		$this->title = $title;
		$this->slug  = $slug;
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_title(): string {
		return $this->title;
	}
}

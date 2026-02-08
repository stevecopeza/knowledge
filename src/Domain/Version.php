<?php

namespace Knowledge\Domain;

use Knowledge\Domain\ValueObject\Source;
use DateTimeImmutable;

class Version {
	private string $uuid;
	private int $article_id;
	private Source $source;
	private string $content_path; // Path to file on disk
	private string $title;
	private string $hash;
	private DateTimeImmutable $created_at;

	public function __construct(
		string $uuid,
		int $article_id,
		Source $source,
		string $title,
		string $content_path,
		string $hash
	) {
		$this->uuid         = $uuid;
		$this->article_id   = $article_id;
		$this->source       = $source;
		$this->title        = $title;
		$this->content_path = $content_path;
		$this->hash         = $hash;
		$this->created_at   = new DateTimeImmutable();
	}

	public function get_uuid(): string {
		return $this->uuid;
	}

	public function get_article_id(): int {
		return $this->article_id;
	}

	public function get_source(): Source {
		return $this->source;
	}

	public function get_title(): string {
		return $this->title;
	}

	public function get_content_path(): string {
		return $this->content_path;
	}

	public function get_hash(): string {
		return $this->hash;
	}
}

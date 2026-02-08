<?php

namespace Knowledge\Domain\ValueObject;

use InvalidArgumentException;

class Source {
	private string $url;
	private string $domain;
	private string $protocol;

	public function __construct( string $url ) {
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			throw new InvalidArgumentException( "Invalid URL provided: {$url}" );
		}

		$this->url      = $url;
		$parsed         = parse_url( $url );
		$this->domain   = $parsed['host'] ?? '';
		$this->protocol = $parsed['scheme'] ?? '';
	}

	public function get_url(): string {
		return $this->url;
	}

	public function get_domain(): string {
		return $this->domain;
	}

	public function __toString(): string {
		return $this->url;
	}
}

<?php

namespace App\Notifications\Contracts;

class NotifyMessage
{
	protected array $links = [];
	protected array|string $content = '';

	function __construct()
	{
	}

	function setContent($msg)
	{
		return $this->content = $msg;
	}

	function setLink($slug, $href,  $text)
	{
		if (!empty($slug) && !empty($href) && !empty($text)) {
			$this->links[] = [
				$slug . '' => [
					'href' => $href,
					'text' => $text
				]
			];
		}
	}

	function getContent()
	{
		return $this->content;
	}

	function getLinks()
	{
		return $this->links;
	}
}

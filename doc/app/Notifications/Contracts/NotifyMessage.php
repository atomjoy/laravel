<?php

namespace App\Notifications\Contracts;

class NotifyMessage
{
	protected array $links = [];

	/**
	 * Notification message format.
	 */
	function __construct(
		protected string|array $content,
	) {
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

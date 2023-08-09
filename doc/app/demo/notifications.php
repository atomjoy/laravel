<?php

use App\Models\User;
use App\Notifications\Contracts\NotifyMessage;
use App\Notifications\DbNotify;
use Illuminate\Support\Facades\Route;

Route::get('/demo/notifications', function () {
	// User
	$user = User::first();

	// Create
	$msg = new NotifyMessage();
	$msg->setContent('Hello Alexis your link_signup and link_signin links.');
	$msg->setLink('link_signup', 'https://example.com/signup', 'Sign Up');
	$msg->setLink('link_signin', 'https://example.com/signin', 'Sign In');

	// Send
	$user->notify(new DbNotify($msg));
	$user->notifyNow(new DbNotify($msg));

	// Get notifications paginate
	$page = 0;
	$perpage = 4;

	return User::with(['notifications' => function ($q) use ($page, $perpage) {
		$q->latest()
			->skip(($page - 1) * $perpage)
			->take($perpage);
		// $q->where('id', '!=', '2');
		// $query->select('id','notifiable_id','data','created_at')->orderBy('created_at', 'desc');
		// $q->latest()->paginate($perpage);
	}])->where('id', $user->id)
		->orderBy('created_at', 'desc')
		->get();
});

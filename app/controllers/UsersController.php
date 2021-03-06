<?php

class UsersController extends BaseController {

	private $rules = array(
		'ltid' => 'regex:/^[0-9a-zA-Z]{10}$/',
		'lastname' => 'required',
		'firstname' => 'required',
		'email' => 'requiredWithout:phone',
		'lang' => 'required'
	);

	private $messages = array(
		'ltid.regex' => 'LTID har ikke riktig format.',
		'lastname.required' => 'Etternavn må fylles inn.',
		'firstname.required' => 'Fornavn må fylles inn.',
		'email.required_without' => 'Enten e-post eller telefonnummer må fylles inn.',
		'lang.required' => 'Språk må fylles inn.'
	);

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$users = array();
		foreach (User::with('loans')->orderBy('lastname')->get() as $user) {
			$users[] = array(
				'id' => $user->id,
				'value' => $user->lastname . ', ' . $user->firstname,
				'lastname' => $user->lastname,
				'firstname' => $user->firstname,
				'ltid' => $user->ltid,
				'loancount' => count($user->loans)
			);
		}
		if (Request::ajax()) {
			return Response::json($users);
		}
		return Response::view('users.index', array(
			'users' => $users
		));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		# with('loans')->

		if (is_numeric($id)) {
			$user = User::find($id);
		} else {
			$user = User::where('ltid','=',$id)->first();
		}

		if (!$user) {
			return Response::view('errors.missing', array('what' => 'Brukeren'), 404);
		}
		return Response::view('users.show', array(
				'user' => $user
			));
	}

	/**
	 * Display BIBSYS NCIP info for the specified user.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getNcipLookup($id)
	{
		$user = User::find($id);
		if (!$user) {
			return Response::json(array('exists' => false));
		}
		$data = $user->ncipLookup();

		return Response::json($data);
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
		$user = User::find($id);
		return Response::view('users.edit', array(
				'user' => $user
			));

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate($id)
	{
		$validator = Validator::make(Input::all(), $this->rules, $this->messages);

		if ($validator->fails())
		{
			return Redirect::action('UsersController@getEdit', $id)
				->withErrors($validator)
				->withInput();
		}

		$user = User::find($id);
		$ltid = Input::get('ltid');
		if (empty($ltid)) {
			$user->ltid = null;
		} else {
			$user->ltid = $ltid;
		}
		$user->lastname = Input::get('lastname');
		$user->firstname = Input::get('firstname');
		$user->phone = Input::get('phone') ? Input::get('phone') : null;
		$user->email = Input::get('email') ? Input::get('email') : null;
		$user->lang = Input::get('lang');
		$user->save();

		return Redirect::action('UsersController@getShow', $id)
			->with('status', 'Informasjonen ble lagret.');
	}

	/**
	 * Display form to merge two users.
	 *
	 * @param  string  $user1
	 * @param  string  $user2
	 * @return Response
	 */
	public function getMerge($user1, $user2)
	{
		$user1 = User::find($user1);
		if (!$user1) {
			return Response::view('errors.missing', array('what' => 'Bruker 1'), 404);
		}

		$user2 = User::find($user2);
		if (!$user2) {
			return Response::view('errors.missing', array('what' => 'Bruker 2'), 404);
		}

		$merged = $user1->getMergeData($user2);

		return Response::view('users.merge', array(
			'user1' => $user1,
			'user2' => $user2,
			'merged' => $merged
		));
	}

	/**
	 * Merge $user2 into $user1
	 *
	 * @param  string  $user1
	 * @param  string  $user2
	 * @return Response
	 */
	public function postMerge($user1, $user2)
	{
		$user1 = User::find($user1);
		if (!$user1) {
			return Response::view('errors.missing', array('what' => 'Bruker 1'), 404);
		}

		$user2 = User::find($user2);
		if (!$user2) {
			return Response::view('errors.missing', array('what' => 'Bruker 2'), 404);
		}

		$mergedAttributes = array();
		foreach (User::$editableAttributes as $attr) {
			$mergedAttributes[$attr] = Input::get($attr);
		}

		$errors = $user1->merge($user2, $mergedAttributes);

		if (!is_null($errors)) {
			return Redirect::action('UsersController@getMerge', array($user1->id, $user2->id))
				->withErrors($errors);
		}

		return Redirect::action('UsersController@getShow', $user1->id)
			->with('status', 'Brukerne ble flettet.');
	}


}

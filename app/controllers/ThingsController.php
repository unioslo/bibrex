<?php

class ThingsController extends BaseController {

	protected $thing;

	public function __construct(Thing $thing)
	{
		$this->thingFactory = $thing;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$library_id = Auth::user()->id;

		$things = $this->thingFactory
			->with('documents.loans')
			->where('library_id', null)
			->orWhere('library_id', $library_id)
			->get();

		return Response::view('things.index', array(
			'things' => $things
		));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore()
	{
		$thing = new $this->thingFactory();
		$thing->name = Input::get('thing');

		if (!$thing->save()) {
			return Redirect::action('ThingsController@getIndex')
				->withErrors($thing->errors)
				->withInput();
		}

		return Redirect::action('ThingsController@getIndex')
			->with('status', 'Tingen ble lagret!');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		$thing = $this->thingFactory->find($id);
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}
		return Response::view('things.show', array(
			'thing' => $thing
		));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
		$thing = $this->thingFactory->find($id);
		return Response::view('things.edit', array(
			'thing' => $thing
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
		$thing = $this->thingFactory->find($id);
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}

		$thing->name = Input::get('name');
		$thing->disabled = Input::get('disabled') == 'on';

		if (!$thing->save()) {
			return Redirect::action('ThingsController@getEdit', $thing->id)
				->withErrors($thing->errors)
				->withInput();
		}

		return Redirect::action('ThingsController@getShow', $thing->id)
			->with('status', 'Tingen ble lagret!');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getDestroy($id)
	{
		$thing = $this->thingFactory->find($id);
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}

		$name = $thing->name;

		if (count($thing->allLoans()) != 0) {
			return Redirect::action('ThingsController@getShow', $thing->id)
				->with('status', 'Beklager, kan bare slette ting som ikke har blitt lånt ut enda.');
		}

		$thing->delete();

		return Redirect::action('ThingsController@getIndex')
			->with('status', 'Tingen «' . $name . '» ble slettet.');
	}

}
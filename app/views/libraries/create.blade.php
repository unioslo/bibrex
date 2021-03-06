@extends('layouts.master')

@section('content')

  {{ Form::model(new Thing(), array(
      'action' => 'LibrariesController@postStore',
      'class' => 'panel panel-primary form-horizontal'
      )) }}

<div class="panel-heading">
      <h3 class="panel-title">Opprett nytt bibliotek</h3>
    </div>

 <div class="panel-body">

    <div class="form-group">
	    <label for="name" class="col-sm-2 control-label">Navn</label>
	    <div class="col-sm-10">
	      {{ Form::text('name', null, array(
	      	'id' => 'name',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

	   <div class="form-group">
	    <label for="email" class="col-sm-2 control-label">Epost </label>
	    <div class="col-sm-10">
	      {{ Form::text('email', null, array(
	      	'id' => 'email',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

	  <div class="form-group">
	    <label for="password" class="col-sm-2 control-label">Passord </label>
	    <div class="col-sm-10">
	      {{ Form::password('password',  array(
	      	'id' => 'password',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

	   <div class="form-group">
	    <label for="password2" class="col-sm-2 control-label">Gjenta passord </label>
	    <div class="col-sm-10">
	      {{ Form::password('password2', array(
	      	'id' => 'password2',
            'class' => 'form-control'
        )) }}
	    </div>
	  </div>

    <button type="submit" class="btn btn-success">
      Lagre nytt bibliotek
    </button>

    <img src="/img/spinner2.gif" class="spinner" />

  </div>

  {{ Form::close() }}


@stop


@section('scripts')

<script type='text/javascript'>     
  $(document).ready(function() {
    $('.spinner').hide();
    $('form').on('submit', function(e) {
      $('.spinner').show();
      $('input[type="button"]').prop('disabled', true);
      return true;
    });
  });
</script>

@stop

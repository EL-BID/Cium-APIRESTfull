@extends('errors.layout')

@section('title-page') 403 @endsection

@section('js')
@parent
@endsection


@section('aside')
@endsection

@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default datagrid">
		    <div class="panel-heading"> <h1><span class="glyphicon glyphicon-warning-sign"></span> 403</h1></div>
	        <div class="panel-body">
				<h2>Acceso denegado.</h2>
				<p>Lo sentimos no tienes acceso al recurso al que intentas acceder.</p>
		    </div> 
		</div>
	</div>
</div> 	

@endsection
